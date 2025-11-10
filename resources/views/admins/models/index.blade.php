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
                Model Ai
            </div>
            
            @if ($errors->any())
                <div class="row">
                    @foreach ($errors->all() as $error)
                        <div class="col-12 col-lg-6">
                            <div class="alert alert-danger text-white text-xs alert-dismissible fade show" role="alert">
                                {{ $error }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Tombol Add -->
            <button class="btn btn-primary text-white mx-3" data-bs-toggle="modal" data-bs-target="#addModelModal">
                <span style="padding-left: 50px; padding-right: 50px;"><b>+</b> Add</span>
            </button>

            <div class="table-responsive p-0">
                <table id="example" class="table align-items-center mb-0">
                    <thead>
                        <tr>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">No</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Name Model</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7">Path Model</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder opacity-7" style="width: 15%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ( $model as $m )
                        <tr>
                            <td class="align-middle text-center">
                                <p class="text-xs font-weight-bold text-secondary">{{ $loop->iteration }}</p>
                            </td>
                            <td class="align-middle text-center" style="text-align: left;">
                                <p class="text-xs text-primary mb-0">{{ $m->Name_Model }}</p>
                            </td>
                            <td class="align-middle text-center">
                                <p class="text-xs text-secondary mb-0">{{ $m->Path_Model }}</p>
                            </td>
                            <td class="align-middle text-center">
                                <div class="d-flex justify-content-center">
                                    <a href="#" class="btn btn-primary text-white text-xs mx-1" data-bs-toggle="modal" data-bs-target="#editModelModal"
                                        onclick="setEditModel({{ $m->Id_Model }}, '{{ $m->Name_Model }}', '{{ $m->Path_Model }}')">
                                        edit
                                    </a>
                                    <a href="#" class="btn btn-danger text-white text-xs mx-1" data-bs-toggle="modal" data-bs-target="#deleteModelModal"
                                        onclick="setDeleteModel({{ $m->Id_Model }}, '{{ $m->Name_Model }}', '{{ $m->Path_Model }}')">
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

<!-- Modal Add Model AI -->
<div class="modal fade" id="addModelModal" tabindex="-1" aria-labelledby="addModelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('model.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">Add Model AI</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group pb-2">
                        <label class="form-label">Name Model</label>
                        <input type="text" class="form-control" name="Name_Model" required>
                    </div>
                    <div class="form-group pb-2">
                        <label class="form-label">Path (tanpa spasi, akan di-slug)</label>
                        <input type="text" class="form-control" name="Path_Model" required placeholder="contoh: ring_synchronizer">
                    </div>
                    <div class="form-group pb-2">
                        <label class="form-label">Upload metadata.json</label>
                        <input type="file" class="form-control" name="metadata" accept=".json" required>
                    </div>
                    <div class="form-group pb-2">
                        <label class="form-label">Upload model.json</label>
                        <input type="file" class="form-control" name="model_file" accept=".json" required>
                    </div>
                    <div class="form-group pb-2">
                        <label class="form-label">Upload weights.bin</label>
                        <input type="file" class="form-control" name="weights" accept=".bin" required>
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

<!-- Modal Edit Model AI -->
<div class="modal fade" id="editModelModal" tabindex="-1" aria-labelledby="editModelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="editModelForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">Edit Model AI</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="Id_Model" id="edit-id">

                    <div class="form-group pb-2">
                        <label class="form-label">Name Model</label>
                        <input type="text" class="form-control" name="Name_Model" id="edit-name" required>
                    </div>
                    <div class="form-group pb-2">
                        <label class="form-label">Path (akan di-slug)</label>
                        <input type="text" class="form-control" name="Path_Model" id="edit-path" required>
                    </div>
                    <div class="form-group pb-2">
                        <label class="form-label">Ganti metadata.json (opsional)</label>
                        <input type="file" class="form-control" name="metadata" accept=".json">
                    </div>
                    <div class="form-group pb-2">
                        <label class="form-label">Ganti model.json (opsional)</label>
                        <input type="file" class="form-control" name="model_file" accept=".json">
                    </div>
                    <div class="form-group pb-2">
                        <label class="form-label">Ganti weights.bin (opsional)</label>
                        <input type="file" class="form-control" name="weights" accept=".bin">
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

<!-- Modal Delete Model AI -->
<div class="modal fade" id="deleteModelModal" tabindex="-1" aria-labelledby="deleteModelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="deleteModelForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white">Delete Model AI</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Yakin hapus model ini beserta folder-nya?</p>
                    <p><b>Nama:</b> <span id="delete-name"></span></p>
                    <p><b>Path:</b> <span id="delete-path"></span></p>
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
<link href="{{asset('assets/datatables/datatables.min.css')}}" rel="stylesheet">
@endsection

@section('script')
<script src="{{asset('assets/js/jquery.min.js')}}"></script>
<script src="{{asset('assets/datatables/datatables.min.js')}}"></script>
<script>
new DataTable('#example');
</script>
<script>
function setEditModel(id, name, path) {
    document.getElementById('edit-id').value = id;
    document.getElementById('edit-name').value = name;
    document.getElementById('edit-path').value = path;
    document.getElementById('editModelForm').action = `/iseki_parcom/public/model/update/${id}`;
}

function setDeleteModel(id, name, path) {
    document.getElementById('delete-name').textContent = name;
    document.getElementById('delete-path').textContent = path;
    document.getElementById('deleteModelForm').action = `/iseki_parcom/public/model/delete/${id}`;
}
</script>
@endsection