@extends('layout.admin')

@section('template_title', 'Thêm gói Credit')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="fa fa-plus"></i> Thêm gói Credit mới</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.credit-packages.store') }}" method="POST">
                    @csrf
                    @include('admin.credit-packages._form')
                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Lưu gói</button>
                        <a href="{{ route('admin.credit-packages.index') }}" class="btn btn-secondary">Hủy</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
