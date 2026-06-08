@extends('layout.admin')

@section('template_title', 'Sửa gói Credit')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="fa fa-edit"></i> Sửa gói: {{ $creditPackage->name }}</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.credit-packages.update', $creditPackage->id) }}" method="POST">
                    @csrf @method('PUT')
                    @include('admin.credit-packages._form', ['pkg' => $creditPackage])
                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Cập nhật</button>
                        <a href="{{ route('admin.credit-packages.index') }}" class="btn btn-secondary">Hủy</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
