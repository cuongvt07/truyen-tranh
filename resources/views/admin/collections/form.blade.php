@extends('layout.admin')
@section('template_title', $mode === 'create' ? 'Tạo bộ sưu tập' : 'Sửa bộ sưu tập')
@php
    $action = $mode === 'create' ? route('admin.collections.store') : route('admin.collections.update', $item->id);
    $selected = $item->exists ? $item->articles->pluck('id')->all() : [];
@endphp

@section('content')
<div class="content"><div class="container-fluid">
    <form method="post" action="{{ $action }}"><div class="row">
        <div class="col-md-8"><div class="card card-primary card-outline">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Thông tin</h3></div>
            <div class="card-body">
                <div class="form-group">
                    <label>Tên bộ sưu tập <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $item->name) }}" required autofocus>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group"><label>Mô tả</label><textarea name="description" class="form-control" rows="3">{{ old('description', $item->description) }}</textarea></div>
                <div class="form-group">
                    <label>Chọn truyện</label>
                    <input type="text" id="book-filter" class="form-control mb-2" placeholder="Lọc theo tên...">
                    <div id="book-list" style="max-height:320px;overflow-y:auto;border:1px solid #e2e8f0;border-radius:6px;padding:10px">
                        @foreach($articles as $a)
                            <div class="form-check" data-name="{{ \Illuminate\Support\Str::lower($a->title) }}">
                                <input type="checkbox" class="form-check-input" name="books[]" value="{{ $a->id }}" id="book{{ $a->id }}" {{ in_array($a->id, old('books', $selected)) ? 'checked' : '' }}>
                                <label class="form-check-label" for="book{{ $a->id }}">{{ $a->title }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div></div>
        <div class="col-md-4">
            <div class="card card-primary card-outline">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-cog mr-2"></i>Tuỳ chọn</h3></div>
                <div class="card-body">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="is_private" name="is_private" value="1" {{ old('is_private', $item->is_private) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_private">Riêng tư (chỉ mình thấy)</label>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ route('admin.collections.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Huỷ</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> {{ $mode === 'create' ? 'Tạo' : 'Lưu' }}</button>
                </div>
            </div>
        </div>
    </div></form>
</div></div>
<script>
document.getElementById('book-filter')?.addEventListener('input', function(){
    var q=this.value.toLowerCase();
    document.querySelectorAll('#book-list .form-check').forEach(function(l){
        l.style.display = l.getAttribute('data-name').includes(q) ? 'block' : 'none';
    });
});
</script>
@endsection
