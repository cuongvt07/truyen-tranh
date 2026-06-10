@extends('layout.admin')

@section('template_title', 'Thêm mới Tác giả')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Thêm mới Tác giả</h3>
    </div>
    <form action="{{ route('admin.authors.store') }}" method="POST">
        @csrf
        <div class="card-body">
            @include('admin.authors.form')
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Lưu tác giả
            </button>
            <a href="{{ route('admin.authors.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </form>
</div>
@endsection
