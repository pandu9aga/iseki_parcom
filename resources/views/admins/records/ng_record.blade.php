@extends('layouts.admin')

@section('content')
    <div class="container-fluid p-0">
        <section class="resume-section">
            <div class="resume-section-content">
                <h3 class="mb-3">
                    NG Record
                    <span class="text-primary">List</span>
                </h3>
                <div class="subheading mb-5">
                    Unvalidated NG Records
                </div>
                
                <div class="table-responsive p-0">
                    <table id="example" class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-center text-primary font-weight-bolder">No</th>
                                <th class="text-center text-primary font-weight-bolder">No Tractor</th>
                                <th class="text-center text-primary font-weight-bolder">Name Tractor</th>
                                <th class="text-center text-primary font-weight-bolder">Comparison</th>
                                <th class="text-center text-primary font-weight-bolder">Part Detection</th>
                                <th class="text-center text-primary font-weight-bolder">Correct Text</th>
                                <th class="text-center text-primary font-weight-bolder">Text Detection</th>
                                <th class="text-center text-primary font-weight-bolder">Result</th>
                                <th class="text-center text-primary font-weight-bolder">Time Record</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($records as $record)
                                <tr>
                                    <td class="align-middle text-center">
                                        <p class="text-xs font-weight-bold text-secondary">{{ $loop->iteration }}</p>
                                    </td>
                                    <td class="align-middle text-center">
                                        {{ $record->No_Tractor_Record ?? '-' }}
                                    </td>
                                    <td class="align-middle text-center">
                                        {{ $record->tractor_name }}
                                    </td>
                                    <td class="align-middle text-center">
                                        {{ optional($record->comparison)->Name_Comparison ?? '-' }}
                                    </td>
                                    <td class="align-middle text-center">
                                        {{ optional($record->part)->Code_Part ?? '-' }}
                                    </td>
                                    <td class="align-middle text-center">
                                        {{ $record->Text_Record ?? '-' }}
                                    </td>
                                    <td class="align-middle text-center">
                                        {{ $record->Predict_Record ?? '-' }}
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="badge bg-danger view-detail" data-bs-toggle="modal"
                                            data-bs-target="#detailModal" data-id="{{ $record->Id_Record }}"
                                            data-no="{{ $record->No_Tractor_Record }}" data-type="{{ $record->tractor_name }}"
                                            data-comp="{{ optional($record->comparison)->Name_Comparison ?? '-' }}"
                                            data-part="{{ optional($record->part)->Code_Part ?? '-' }}"
                                            data-result="{{ $record->Result_Record }}"
                                            data-time="{{ \Carbon\Carbon::parse($record->Time_Record)->format('d-m-Y H:i:s') }}"
                                            data-photo="{{ $record->Photo_Ng_Path ? asset('uploads/' . $record->Photo_Ng_Path) : null }}"
                                            data-photo-two="{{ $record->Photo_Ng_Path_Two ? asset('uploads/' . $record->Photo_Ng_Path_Two) : null }}"
                                            data-text="{{ $record->Text_Record ?? null }}"
                                            data-predict="{{ $record->Predict_Record ?? null }}" data-approve="true">
                                            {{ $record->Result_Record }}
                                        </span>
                                    </td>
                                    <td class="align-middle text-center">
                                        {{ \Carbon\Carbon::parse($record->Time_Record)->format('d-m-Y H:i:s') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>

    <!-- Modal Detail -->
    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title text-white" id="detailModalLabel">Detail Record</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered">
                        <tr>
                            <th>No Tractor</th>
                            <td id="modalNo"></td>
                        </tr>
                        <tr>
                            <th>Tractor Type</th>
                            <td id="modalType"></td>
                        </tr>
                        <tr>
                            <th>Comparison</th>
                            <td id="modalComp"></td>
                        </tr>
                        <tr>
                            <th>Part Detection</th>
                            <td id="modalPart"></td>
                        </tr>
                        <tr>
                            <th>Result</th>
                            <td><span id="modalResult" class="badge"></span></td>
                        </tr>
                        <tr>
                            <th>Time</th>
                            <td id="modalTime"></td>
                        </tr>
                        <tr id="textRecordRow" style="display: none;">
                            <th>Text Record</th>
                            <td id="modalTextRecord"></td>
                        </tr>
                        <tr id="predictRecordRow" style="display: none;">
                            <th>Predict Record</th>
                            <td id="modalPredictRecord"></td>
                        </tr>
                        <tr id="approvedByRow" style="display:none;">
                            <th>Approved By</th>
                            <td id="modalApprovedBy"></td>
                        </tr>
                        <tr id="photosRow" style="display: none;">
                            <th>Foto</th>
                            <td>
                                <div class="d-flex flex-wrap gap-2">
                                    <div class="flex-fill text-center">
                                        <small class="text-muted">Foto Part</small><br>
                                        <a id="modalPhotoLink" href="#" target="_blank">
                                            <img id="modalPhoto" src="" alt="Foto Part" class="img-fluid rounded shadow"
                                                style="max-height: 200px; object-fit: contain; cursor: pointer;">
                                        </a>
                                    </div>
                                    <div class="flex-fill text-center">
                                        <small class="text-muted">Foto OCR</small><br>
                                        <a id="modalPhotoTwoLink" href="#" target="_blank">
                                            <img id="modalPhotoTwo" src="" alt="Foto OCR" class="img-fluid rounded shadow"
                                                style="max-height: 200px; object-fit: contain; cursor: pointer;">
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>
                    <form id="approveForm" action="{{ route('dashboard.admin.approve') }}" method="POST" class="mb-3">
                        @csrf
                        <input type="hidden" name="record_id" id="modalRecordId">
                        <button type="submit" id="approveBtn" class="btn btn-danger">Approve</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('style')
    <link href="{{asset('assets/datatables/datatables.min.css')}}" rel="stylesheet">
@endsection

@section('script')
    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <script src="{{asset('assets/datatables/datatables.min.js')}}"></script>
    <script>
        new DataTable('#example');

        // Script untuk modal detail
        document.getElementById('detailModal').addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const no = button.getAttribute('data-no');
            const type = button.getAttribute('data-type');
            const comp = button.getAttribute('data-comp');
            const part = button.getAttribute('data-part');
            const result = button.getAttribute('data-result');
            const time = button.getAttribute('data-time');
            const photo = button.getAttribute('data-photo'); 
            const photoTwo = button.getAttribute('data-photo-two'); 
            const textRecord = button.getAttribute('data-text'); 
            const predictRecord = button.getAttribute('data-predict'); 
            const needsApproval = button.getAttribute('data-approve') === 'true';

            // Isi data
            document.getElementById('modalRecordId').value = id;
            document.getElementById('modalNo').textContent = no;
            document.getElementById('modalType').textContent = type;
            document.getElementById('modalComp').textContent = comp;
            document.getElementById('modalPart').textContent = part;
            document.getElementById('modalTime').textContent = time;

            // Reset badge dan header
            const modalResult = document.getElementById('modalResult');
            const modalHeader = this.querySelector('.modal-header');
            modalResult.className = 'badge';
            modalHeader.className = 'modal-header';

            // Set warna berdasarkan result
            modalResult.classList.add('bg-danger');
            modalHeader.classList.add('bg-danger');
            if (needsApproval) {
                document.getElementById('approveForm').style.display = 'block';
            } else {
                document.getElementById('approveForm').style.display = 'none';
            }
            modalResult.textContent = result;

            // Text Record
            const textRecordRow = document.getElementById('textRecordRow');
            const modalTextRecord = document.getElementById('modalTextRecord');
            if (textRecord) {
                modalTextRecord.textContent = textRecord;
                textRecordRow.style.display = '';
            } else {
                modalTextRecord.textContent = '';
                textRecordRow.style.display = 'none';
            }

            // Predict Record
            const predictRecordRow = document.getElementById('predictRecordRow');
            const modalPredictRecord = document.getElementById('modalPredictRecord');
            if (predictRecord) {
                modalPredictRecord.textContent = predictRecord;
                predictRecordRow.style.display = '';
            } else {
                modalPredictRecord.textContent = '';
                predictRecordRow.style.display = 'none';
            }

            // Foto Part & OCR
            const photosRow = document.getElementById('photosRow');
            const modalPhoto = document.getElementById('modalPhoto');
            const modalPhotoLink = document.getElementById('modalPhotoLink');
            const modalPhotoTwo = document.getElementById('modalPhotoTwo');
            const modalPhotoTwoLink = document.getElementById('modalPhotoTwoLink');

            const hasPhoto1 = !!photo;
            const hasPhoto2 = !!photoTwo;

            if (hasPhoto1 || hasPhoto2) {
                photosRow.style.display = '';
                if (hasPhoto1) {
                    modalPhoto.src = photo;
                    modalPhoto.style.display = 'block';
                    modalPhotoLink.href = photo;
                } else {
                    modalPhoto.src = '';
                    modalPhoto.style.display = 'none';
                    modalPhotoLink.href = '#';
                }
                if (hasPhoto2) {
                    modalPhotoTwo.src = photoTwo;
                    modalPhotoTwo.style.display = 'block';
                    modalPhotoTwoLink.href = photoTwo;
                } else {
                    modalPhotoTwo.src = '';
                    modalPhotoTwo.style.display = 'none';
                    modalPhotoTwoLink.href = '#';
                }
            } else {
                photosRow.style.display = 'none';
                modalPhoto.src = '';
                modalPhoto.style.display = 'none';
                modalPhotoLink.href = '#';
                modalPhotoTwo.src = '';
                modalPhotoTwo.style.display = 'none';
                modalPhotoTwoLink.href = '#';
            }

            // Approved By 
            const approvedByRow = document.getElementById('approvedByRow');
            approvedByRow.style.display = 'none';
        });
    </script>
@endsection
