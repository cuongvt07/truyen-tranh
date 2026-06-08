@extends('layout.admin')
@section('template_title', $mode === 'create' ? 'Tạo nhóm dịch' : 'Sửa nhóm dịch')
@php $action = $mode === 'create' ? route('admin.teams.store') : route('admin.teams.update', $item->id); @endphp

@section('content')
<div class="content"><div class="container-fluid">
    <form method="post" action="{{ $action }}" enctype="multipart/form-data"><div class="row">
        <div class="col-md-8"><div class="card card-primary card-outline">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Thông tin nhóm</h3></div>
            <div class="card-body">
                <div class="form-group">
                    <label>Tên nhóm <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $item->name) }}" required autofocus>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group"><label>Mô tả</label><textarea name="description" class="form-control" rows="4">{{ old('description', $item->description) }}</textarea></div>
                <div class="form-group"><label>Website</label><input type="text" name="site" class="form-control" value="{{ old('site', $item->site) }}" placeholder="https://..."></div>
                <div class="row">
                    <div class="form-group col-md-6"><label>Nội dung quyên góp</label><input type="text" name="donation_text" class="form-control" value="{{ old('donation_text', $item->donation_text) }}"></div>
                    <div class="form-group col-md-6"><label>Link quyên góp</label><input type="text" name="donation_url" class="form-control" value="{{ old('donation_url', $item->donation_url) }}" placeholder="https://..."></div>
                </div>
            </div>
        </div></div>
        <div class="col-md-4">
            <div class="card card-secondary card-outline">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-image mr-2"></i>Ảnh nhóm</h3></div>
                <div class="card-body text-center">
                    <img id="cover-preview" src="{{ $item->photo ?: asset('static/core/images/no_cover.webp') }}" style="width:120px;height:120px;object-fit:cover;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,.2)">
                    <div class="cover-upload-zone mt-3"><i class="fas fa-cloud-upload-alt fa-2x text-muted"></i><p class="mt-2 mb-1 small">Chọn ảnh</p><input type="file" name="photo" id="cover-input" accept="image/*"></div>
                    <input type="text" name="photo_url" class="form-control mt-2" placeholder="Hoặc URL ảnh...">
                </div>
            </div>
            <div class="card card-primary card-outline"><div class="card-body d-flex justify-content-between">
                <a href="{{ route('admin.teams.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Huỷ</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> {{ $mode === 'create' ? 'Tạo' : 'Lưu' }}</button>
            </div></div>
        </div>
    </div></form>
</div></div>
@endsection
