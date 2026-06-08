@extends('layout.admin')
@section('template_title', $mode === 'create' ? 'Thêm nhân vật' : 'Sửa nhân vật')
@php $action = $mode === 'create' ? route('admin.characters.store') : route('admin.characters.update', $item->id); @endphp

@section('content')
<div class="content"><div class="container-fluid">
    <form method="post" action="{{ $action }}" enctype="multipart/form-data">
        @csrf @if($mode === 'edit') @method('patch') @endif
        <div class="row">
            <div class="col-md-8">
                <div class="card card-primary card-outline">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Thông tin nhân vật</h3></div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Tên nhân vật <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $item->name) }}" required autofocus>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label>Loại</label>
                            <select name="type" class="form-control">
                                <option value="0" @selected(old('type',$item->type)==0)>Nhân vật chính</option>
                                <option value="1" @selected(old('type',$item->type)==1)>Nhân vật phụ</option>
                                <option value="2" @selected(old('type',$item->type)==2)>Khác</option>
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <label>Mô tả</label>
                            <textarea name="description" class="form-control" rows="6">{{ old('description', $item->description) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-secondary card-outline">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-image mr-2"></i>Ảnh nhân vật</h3></div>
                    <div class="card-body text-center">
                        <img id="cover-preview" src="{{ $item->photo ?: asset('static/account/images/no-ava.jpg') }}" style="width:120px;height:120px;object-fit:cover;border-radius:50%;box-shadow:0 4px 12px rgba(0,0,0,.2)">
                        <div class="cover-upload-zone mt-3">
                            <i class="fas fa-cloud-upload-alt fa-2x text-muted"></i>
                            <p class="mt-2 mb-1 small">Chọn ảnh</p>
                            <input type="file" name="photo" id="cover-input" accept="image/*">
                        </div>
                        <input type="text" name="photo_url" class="form-control mt-2" placeholder="Hoặc URL ảnh...">
                    </div>
                </div>
                <div class="card card-primary card-outline">
                    <div class="card-body d-flex justify-content-between">
                        <a href="{{ route('admin.characters.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Huỷ</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> {{ $mode === 'create' ? 'Thêm' : 'Lưu' }}</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div></div>
@endsection
