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
                Dashboard
                <!-- Ganti tombol Record Now menjadi dropdown -->
                {{-- <div class="dropdown d-inline-block ms-2">
                    <button class="btn btn-primary dropdown-toggle text-white" type="button" id="recordNowDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        Record Now
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="recordNowDropdown">
                        <li><a class="dropdown-item" href="{{ route('record.admin', ['Id_Comparison' => 1]) }}">Ring Synchronizer</a></li>
                        <li><a class="dropdown-item" href="{{ route('record.admin', ['Id_Comparison' => 2]) }}">Bearing KBC</a></li>
                        <li><a class="dropdown-item" href="{{ route('record.admin', ['Id_Comparison' => 3]) }}">Bearing KOYO</a></li>
                    </ul>
                </div> --}}
            </div>
            <p class="lead mb-3">List Record:</p>
            <div class="col-xl-4 col-md-6 mb-3">
                <div class="card border-left-primary h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col-xl-12">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Choose Day & Comparison
                                </div>
                                <form class="user" action="{{ route('dashboard.admin.submit') }}" method="GET">
                                    @csrf
                                    <div class="row d-flex align-items-center">
                                        <div class="col-lg-4 col-md-4 mb-1">
                                            <input name="Day_Record" type="date" class="form-control form-control-user" value="{{ $dateFormatted }}" required>
                                        </div>
                                        <div class="col-lg-4 col-md-4 mb-1">
                                            <select name="Id_Comparison" class="form-control">
                                                @foreach($availableComparisons as $comp)
                                                    <option value="{{ $comp->Id_Comparison }}" {{ $comp->Id_Comparison == $selectedComparisonId ? 'selected' : '' }}>
                                                        {{ $comp->Name_Comparison }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-lg-4 col-md-4">
                                            <button class="d-sm-inline btn btn-md btn-primary text-white" type="submit">
                                                Apply
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Form Export dengan Id_Comparison_Hidden -->
            <form class="user mb-3" action="{{ route('dashboard.admin.export') }}" method="GET" target="_blank">
                <input name="Day_Record_Hidden" type="hidden" value="{{ $dateFormatted }}">
                <input name="Id_Comparison_Hidden" type="hidden" value="{{ $selectedComparisonId }}">
                <button class="d-sm-inline-block btn btn-md btn-primary text-white" type="submit">
                    <i class="fas fa-download fa-sm"></i> Download Report
                </button>
            </form>
            {{-- <button class="d-sm-inline-block btn btn-md btn-danger my-2" type="button" data-bs-toggle="modal" data-bs-target="#resetReportModal">
                <i class="fas fa-trash fa-sm"></i> Reset Report
            </button> --}}
            <!-- Modal Reset -->
            <div class="modal fade" id="resetReportModal" tabindex="-1" role="dialog" aria-labelledby="resetReportModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title" id="exampleModalLabel">Reset Confirmation?</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div>Are you sure to reset records?</div>
                            <div>This action cannot be returned!</div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                            <a class="btn btn-danger" href="{{ route('dashboard.admin.reset') }}">Reset</a>
                        </div>
                    </div>
                </div>
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
                                    {{ $record->plan->Model_Name_Plan ?? '-' }}
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
                                    @if ($record->Result_Record === 'OK')
                                        <span class="badge bg-success view-detail"
                                            data-bs-toggle="modal"
                                            data-bs-target="#detailModal"
                                            data-id="{{ $record->Id_Record }}"
                                            data-no="{{ $record->No_Tractor_Record }}"
                                            data-type="{{ $record->plan->Model_Name_Plan ?? '-' }}"
                                            data-comp="{{ optional($record->comparison)->Name_Comparison ?? '-' }}"
                                            data-part="{{ optional($record->part)->Code_Part ?? '-' }}"
                                            data-result="{{ $record->Result_Record }}"
                                            data-time="{{ \Carbon\Carbon::parse($record->Time_Record)->format('d-m-Y H:i:s') }}"
                                            data-photo="{{ $record->Photo_Ng_Path ? asset('uploads/'.$record->Photo_Ng_Path) : null }}"
                                            data-photo-two="{{ $record->Photo_Ng_Path_Two ? asset('uploads/'.$record->Photo_Ng_Path_Two) : null }}"
                                            data-text="{{ $record->Text_Record ?? null }}"
                                            data-predict="{{ $record->Predict_Record ?? null }}"
                                            data-approve="false"
                                            data-approvedby=""
                                        >
                                            {{ $record->Result_Record }}
                                        </span>
                                    @elseif ($record->Result_Record === 'NG')
                                        <span class="badge bg-danger view-detail"
                                            data-bs-toggle="modal"
                                            data-bs-target="#detailModal"
                                            data-id="{{ $record->Id_Record }}"
                                            data-no="{{ $record->No_Tractor_Record }}"
                                            data-type="{{ $record->plan->Model_Name_Plan ?? '-' }}"
                                            data-comp="{{ optional($record->comparison)->Name_Comparison ?? '-' }}"
                                            data-part="{{ optional($record->part)->Code_Part ?? '-' }}"
                                            data-result="{{ $record->Result_Record }}"
                                            data-time="{{ \Carbon\Carbon::parse($record->Time_Record)->format('d-m-Y H:i:s') }}"
                                            data-photo="{{ $record->Photo_Ng_Path ? asset('uploads/'.$record->Photo_Ng_Path) : null }}"
                                            data-photo-two="{{ $record->Photo_Ng_Path_Two ? asset('uploads/'.$record->Photo_Ng_Path_Two) : null }}"
                                            data-text="{{ $record->Text_Record ?? null }}"
                                            data-predict="{{ $record->Predict_Record ?? null }}"
                                            data-approve="true"
                                        >
                                            {{ $record->Result_Record }}
                                        </span>
                                    @elseif ($record->Result_Record === 'NG-OK')
                                        <span class="badge bg-warning view-detail"
                                            data-bs-toggle="modal"
                                            data-bs-target="#detailModal"
                                            data-id="{{ $record->Id_Record }}"
                                            data-no="{{ $record->No_Tractor_Record }}"
                                            data-type="{{ $record->plan->Model_Name_Plan ?? '-' }}"
                                            data-comp="{{ optional($record->comparison)->Name_Comparison ?? '-' }}"
                                            data-part="{{ optional($record->part)->Code_Part ?? '-' }}"
                                            data-result="{{ $record->Result_Record }}"
                                            data-time="{{ \Carbon\Carbon::parse($record->Time_Record)->format('d-m-Y H:i:s') }}"
                                            data-photo="{{ $record->Photo_Ng_Path ? asset('uploads/'.$record->Photo_Ng_Path) : null }}"
                                            data-photo-two="{{ $record->Photo_Ng_Path_Two ? asset('uploads/'.$record->Photo_Ng_Path_Two) : null }}"
                                            data-text="{{ $record->Text_Record ?? null }}"
                                            data-predict="{{ $record->Predict_Record ?? null }}"
                                            data-approvedby="{{ optional($record->user)->Name_User ?? '-' }}"
                                            data-approve="false"
                                        >
                                            {{ $record->Result_Record }}
                                        </span>
                                    @else
                                        <!-- Untuk nilai Result_Record lainnya -->
                                        <span class="badge bg-secondary view-detail"
                                            data-bs-toggle="modal"
                                            data-bs-target="#detailModal"
                                            data-id="{{ $record->Id_Record }}"
                                            data-no="{{ $record->No_Tractor_Record }}"
                                            data-type="{{ $record->plan->Model_Name_Plan ?? '-' }}"
                                            data-comp="{{ optional($record->comparison)->Name_Comparison ?? '-' }}"
                                            data-part="{{ optional($record->part)->Code_Part ?? '-' }}"
                                            data-result="{{ $record->Result_Record }}"
                                            data-time="{{ \Carbon\Carbon::parse($record->Time_Record)->format('d-m-Y H:i:s') }}"
                                            data-photo="{{ $record->Photo_Ng_Path ? asset('uploads/'.$record->Photo_Ng_Path) : null }}"
                                            data-photo-two="{{ $record->Photo_Ng_Path_Two ? asset('uploads/'.$record->Photo_Ng_Path_Two) : null }}"
                                            data-text="{{ $record->Text_Record ?? null }}"
                                            data-predict="{{ $record->Predict_Record ?? null }}"
                                            data-approvedby="{{ optional($record->user)->Name_User ?? '-' }}"
                                            data-approve="false"
                                        >
                                            {{ $record->Result_Record ?? '-' }}
                                        </span>
                                    @endif
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
                <h5 class="modal-title" id="detailModalLabel">Detail Record</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <tr><th>No Tractor</th><td id="modalNo"></td></tr>
                    <tr><th>Tractor Type</th><td id="modalType"></td></tr>
                    <tr><th>Comparison</th><td id="modalComp"></td></tr>
                    <tr><th>Part Detection</th><td id="modalPart"></td></tr>
                    <tr>
                        <th>Result</th>
                        <td><span id="modalResult" class="badge"></span></td>
                    </tr>
                    <tr><th>Time</th><td id="modalTime"></td></tr>
                    <!-- 🔥 Baris baru: Text Record -->
                    <tr id="textRecordRow" style="display: none;">
                        <th>Text Record</th><td id="modalTextRecord"></td>
                    </tr>
                    <!-- 🔥 Baris baru: Predict Record -->
                    <tr id="predictRecordRow" style="display: none;">
                        <th>Predict Record</th><td id="modalPredictRecord"></td>
                    </tr>
                    <tr id="approvedByRow" style="display:none;">
                        <th>Approved By</th><td id="modalApprovedBy"></td>
                    </tr>
                    <tr id="photosRow" style="display: none;">
                        <th>Foto</th>
                        <td>
                            <div class="d-flex flex-wrap gap-2">
                                <div class="flex-fill text-center">
                                    <small class="text-muted">Foto Part</small><br>
                                    <a id="modalPhotoLink" href="#" target="_blank">
                                        <img id="modalPhoto" src="" alt="Foto Part" class="img-fluid rounded shadow" style="max-height: 200px; object-fit: contain; cursor: pointer;">
                                    </a>
                                </div>
                                <div class="flex-fill text-center">
                                    <small class="text-muted">Foto OCR</small><br>
                                    <a id="modalPhotoTwoLink" href="#" target="_blank">
                                        <img id="modalPhotoTwo" src="" alt="Foto OCR" class="img-fluid rounded shadow" style="max-height: 200px; object-fit: contain; cursor: pointer;">
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
    const photo = button.getAttribute('data-photo'); // Bisa null
    const photoTwo = button.getAttribute('data-photo-two'); // Bisa null
    const textRecord = button.getAttribute('data-text'); // Bisa null
    const predictRecord = button.getAttribute('data-predict'); // Bisa null
    const needsApproval = button.getAttribute('data-approve') === 'true';
    const approvedBy = button.getAttribute('data-approvedby'); // Bisa null

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
    if (result === 'OK') {
        modalResult.classList.add('bg-success');
        modalHeader.classList.add('bg-success');
        document.getElementById('approveForm').style.display = 'none';
    } else if (result === 'NG') {
        modalResult.classList.add('bg-danger');
        modalHeader.classList.add('bg-danger');
        if(needsApproval) {
            document.getElementById('approveForm').style.display = 'block';
        } else {
            document.getElementById('approveForm').style.display = 'none';
        }
    } else { // NG-OK atau hasil lainnya
        modalResult.classList.add('bg-warning'); // Atau bg-secondary untuk hasil tak dikenal
        modalHeader.classList.add('bg-warning'); // Atau bg-secondary
        document.getElementById('approveForm').style.display = 'none';
    }
    modalResult.textContent = result;

    // --- 🔥 HANDLE FIELD TAMBAHAN ---

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

    // --- 🔥 FOTO PART & OCR BERSEBELAHAN ---
    const photosRow = document.getElementById('photosRow');
    const modalPhoto = document.getElementById('modalPhoto');
    const modalPhotoLink = document.getElementById('modalPhotoLink'); // 🔥 Ambil elemen link
    const modalPhotoTwo = document.getElementById('modalPhotoTwo');
    const modalPhotoTwoLink = document.getElementById('modalPhotoTwoLink'); // 🔥 Ambil elemen link

    const hasPhoto1 = !!photo;
    const hasPhoto2 = !!photoTwo;

    if (hasPhoto1 || hasPhoto2) {
        photosRow.style.display = '';
        // Set foto pertama
        if (hasPhoto1) {
            modalPhoto.src = photo;
            modalPhoto.style.display = 'block';
            modalPhotoLink.href = photo; // 🔥 Set href link ke URL foto
        } else {
            modalPhoto.src = '';
            modalPhoto.style.display = 'none';
            modalPhotoLink.href = '#'; // 🔥 Kosongkan href jika tidak ada foto
        }
        // Set foto kedua
        if (hasPhoto2) {
            modalPhotoTwo.src = photoTwo;
            modalPhotoTwo.style.display = 'block';
            modalPhotoTwoLink.href = photoTwo; // 🔥 Set href link ke URL foto
        } else {
            modalPhotoTwo.src = '';
            modalPhotoTwo.style.display = 'none';
            modalPhotoTwoLink.href = '#'; // 🔥 Kosongkan href jika tidak ada foto
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

    // Approved By (hanya muncul jika NG-OK dan approvedBy ada)
    const approvedByRow = document.getElementById('approvedByRow');
    const modalApprovedBy = document.getElementById('modalApprovedBy');
    if (result === 'NG-OK' && approvedBy) {
        modalApprovedBy.textContent = approvedBy;
        approvedByRow.style.display = '';
    } else {
        modalApprovedBy.textContent = '';
        approvedByRow.style.display = 'none';
    }
});
</script>
@endsection