@extends('layouts.admin')
@section('content')
<div class="container-fluid p-0">
    <section class="resume-section">
        <div class="resume-section-content">
            <h3 class="mb-3">
                List
                <span class="text-primary">Comparison</span>
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

            <button class="btn btn-primary text-white mx-3 mb-3" data-bs-toggle="modal" data-bs-target="#addListComparisonModal">
                <span style="padding-left: 30px; padding-right: 30px;"><b>+</b> Add List Comparison</span>
            </button>

            <div class="table-responsive">
                <table class="table align-items-center mb-0" id="example">
                    <thead>
                        <tr>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder">No</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder">Comparison</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder">Tractor</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder">Part</th>
                            <th class="text-center text-uppercase text-primary text-xxs font-weight-bolder" style="width: 15%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($listComparisons as $lc)
                        <tr>
                            <td class="align-middle text-center">
                                <p class="text-xs font-weight-bold text-secondary">{{ $loop->iteration }}</p>
                            </td>
                            <td class="align-middle text-center text-primary">{{ $lc->comparison?->Name_Comparison ?? '—' }}</td>
                            <td class="align-middle text-center text-secondary">{{ $lc->tractor?->Type_Tractor ?? '—' }}</td>
                            <td class="align-middle text-center text-secondary">{{ $lc->part?->Name_Part ?? '—' }}</td>
                            <td class="align-middle text-center">
                                <div class="d-flex justify-content-center">
                                    <a href="#" class="btn btn-primary text-white text-xs mx-1"
                                        data-bs-toggle="modal" data-bs-target="#editListComparisonModal"
                                        onclick="setEditList({{ $lc->Id_List_Comparison }}, {{ $lc->Id_Comparison }}, {{ $lc->Id_Tractor }}, {{ $lc->Id_Part }})">
                                        edit
                                    </a>
                                    <a href="#" class="btn btn-danger text-white text-xs mx-1"
                                        data-bs-toggle="modal" data-bs-target="#deleteListComparisonModal"
                                        onclick="setDeleteList({{ $lc->Id_List_Comparison }}, {{ json_encode($lc->comparison?->Name_Comparison ?: 'Item') }})">
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
<div class="modal fade" id="addListComparisonModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('list.comparison.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">Add List Comparison</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label>Comparison</label>
                        <select name="Id_Comparison" class="form-select" required>
                            <option value="">-- Pilih Comparison --</option>
                            @foreach(App\Models\Comparison::with('model')->get() as $c)
                                <option value="{{ $c->Id_Comparison }}">{{ $c->Name_Comparison }} ({{ $c->model?->Name_Model ?? '—' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label>Tractor</label>
                        <select name="Id_Tractor" class="form-select" required>
                            <option value="">-- Pilih Tractor --</option>
                            @foreach(App\Models\Tractor::all() as $t)
                                <option value="{{ $t->Id_Tractor }}">{{ $t->Type_Tractor }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label>Part</label>
                        <select name="Id_Part" class="form-select" required>
                            <option value="">-- Pilih Part --</option>
                            @foreach(App\Models\Part::all() as $p)
                                <option value="{{ $p->Id_Part }}">{{ $p->Name_Part }} ({{ $p->Code_Part }})</option>
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
<div class="modal fade" id="editListComparisonModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="editListForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">Edit List Comparison</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit-id" name="Id_List_Comparison">
                    <div class="form-group mb-3">
                        <label>Comparison</label>
                        <select id="edit-comparison" name="Id_Comparison" class="form-select" required>
                            <option value="">-- Pilih Comparison --</option>
                            @foreach(App\Models\Comparison::with('model')->get() as $c)
                                <option value="{{ $c->Id_Comparison }}">{{ $c->Name_Comparison }} ({{ $c->model?->Name_Model ?? '—' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label>Tractor</label>
                        <select id="edit-tractor" name="Id_Tractor" class="form-select" required>
                            <option value="">-- Pilih Tractor --</option>
                            @foreach(App\Models\Tractor::all() as $t)
                                <option value="{{ $t->Id_Tractor }}">{{ $t->Type_Tractor }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label>Part</label>
                        <select id="edit-part" name="Id_Part" class="form-select" required>
                            <option value="">-- Pilih Part --</option>
                            @foreach(App\Models\Part::all() as $p)
                                <option value="{{ $p->Id_Part }}">{{ $p->Name_Part }} ({{ $p->Code_Part }})</option>
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
<div class="modal fade" id="deleteListComparisonModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="deleteListForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white">Delete List Comparison</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Yakin hapus list comparison: <b id="delete-name"></b> ?</p>
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
    function setEditList(id, compId, tractorId, partId) {
        document.getElementById('edit-id').value = id;
        document.getElementById('edit-comparison').value = compId;
        document.getElementById('edit-tractor').value = tractorId;
        document.getElementById('edit-part').value = partId;
        document.getElementById('editListForm').action = `/iseki_parcom/public/list-comparison/${id}`;
    }

    function setDeleteList(id, name) {
        document.getElementById('delete-name').textContent = name;
        document.getElementById('deleteListForm').action = `/iseki_parcom/public/list-comparison/${id}`;
    }
</script>
@endsection