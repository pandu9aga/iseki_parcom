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
                <a href="{{ route('record.admin', ['Id_Comparison' => 1]) }}"><button class="btn btn-primary text-white" type="button">Record Now</button></a>
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
                                <form class="user" action="{{ route('dashboard.admin.submit') }}" method="GET">
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
            <form class="user mb-3" action="{{ route('dashboard.admin.export') }}" method="GET" target="_blank">
                <input name="Day_Record_Hidden" type="hidden" class="form-control form-control-user" value="{{ $date }}">
                <button class="d-sm-inline-block btn btn-md btn-primary text-white" type="submit">
                    <i class="fas fa-download fa-sm"></i> Download Report
                </button>
            </form>
            <button class="d-sm-inline-block btn btn-md btn-danger my-2" type="button" data-bs-toggle="modal" data-bs-target="#resetReportModal">
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
                                <span class="badge {{ $record->Result_Record === 'OK' ? 'bg-success' : 'bg-danger' }}">
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
@endsection

@section('style')
<link href="{{asset('assets/datatables/datatables.min.css')}}" rel="stylesheet">
@endsection

@section('script')
<script src="{{ asset('assets/js/jquery.min.js') }}"></script>
<script src="{{asset('assets/datatables/datatables.min.js')}}"></script>
<script>
new DataTable('#example');
</script>
@endsection