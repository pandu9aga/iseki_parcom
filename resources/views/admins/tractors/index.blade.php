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
                Tractor
            </div>

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
            <button class="btn btn-primary text-white mx-3 mb-3" data-bs-toggle="modal" data-bs-target="#addTractorModal">
                <span style="padding-left: 30px; padding-right: 30px;"><b>+</b> Add Tractor</span>
            </button>

            <div class="table-responsive">
                <table class="table align-items-center mb-0" id="example">
                    <thead>
                        <tr>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder">No</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder">Type Tractor</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder" style="width: 15%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tractors as $t)
                        <tr>
                            <td class="align-middle text-center">
                                <p class="text-xs font-weight-bold text-secondary">{{ $loop->iteration }}</p>
                            </td>
                            <td class="align-middle text-center text-primary">
                                {{ $t->Type_Tractor }}
                            </td>
                            <td class="align-middle text-center">
                                <div class="d-flex justify-content-center">
                                    <a href="#" class="btn btn-primary text-white text-xs mx-1"
                                        data-bs-toggle="modal" data-bs-target="#editTractorModal"
                                        onclick="setEditTractor({{ $t->Id_Tractor }}, '{{ addslashes($t->Type_Tractor) }}')">
                                        edit
                                    </a>
                                    <a href="#" class="btn btn-danger text-white text-xs mx-1"
                                        data-bs-toggle="modal" data-bs-target="#deleteTractorModal"
                                        onclick="setDeleteTractor({{ $t->Id_Tractor }}, '{{ addslashes($t->Type_Tractor) }}')">
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
<div class="modal fade" id="addTractorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('tractor.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">Add Tractor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Type Tractor</label>
                        <input type="text" name="Type_Tractor" class="form-control" required>
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
<div class="modal fade" id="editTractorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="editTractorForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">Edit Tractor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit-id" name="Id_Tractor">
                    <div class="form-group">
                        <label>Type Tractor</label>
                        <input type="text" id="edit-type" name="Type_Tractor" class="form-control" required>
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
<div class="modal fade" id="deleteTractorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="deleteTractorForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white">Delete Tractor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Yakin hapus tractor: <b id="delete-type"></b> ?</p>
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
    function setEditTractor(id, type) {
        document.getElementById('edit-id').value = id;
        document.getElementById('edit-type').value = type;
        document.getElementById('editTractorForm').action = `/iseki_parcom/public/tractor/${id}`;
    }

    function setDeleteTractor(id, type) {
        document.getElementById('delete-type').textContent = type;
        document.getElementById('deleteTractorForm').action = `/iseki_parcom/public/tractor/${id}`;
    }
</script>
@endsection