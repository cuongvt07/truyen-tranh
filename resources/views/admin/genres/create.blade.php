@extends('layout.admin')

@section('template_title', 'Thêm mới Thể loại')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Thêm mới Thể loại</h3>
    </div>
    <form action="{{ route('admin.genres.store') }}" method="POST">
        @csrf
        <div class="card-body">
            @include('admin.genres.form')
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Lưu thể loại
            </button>
            <a href="{{ route('admin.genres.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </form>
</div>
@endsection
