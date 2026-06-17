@extends('layout.admin')
@section('template_title', 'Quốc gia')

@section('content')
<div class="row">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong><i class="fa fa-flag"></i> Danh sách quốc gia</strong>
                <span class="badge badge-info">{{ $countries->count() }}</span>
            </div>
            <div class="card-body p-0">
                @if(session('success'))
                    <div class="alert alert-success m-3">{{ session('success') }}</div>
                @endif
                <table class="table table-sm table-bordered mb-0">
                    <thead class="thead-light">
                        <tr><th width="50">#</th><th>Tên (VI)</th><th>Tên (EN)</th><th width="70">Thứ tự</th><th width="100"></th></tr>
                    </thead>
                    <tbody>
                        @foreach($countries as $c)
                        <tr>
                            <td class="text-muted">{{ $c->id }}</td>
                            <td>
                                <form action="{{ route('admin.countries.update', $c->id) }}" method="POST" class="d-flex gap-1 align-items-center">
                                    @csrf @method('PUT')
                                    <input type="text" name="name" value="{{ $c->name }}" class="form-control form-control-sm" style="max-width:130px">
                                    <input type="text" name="name_en" value="{{ $c->name_en }}" class="form-control form-control-sm" style="max-width:110px" placeholder="EN">
                                    <input type="number" name="sort_order" value="{{ $c->sort_order }}" class="form-control form-control-sm" style="max-width:60px">
                                    <button class="btn btn-sm btn-primary">Lưu</button>
                                </form>
                            </td>
                            <td></td>
                            <td></td>
                            <td>
                                <form action="{{ route('admin.countries.destroy', $c->id) }}" method="POST" onsubmit="return confirm('Xoá?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card">
            <div class="card-header"><strong><i class="fa fa-plus"></i> Thêm quốc gia</strong></div>
            <div class="card-body">
                <form action="{{ route('admin.countries.store') }}" method="POST">
                    @csrf
                    @if($errors->any())
                        <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
                    @endif
                    <div class="form-group mb-2">
                        <label>Tên (VI) <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>
                    <div class="form-group mb-2">
                        <label>Tên (EN)</label>
                        <input type="text" name="name_en" class="form-control" value="{{ old('name_en') }}" placeholder="English name">
                    </div>
                    <div class="form-group mb-3">
                        <label>Thứ tự hiển thị</label>
                        <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}" min="0">
                    </div>
                    <button class="btn btn-success"><i class="fa fa-plus"></i> Thêm</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
