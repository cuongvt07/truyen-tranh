@extends('layout.admin')
@section('template_title', 'Quốc gia')

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
@endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0"><i class="fa fa-flag mr-1"></i> Quốc gia <span class="badge badge-info ml-1">{{ $countries->count() }}</span></h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-bordered table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th width="50" class="text-center">ID</th>
                    <th>Tên (VI)</th>
                    <th>Tên (EN)</th>
                    <th width="90" class="text-center">Thứ tự</th>
                    <th width="120" class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @foreach($countries as $c)
                {{-- VIEW ROW --}}
                <tr id="row-{{ $c->id }}">
                    <td class="text-center text-muted">{{ $c->id }}</td>
                    <td><strong>{{ $c->name }}</strong></td>
                    <td class="text-muted">{{ $c->name_en }}</td>
                    <td class="text-center"><span class="badge badge-secondary">{{ $c->sort_order }}</span></td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-info" onclick="toggleEdit({{ $c->id }})">
                            <i class="fa fa-edit"></i>
                        </button>
                        <form action="{{ route('admin.countries.destroy', $c->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Xoá quốc gia này?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                {{-- EDIT ROW --}}
                <tr id="edit-{{ $c->id }}" style="display:none; background:var(--light,#f8f9fa)">
                    <td class="text-center text-muted">{{ $c->id }}</td>
                    <td colspan="3">
                        <form action="{{ route('admin.countries.update', $c->id) }}" method="POST" class="d-flex gap-2 align-items-center flex-wrap">
                            @csrf @method('PUT')
                            <input type="text" name="name" value="{{ $c->name }}" class="form-control form-control-sm" style="max-width:160px" placeholder="Tên VI" required>
                            <input type="text" name="name_en" value="{{ $c->name_en }}" class="form-control form-control-sm" style="max-width:140px" placeholder="Tên EN">
                            <input type="number" name="sort_order" value="{{ $c->sort_order }}" class="form-control form-control-sm" style="max-width:80px" placeholder="Thứ tự">
                            <button class="btn btn-sm btn-success"><i class="fa fa-check"></i> Lưu</button>
                            <button type="button" class="btn btn-sm btn-secondary" onclick="toggleEdit({{ $c->id }})">Huỷ</button>
                        </form>
                    </td>
                    <td></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Thêm mới --}}
<div class="card mt-3" style="max-width:480px">
    <div class="card-header"><strong><i class="fa fa-plus mr-1"></i> Thêm quốc gia</strong></div>
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
        @endif
        <form action="{{ route('admin.countries.store') }}" method="POST" class="d-flex flex-wrap gap-2 align-items-end">
            @csrf
            <div>
                <label class="small mb-1">Tên (VI) <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control form-control-sm" value="{{ old('name') }}" style="width:150px" required>
            </div>
            <div>
                <label class="small mb-1">Tên (EN)</label>
                <input type="text" name="name_en" class="form-control form-control-sm" value="{{ old('name_en') }}" style="width:130px" placeholder="English">
            </div>
            <div>
                <label class="small mb-1">Thứ tự</label>
                <input type="number" name="sort_order" class="form-control form-control-sm" value="{{ old('sort_order', 0) }}" style="width:75px" min="0">
            </div>
            <button class="btn btn-sm btn-success"><i class="fa fa-plus"></i> Thêm</button>
        </form>
    </div>
</div>

<script>
function toggleEdit(id) {
    var view = document.getElementById('row-' + id);
    var edit = document.getElementById('edit-' + id);
    var show = edit.style.display === 'none';
    view.style.display = show ? 'none' : '';
    edit.style.display = show ? '' : 'none';
}
</script>
@endsection
