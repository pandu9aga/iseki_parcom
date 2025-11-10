@extends('layouts.admin')
@section('content')
<div class="container-fluid p-0">
    <section class="resume-section">
        <div class="resume-section-content">
            <h3 class="mb-3">
                Comparison
                <span class="text-primary">Management</span>
            </h3>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Tombol Tambah -->
            <button class="btn btn-primary text-white mx-3 mb-3" data-bs-toggle="modal" data-bs-target="#addComparisonModal">
                <span style="padding-left: 30px; padding-right: 30px;"><b>+</b> Add Comparison</span>
            </button>

            <div class="table-responsive">
                <table class="table align-items-center mb-0" id="example">
                    <thead>
                        <tr>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder">No</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder">Name Comparison</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder">Model AI</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder" style="width: 15%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($comparisons as $c)
                        <tr>
                            <td class="align-middle text-center">
                                <p class="text-xs font-weight-bold text-secondary">{{ $loop->iteration }}</p>
                            </td>
                            <td class="align-middle text-center text-primary">{{ $c->Name_Comparison }}</td>
                            <td class="align-middle text-center text-secondary">
                                {{ $c->model ? $c->model->Name_Model : '—' }}
                            </td>
                            <td class="align-middle text-center">
                                <div class="d-flex justify-content-center">
                                    <a href="#" class="btn btn-primary text-white text-xs mx-1"
                                        data-bs-toggle="modal" data-bs-target="#editComparisonModal"
                                        onclick="setEditComparison({{ $c->Id_Comparison }}, {{ json_encode($c->Name_Comparison) }}, {{ $c->Id_Model }})">
                                        edit
                                    </a>
                                    <a href="#" class="btn btn-danger text-white text-xs mx-1"
                                        data-bs-toggle="modal" data-bs-target="#deleteComparisonModal"
                                        onclick="setDeleteComparison({{ $c->Id_Comparison }}, {{ json_encode($c->Name_Comparison) }})">
                                        delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<!-- Modal Add -->
<div class="modal fade" id="addComparisonModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('comparison.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">Add Comparison</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label>Name Comparison</label>
                        <input type="text" name="Name_Comparison" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label>Model AI</label>
                        <select name="Id_Model" class="form-select" required>
                            <option value="">-- Pilih Model AI --</option>
                            @foreach($models ?? App\Models\ModelAi::all() as $model)
                                <option value="{{ $model->Id_Model }}">{{ $model->Name_Model }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary text-white">Submit</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="editComparisonModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="editComparisonForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">Edit Comparison</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit-id" name="Id_Comparison">
                    <div class="form-group mb-3">
                        <label>Name Comparison</label>
                        <input type="text" id="edit-name" name="Name_Comparison" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label>Model AI</label>
                        <select id="edit-model" name="Id_Model" class="form-select" required>
                            <option value="">-- Pilih Model AI --</option>
                            @foreach(App\Models\ModelAi::all() as $model)
                                <option value="{{ $model->Id_Model }}">{{ $model->Name_Model }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary text-white">Update</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Delete -->
<div class="modal fade" id="deleteComparisonModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="deleteComparisonForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white">Delete Comparison</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Yakin hapus comparison: <b id="delete-name"></b> ?</p>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger">Delete</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('style')
<link href="{{ asset('assets/datatables/datatables.min.css') }}" rel="stylesheet">
@endsection

@section('script')
<script src="{{ asset('assets/js/jquery.min.js') }}"></script>
<script src="{{ asset('assets/datatables/datatables.min.js') }}"></script>
<script>
    new DataTable('#example');
</script>
<script>
    function setEditComparison(id, name, modelId) {
        document.getElementById('edit-id').value = id;
        document.getElementById('edit-name').value = name;
        document.getElementById('edit-model').value = modelId;
        document.getElementById('editComparisonForm').action = `/iseki_parcom/public/comparison/${id}`;
    }

    function setDeleteComparison(id, name) {
        document.getElementById('delete-name').textContent = name;
        document.getElementById('deleteComparisonForm').action = `/iseki_parcom/public/comparison/${id}`;
    }
</script>
@endsection