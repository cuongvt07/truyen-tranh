@extends('layout.admin')

@section('template_title', 'Chỉnh sửa Thể loại: ' . $genre->name)

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Chỉnh sửa Thể loại</h3>
    </div>
    <form action="{{ route('admin.genres.update', $genre->id) }}" method="POST">
        @csrf
        @method('PATCH')
        <div class="card-body">
            @include('admin.genres.form')
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Cập nhật thể loại
            </button>
            <a href="{{ route('admin.genres.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </form>
</div>
@endsection
