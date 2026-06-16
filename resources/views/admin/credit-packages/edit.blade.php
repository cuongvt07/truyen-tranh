@extends('layout.admin')

@section('template_title', 'Chỉnh sửa Gói Credit: ' . $creditPackage->name)

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-edit"></i>
                    Chỉnh sửa: {{ $creditPackage->name }}
                </h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.credit-packages.update', $creditPackage->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf 
                    @method('PUT')
                    @include('admin.credit-packages._form', ['pkg' => $creditPackage])
                    
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Cập nhật
                        </button>
                        <a href="{{ route('admin.credit-packages.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
