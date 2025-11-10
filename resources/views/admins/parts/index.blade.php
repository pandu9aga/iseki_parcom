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
                Part
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
            <button class="btn btn-primary text-white mx-3 mb-3" data-bs-toggle="modal" data-bs-target="#addPartModal">
                <span style="padding-left: 30px; padding-right: 30px;"><b>+</b> Add Part</span>
            </button>

            <div class="table-responsive">
                <table class="table align-items-center mb-0" id="example">
                    <thead>
                        <tr>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder">No</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder">Name Part</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder">Code Part</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder">Rack Code</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder" style="width: 15%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($parts as $p)
                        <tr>
                            <td class="align-middle text-center">
                                <p class="text-xs font-weight-bold text-secondary">{{ $loop->iteration }}</p>
                            </td>
                            <td class="align-middle text-center text-primary">{{ $p->Name_Part }}</td>
                            <td class="align-middle text-center text-secondary">{{ $p->Code_Part }}</td>
                            <td class="align-middle text-center text-secondary">{{ $p->Code_Rack_Part }}</td>
                            <td class="align-middle text-center">
                                <div class="d-flex justify-content-center">
                                    <a href="#" class="btn btn-primary text-white text-xs mx-1"
                                        data-bs-toggle="modal" data-bs-target="#editPartModal"
                                        onclick="setEditPart({{ $p->Id_Part }}, {{ json_encode($p->Name_Part) }}, {{ json_encode($p->Code_Part) }}, {{ json_encode($p->Code_Rack_Part) }})">
                                        edit
                                    </a>
                                    <a href="#" class="btn btn-danger text-white text-xs mx-1"
                                        data-bs-toggle="modal" data-bs-target="#deletePartModal"
                                        onclick="setDeletePart({{ $p->Id_Part }}, {{ json_encode($p->Name_Part) }})">
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
<div class="modal fade" id="addPartModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('part.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">Add Part</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label>Name Part</label>
                        <input type="text" name="Name_Part" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label>Code Part</label>
                        <input type="text" name="Code_Part" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label>Rack Code</label>
                        <input type="text" name="Code_Rack_Part" class="form-control" required>
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
<div class="modal fade" id="editPartModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="editPartForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">Edit Part</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit-id" name="Id_Part">
                    <div class="form-group mb-3">
                        <label>Name Part</label>
                        <input type="text" id="edit-name" name="Name_Part" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label>Code Part</label>
                        <input type="text" id="edit-code" name="Code_Part" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label>Rack Code</label>
                        <input type="text" id="edit-rack" name="Code_Rack_Part" class="form-control" required>
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
<div class="modal fade" id="deletePartModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="deletePartForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white">Delete Part</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Yakin hapus part: <b id="delete-name"></b> ?</p>
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
    function setEditPart(id, name, code, rack) {
        document.getElementById('edit-id').value = id;
        document.getElementById('edit-name').value = name;
        document.getElementById('edit-code').value = code;
        document.getElementById('edit-rack').value = rack;
        document.getElementById('editPartForm').action = `/iseki_parcom/public/part/${id}`;
    }

    function setDeletePart(id, name) {
        document.getElementById('delete-name').textContent = name;
        document.getElementById('deletePartForm').action = `/iseki_parcom/public/part/${id}`;
    }
</script>
@endsection