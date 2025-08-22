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
                Dashboard
                <a href="{{ route('record', ['Id_Comparison' => 1]) }}"><button class="btn btn-primary text-white" type="button">Record Now</button></a>
            </div>
            <p class="lead mb-3">List Record:</p>
            <div class="col-xl-4 col-md-6 mb-3">
                <div class="card border-left-primary h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col-xl-12">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Choose Day
                                </div>
                                <form class="user" action="{{ route('dashboard.submit') }}" method="GET">
                                    @csrf
                                    <div class="row d-flex align-items-center">
                                        <div class="col-lg-8 col-md-6 mb-1">
                                            <input name="Day_Record" type="date" class="form-control form-control-user" value="{{ $date }}" required>
                                        </div>
                                        <div class="col-lg-4 col-md-6">
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
            <form class="user mb-3" action="{{ route('dashboard.export') }}" method="GET" target="_blank">
                <input name="Day_Record_Hidden" type="hidden" class="form-control form-control-user" value="{{ $date }}">
                <button class="d-sm-inline-block btn btn-md btn-primary text-white" type="submit">
                    <i class="fas fa-download fa-sm"></i> Download Report
                </button>
            </form>
            {{-- <button class="d-sm-inline-block btn btn-md btn-danger my-2" type="button" data-bs-toggle="modal" data-bs-target="#resetReportModal">
                <i class="fas fa-trash fa-sm"></i> Reset Report
            </button>
            <div class="modal fade" id="resetReportModal" tabindex="-1" role="dialog" aria-labelledby="resetReportModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header bg-danger">
                            <h5 class="modal-title text-white" id="exampleModalLabel">Reset Confirmation?</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div>Are you sure to reset records?</div>
                            <div>This action cannot be returned!</div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                            <a class="btn btn-danger" href="{{ route('dashboard.reset') }}">Reset</a>
                        </div>
                    </div>
                </div>
            </div> --}}
            <div class="table-responsive p-0">
                <table id="example" class="table align-items-center mb-0">
                    <thead>
                        <tr>
                            <th class="text-center text-primary font-weight-bolder">No</th>
                            <th class="text-center text-primary font-weight-bolder">No Tractor</th>
                            <th class="text-center text-primary font-weight-bolder">Name Tractor</th>
                            <th class="text-center text-primary font-weight-bolder">Comparison</th>
                            <th class="text-center text-primary font-weight-bolder">Part Detection</th>
                            <th class="text-center text-primary font-weight-bolder">Result</th>
                            <th class="text-center text-primary font-weight-bolder">Time Record</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ( $records as $record )
                        <tr>
                            <td class="align-middle text-center">
                                <p class="text-xs font-weight-bold text-secondary">{{ $loop->iteration }}</p>
                            </td>
                            <td class="align-middle text-center">
                                {{ $record->No_Tractor_Record }}
                            </td>
                            <td class="align-middle text-center">
                                {{ $record->tractor->Type_Tractor }}
                            </td>
                            <td class="align-middle text-center">
                                {{ $record->comparison->Name_Comparison }}
                            </td>
                            <td class="align-middle text-center">
                                {{ $record->part->Code_Part }}
                            </td>
                            <td class="align-middle text-center">
                                @if ($record->Result_Record === 'OK')
                                    <span class="badge bg-success">
                                        {{ $record->Result_Record }}
                                    </span>
                                @elseif ($record->Result_Record === 'NG')
                                    <span class="badge bg-danger view-detail"
                                        data-bs-toggle="modal"
                                        data-bs-target="#detailModal"
                                        data-id="{{ $record->Id_Record }}"
                                        data-no="{{ $record->No_Tractor_Record }}"
                                        data-type="{{ $record->tractor->Type_Tractor }}"
                                        data-comp="{{ $record->comparison->Name_Comparison }}"
                                        data-part="{{ $record->part->Code_Part }}"
                                        data-result="{{ $record->Result_Record }}"
                                        data-time="{{ \Carbon\Carbon::parse($record->Time_Record)->format('d-m-Y H:i:s') }}"
                                        data-photo="{{ $record->Photo_Ng_Path ? asset('uploads/'.$record->Photo_Ng_Path) : asset('storage/no-img.jpeg') }}"
                                        data-approve="true">
                                        {{ $record->Result_Record }}
                                    </span>
                                @elseif ($record->Result_Record === 'NG-OK')
                                    <span class="badge bg-warning view-detail"
                                        data-bs-toggle="modal"
                                        data-bs-target="#detailModal"
                                        data-id="{{ $record->Id_Record }}"
                                        data-no="{{ $record->No_Tractor_Record }}"
                                        data-type="{{ $record->tractor->Type_Tractor }}"
                                        data-comp="{{ $record->comparison->Name_Comparison }}"
                                        data-part="{{ $record->part->Code_Part }}"
                                        data-result="{{ $record->Result_Record }}"
                                        data-time="{{ \Carbon\Carbon::parse($record->Time_Record)->format('d-m-Y H:i:s') }}"
                                        data-photo="{{ $record->Photo_Ng_Path ? asset('uploads/'.$record->Photo_Ng_Path) : asset('storage/no-img.jpeg') }}"
                                        data-approvedby="{{ $record->user ? $record->user->Name_User : '-' }}"
                                        data-approve="false">
                                        {{ $record->Result_Record }}
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
                <!-- Modal -->
                <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title text-white" id="detailModalLabel">Detail Record</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <table class="table table-bordered">
                                    <tr><th>No Tractor</th><td id="modalNo"></td></tr>
                                    <tr><th>Tractor Type</th><td id="modalType"></td></tr>
                                    <tr><th>Comparison</th><td id="modalComp"></td></tr>
                                    <tr><th>Part Prediction</th><td id="modalPart"></td></tr>
                                    <tr>
                                        <th>Result</th>
                                        <td><span id="modalResult" class="badge"></span></td>
                                    </tr>
                                    <tr><th>Time</th><td id="modalTime"></td></tr>
                                    <tr id="approvedByRow" style="display:none;">
                                        <th>Approved By</th><td id="modalApprovedBy"></td>
                                    </tr>
                                </table>
                                {{-- <form id="approveForm" action="{{ route('dashboard.admin.approve') }}" method="POST" class="mb-3">
                                    @csrf
                                    <input type="hidden" name="record_id" id="modalRecordId">
                                    <button type="submit" id="approveBtn" class="btn btn-danger">Approve</button>
                                </form> --}}
                                <div class="text-center">
                                    <img id="modalPhoto" src="" alt="Foto NG" class="img-fluid rounded shadow">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
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

document.addEventListener('DOMContentLoaded', () => {
    const detailModal = document.getElementById('detailModal');
    const modalHeader = detailModal.querySelector('.modal-header');
    const modalTitle = detailModal.querySelector('.modal-title');
    const modalResult = document.getElementById('modalResult');
    // const approveForm = document.getElementById('approveForm');
    const approvedByRow = document.getElementById('approvedByRow');
    const modalApprovedBy = document.getElementById('modalApprovedBy');

    detailModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const result = button.getAttribute('data-result');
        const approvedBy = button.getAttribute('data-approvedby');

        // Isi data tabel
        // document.getElementById('modalRecordId').value = button.getAttribute('data-id');
        document.getElementById('modalNo').textContent = button.getAttribute('data-no');
        document.getElementById('modalType').textContent = button.getAttribute('data-type');
        document.getElementById('modalComp').textContent = button.getAttribute('data-comp');
        document.getElementById('modalPart').textContent = button.getAttribute('data-part');
        document.getElementById('modalTime').textContent = button.getAttribute('data-time');
        document.getElementById('modalPhoto').src = button.getAttribute('data-photo');

        // Reset header + badge style
        modalHeader.className = 'modal-header';
        modalResult.className = 'badge';

        if (result === 'NG') {
            modalHeader.classList.add('bg-danger', 'text-white');
            modalResult.classList.add('bg-danger');
            // approveForm.style.display = 'none';
        } else if (result === 'NG-OK') {
            modalHeader.classList.add('bg-warning', 'text-white');
            modalResult.classList.add('bg-warning');
            // approveForm.style.display = 'none';
            modalApprovedBy.textContent = approvedBy;
        } else { // OK
            modalHeader.classList.add('bg-success', 'text-white');
            modalResult.classList.add('bg-success');
            // approveForm.style.display = 'none';
        }

        if (result === 'NG-OK') {
            approvedByRow.style.display = '';
            modalApprovedBy.textContent = approvedBy;
        } else {
            approvedByRow.style.display = 'none';
            modalApprovedBy.textContent = '';
        }

        modalTitle.textContent = 'Detail Record';
        modalResult.textContent = result;
    });
});
</script>
@endsection