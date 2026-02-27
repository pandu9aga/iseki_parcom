@extends('layouts.admin')
@section('content')
    <div class="container-fluid p-0">
        <section class="resume-section">
            <div class="resume-section-content">
                <h3 class="mb-3">
                    Part
                    <span class="text-primary">Comparator</span>
                </h3>
                <div class="subheading mb-5">
                    Record - Joint Universal (Admin)
                </div>

                {{-- pesan sukses --}}
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                {{-- pesan error global --}}
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form id="recordForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="Id_Comparison" id="Id_Comparison" value="{{ $comparison->Id_Comparison }}">
                    <input type="hidden" name="No_Tractor_Record" id="No_Tractor_Record">
                    <input type="hidden" name="Production_Date_Record" id="Production_Date_Record">
                    <input type="hidden" name="Text_Record" id="Text_Record">
                    <input type="hidden" name="Predict_Record" id="Predict_Record">
                    <input type="hidden" name="Result_Record" id="Result_Record">

                    <div class="row mb-4">
                        <div class="col-6">
                            <div class="form-group mb-2">
                                <label for="No_Tractor_Display">No:</label>
                                <input type="text" class="form-control" id="No_Tractor_Display" placeholder="Scan QR"
                                    readonly>
                            </div>
                            <div class="form-group mb-2">
                                <label for="Model_Name_Display">Model Name:</label>
                                <input type="text" class="form-control" id="Model_Name_Display" placeholder="Scan QR"
                                    readonly>
                            </div>
                            <div class="form-group mb-2">
                                <label for="Text_Record_Display">Text Record:</label>
                                <input type="text" class="form-control" id="Text_Record_Display" placeholder="Scan QR"
                                    readonly>
                            </div>
                            <button type="button" id="scanQR" class="btn btn-primary text-white">Scan</button>
                        </div>
                        <div class="col-6" id="parent_qr">
                            <div class="form-group mb-2">
                                <label for="result">AI Status:</label>
                                <input type="text" class="form-control" name="result" id="result" readonly>
                            </div>
                            <div class="form-group mb-2">
                                <label for="Predict_Record_Display">Predict Record:</label>
                                <input type="text" class="form-control" id="Predict_Record_Display" placeholder="Take Photo"
                                    readonly>
                            </div>
                            <div class="form-group mb-2">
                                <label for="Result_Record_Display">Result:</label>
                                <input type="text" class="form-control" id="Result_Record_Display" placeholder="Empty"
                                    readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div id="reader_qr" style="width: 100%;"></div>
                        </div>
                        <div id="result-msg"></div>
                    </div>

                    <!-- Notifikasi validasi rule -->
                    <div id="validation-error-message" class="alert alert-danger alert-dismissible fade show mb-3"
                        role="alert" style="display: none;">
                        <strong id="validation-error-text"></strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>

                    <div class="row mb-4">
                        <div class="col-6">
                            <div class="form-group mb-2">
                                <label for="upload">Part Photo:</label>
                                <input type="file" class="form-control" name="Photo_Ng_Path" id="upload" accept="image/*"
                                    capture="environment" disabled />
                            </div>
                            <button type="button" class="btn btn-primary text-white mt-3" style="width: 100%" id="submitBtn"
                                disabled>Submit</button>
                        </div>
                        <div class="col-6">
                            <label for="preview">Preview Photo:</label>
                            <img id="preview" alt="Preview gambar" />
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </div>
@endsection

@section('style')
    <script src="{{ asset('assets/js/tfjs.js') }}"></script>
    <script src="{{ asset('assets/js/teachablemachine-image.min.js') }}"></script>
    <style>
        #preview {
            max-width: 100%;
            max-height: auto;
            display: none;
        }
    </style>
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('script')
    <script src="{{ asset('assets/js/html5-qrcode.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <script>
        const ID_COMPARISON = {{ $comparison->Id_Comparison }};

        var element = document.getElementById('parent_qr');
        var width = element.offsetWidth;

        const qrScanner = new Html5QrcodeScanner("reader_qr", {
            fps: 10,
            qrbox: {
                width: width,
                height: width,
            },
        });

        let currentTextRecord = '';

        async function onScanSuccess(decodedText, decodedResult) {
            const parts = decodedText.split(';');
            const no = parts[0].trim();
            const productionDate = parts[1] ? parts[1].trim() : '';

            document.getElementById("No_Tractor_Record").value = no;
            document.getElementById("No_Tractor_Display").value = no;
            document.getElementById("Production_Date_Record").value = productionDate;

            await validateRuleOnServer(no, productionDate);

            qrScanner.clear();
            checkResultRecord();
        }

        async function validateRuleOnServer(sequenceNo, productionDate) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            document.getElementById('validation-error-message').style.display = 'none';
            document.getElementById('submitBtn').disabled = true;
            document.getElementById('upload').disabled = true;

            try {
                const response = await fetch('/api/joint-universal/validate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        sequence_no: sequenceNo,
                        id_comparison: ID_COMPARISON,
                        production_date: productionDate
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    document.getElementById('Model_Name_Display').value = data.model_name || '-';

                    currentTextRecord = data.text_record || '';
                    document.getElementById('Text_Record_Display').value = currentTextRecord;
                    document.getElementById('Text_Record').value = currentTextRecord;

                    document.getElementById('upload').disabled = false;
                } else {
                    document.getElementById('validation-error-text').textContent = data.message;
                    document.getElementById('validation-error-message').style.display = 'block';
                    document.getElementById('submitBtn').disabled = true;
                    document.getElementById('upload').disabled = true;
                }
            } catch (error) {
                document.getElementById('validation-error-text').textContent = 'Gagal menghubungi server untuk validasi rule.';
                document.getElementById('validation-error-message').style.display = 'block';
                document.getElementById('submitBtn').disabled = true;
                document.getElementById('upload').disabled = true;
            }
        }

        document.getElementById("scanQR").addEventListener("click", () => {
            qrScanner.render(onScanSuccess);
            document.getElementById("reader_qr").scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        });

        const MODEL_URL = "{{ $comparison->model->Path_Model }}";
        let model, maxPredictions;

        async function loadModel() {
            document.getElementById('result').value = 'Loading...';
            model = await tmImage.load(MODEL_URL + "model.json", MODEL_URL + "metadata.json");
            maxPredictions = model.getTotalClasses();
            document.getElementById('result').value = 'Loading complete';
        }

        async function predictImage(imageElement) {
            if (!model) {
                alert('Loading incomplete');
                return;
            }

            const prediction = await model.predict(imageElement);
            let best = prediction.reduce((prev, curr) =>
                (curr.probability > prev.probability) ? curr : prev
            );

            const predictRecord = best.className.toUpperCase();
            document.getElementById('Predict_Record_Display').value = predictRecord;
            document.getElementById('Predict_Record').value = predictRecord;

            checkResultRecord();
        }

        document.getElementById('upload').addEventListener('change', e => {
            const file = e.target.files[0];
            if (!file) return;
            const url = URL.createObjectURL(file);
            const img = document.getElementById('preview');
            img.style.display = 'block';
            img.src = url;
            img.onload = () => predictImage(img);
        });

        function checkResultRecord() {
            const textRecord = document.getElementById("Text_Record").value.trim();
            const predictRecord = document.getElementById("Predict_Record").value.trim();
            const resultRecordDisplay = document.getElementById("Result_Record_Display");
            const resultRecordHidden = document.getElementById("Result_Record");

            if (textRecord === "" || predictRecord === "") {
                resultRecordDisplay.value = "";
                resultRecordHidden.value = "";
                renderResultMsg("empty", predictRecord, textRecord);
                document.getElementById('submitBtn').disabled = true;
            } else if (textRecord === predictRecord) {
                resultRecordDisplay.value = "OK";
                resultRecordHidden.value = "OK";
                renderResultMsg("ok", predictRecord, textRecord);
                document.getElementById('submitBtn').disabled = false;
            } else {
                resultRecordDisplay.value = "NG";
                resultRecordHidden.value = "NG";
                renderResultMsg("ng", predictRecord, textRecord);
                document.getElementById('submitBtn').disabled = false;
            }
        }

        function renderResultMsg(state, pred, text) {
            const el = document.getElementById("result-msg");
            if (state === "empty") { el.innerHTML = ""; return; }

            let alertClass = "alert-secondary", badgeClass = "text-bg-secondary", title = "", body = "";

            if (state === "ok") {
                alertClass = "alert-success"; badgeClass = "text-bg-success"; title = "Sesuai";
                body = `Text Record dan Predict Record cocok.`;
            } else if (state === "ng") {
                alertClass = "alert-danger"; badgeClass = "text-bg-danger"; title = "Tidak Sesuai";
                body = `Text Record dan Predict Record tidak cocok.`;
            }

            el.innerHTML = `
                <div class="alert ${alertClass} p-4 fs-5 d-flex align-items-start justify-content-between" role="alert" style="border-radius: 10px;">
                    <div>
                        <span class="badge ${badgeClass} me-3 p-3 fs-6">${state === "ok" ? "OK" : "NG"}</span>
                        <strong>${title}</strong><br>
                        <small class="fs-6">
                            Predict: <code>${pred || "-"}</code> |
                            Text: <code>${text || "-"}</code>
                        </small>
                        <div class="mt-2">${body}</div>
                    </div>
                </div>
            `;
        }

        document.getElementById('submitBtn').addEventListener('click', async function () {
            const form = document.getElementById('recordForm');
            const formData = new FormData(form);
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const fileInput = document.getElementById('upload');
            if (!fileInput.files || fileInput.files.length === 0) {
                alert('Harap ambil foto part terlebih dahulu.');
                return;
            }

            this.disabled = true;
            this.textContent = 'Submitting...';

            try {
                const response = await fetch('/api/joint-universal/save', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    body: formData
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    alert('Record berhasil disimpan!');
                    window.location.href = '{{ route("dashboard.admin") }}';
                } else {
                    let errorMsg = data.message || 'Gagal menyimpan record.';
                    if (data.errors) { errorMsg = Object.values(data.errors).flat().join('\n'); }
                    alert('Error: ' + errorMsg);
                    this.disabled = false;
                    this.textContent = 'Submit';
                }
            } catch (error) {
                alert('Gagal menghubungi server.');
                this.disabled = false;
                this.textContent = 'Submit';
            }
        });

        window.onload = loadModel;
    </script>
@endsection