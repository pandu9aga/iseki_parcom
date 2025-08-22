@extends('layouts.main')
@section('content')
<div class="container-fluid p-0">
    <section class="resume-section">
        <div class="resume-section-content">
            <h3 class="mb-3">
                Part
                <span class="text-primary">Comparator</span>
            </h3>
            <div class="subheading mb-5">
                Record
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
            
            <form action="{{ route('record.insert') }}" role="form" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row mb-4">
                    <div class="col-6">
                        <div class="form-group mb-2">
                            <label for="No_Tractor_Record">No:</label>
                            <input type="text" class="form-control" name="No_Tractor_Record" id="No_Tractor_Record" placeholder="Scan QR" readonly required>
                        </div>
                        <div class="form-group mb-2">
                            <label for="Type_Tractor">Tractor Type:</label>
                            <input type="text" class="form-control" name="Type_Tractor" id="Type_Tractor" placeholder="Scan QR" readonly required>
                            <input type="hidden" name="Id_Comparison" value="{{ $comparison->Id_Comparison }}" required>
                            <input type="hidden" name="Id_Tractor" id="Id_Tractor" required>
                        </div>
                        <div class="form-group mb-2">
                            <label for="Code_Part">Code Part:</label>
                            <input type="text" class="form-control" name="Code_Part" id="Code_Part" placeholder="Scan QR" readonly required>
                        </div>
                        <button type="button" id="scanQR" class="btn btn-primary text-white">Scan</button>
                    </div>
                    <div class="col-6" id="parent_qr">
                        <div class="form-group mb-2">
                            <label for="result">AI Status:</label>
                            <input type="text" class="form-control" name="result" id="result" readonly>
                        </div>
                        <div class="form-group mb-2">
                            <label for="Code_Part_Prediction">Prediction:</label>
                            <input type="text" class="form-control" name="Code_Part_Prediction" id="Code_Part_Prediction" placeholder="Take Photo" readonly required>
                            <input type="hidden" name="Id_Part" id="Id_Part" required>
                        </div>
                        <div class="form-group mb-2">
                            <label for="Result_Record">Result:</label>
                            <input type="text" class="form-control" name="Result_Record" id="Result_Record" placeholder="Empty" readonly required>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div id="reader_qr" style="width: 100%;"></div>
                    </div>
                    <div id="result-msg"></div>
                </div>
                <div class="row mb-4">
                    <div class="col-6">
                        <div class="form-group mb-2">
                            <label for="upload">Part Photo:</label>
                            <input type="file" class="form-control" name="Photo_Ng_Path" id="upload" accept="image/*" capture="environment" />
                        </div>
                        <button type="submit" class="btn btn-primary text-white mt-3" style="width: 100%">Submit</button>
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
    #preview { max-width: 100%; max-height: auto; display: none; }
</style>
@endsection

@section('script')
<script src="{{ asset('assets/js/html5-qrcode.min.js') }}"></script>
<script src="{{ asset('assets/js/jquery.min.js') }}"></script>
<script>
    const listComparisons = @json($list_comparisons);

    var element = document.getElementById('parent_qr');
    var width = element.offsetWidth;

    const qrScanner = new Html5QrcodeScanner("reader_qr", {
        fps: 10,
        qrbox: {
            width: width,
            height: width,
        },
    });

    async function onScanSuccess(decodedText, decodedResult) {
        const no = decodedText.split(';')[0].trim();
        const typeRaw = decodedText.split(';')[2].trim();

        document.getElementById("No_Tractor_Record").value = no;

        const match = listComparisons.find(item => {
            return item.tractor && typeRaw.startsWith(item.tractor.Type_Tractor);
        });

        if (match) {
            document.getElementById("Id_Tractor").value = match.tractor.Id_Tractor;
            document.getElementById("Type_Tractor").value = match.tractor.Type_Tractor;
            document.getElementById("Code_Part").value = match.part.Code_Part;
        } else {
            document.getElementById("Id_Tractor").value = '';
            document.getElementById("Type_Tractor").value = '';
            document.getElementById("Code_Part").value = '';
        }

        qrScanner.clear();
        checkResultRecord();
    }

    document.getElementById("scanQR").addEventListener("click", () => {
        qrScanner.render(onScanSuccess);

        // Scroll ke elemen reader_qr
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
        console.log("Prediction raw:", prediction);

        let best = prediction.reduce((prev, curr) =>
            (curr.probability > prev.probability) ? curr : prev
        );

        document.getElementById('Code_Part_Prediction').value = best.className;

        const match = listComparisons.find(item =>
            item.part && item.part.Code_Part === best.className
        );

        if (match) {
            document.getElementById("Id_Part").value = match.part.Id_Part;
        } else {
            document.getElementById("Id_Part").value = '';
        }

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
        const codePartPrediction = document.getElementById("Code_Part_Prediction").value.trim();
        const codePart = document.getElementById("Code_Part").value.trim();
        const resultRecord = document.getElementById("Result_Record");

        if (codePartPrediction === "" || codePart === "") {
            resultRecord.value = "";
            renderResultMsg("empty", codePartPrediction, codePart);
        } else if (codePartPrediction === codePart) {
            resultRecord.value = "OK";
            renderResultMsg("ok", codePartPrediction, codePart);
        } else {
            resultRecord.value = "NG";
            renderResultMsg("ng", codePartPrediction, codePart);
        }
    }

    function renderResultMsg(state, pred, code) {
        const el = document.getElementById("result-msg");

        if (state === "empty") {
            el.innerHTML = "";
            return;
        }

        let alertClass = "alert-secondary";
        let badgeClass = "text-bg-secondary";
        let title = "";
        let body = "";

        if (state === "ok") {
            alertClass = "alert-success";
            badgeClass = "text-bg-success";
            title = "Sesuai";
            body = `Prediksi cocok dengan kode part.`;
        } else if (state === "ng") {
            alertClass = "alert-danger";
            badgeClass = "text-bg-danger";
            title = "Tidak Sesuai";
            body = `Prediksi tidak cocok dengan kode part.`;
        }

        el.innerHTML = `
            <div class="alert ${alertClass} p-4 fs-5 d-flex align-items-start justify-content-between" role="alert" style="border-radius: 10px;">
                <div>
                    <span class="badge ${badgeClass} me-3 p-3 fs-6">${state === "ok" ? "OK" : "NG"}</span>
                    <strong>${title}</strong><br>
                    <small class="fs-6">
                        Prediksi: <code>${pred || "-"}</code> |
                        Kode Part: <code>${code || "-"}</code>
                    </small>
                    <div class="mt-2">${body}</div>
                </div>
            </div>
        `;
    }

    window.onload = loadModel;
</script>
@endsection