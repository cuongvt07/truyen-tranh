@extends('layout.admin')

@section('template_title', $mode === 'create' ? 'Thêm mới Nhóm dịch' : 'Chỉnh sửa Nhóm dịch: ' . $item->name)

@section('content')
@php 
    $action = $mode === 'create' ? route('admin.teams.store') : route('admin.teams.update', $item->id); 
@endphp

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-users"></i>
                    {{ $mode === 'create' ? 'Thêm mới Nhóm dịch' : 'Chỉnh sửa Nhóm dịch' }}
                </h3>
            </div>
            <form method="POST" action="{{ $action }}" enctype="multipart/form-data">
                @csrf
                @if($mode === 'edit') @method('PATCH') @endif
                
                <div class="card-body">
                    {{-- Trưởng nhóm --}}
                    @if($mode === 'create')
                    <div class="form-group">
                        <label><i class="fas fa-user-shield text-muted"></i> Trưởng nhóm (username)</label>
                        <input type="text" name="leader_username"
                               class="form-control @error('leader_username') is-invalid @enderror"
                               value="{{ old('leader_username') }}"
                               placeholder="Nhập username của trưởng nhóm">
                        @error('leader_username')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        <small class="form-text text-muted">User này sẽ là trưởng nhóm và sở hữu nhóm. Bỏ trống nếu chưa xác định.</small>
                    </div>
                    @endif

                    {{-- Tên nhóm --}}
                    <div class="form-group">
                        <label>
                            <i class="fas fa-heading text-muted"></i>
                            Tên nhóm <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               name="name" 
                               class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $item->name) }}" 
                               required 
                               autofocus
                               placeholder="Nhập tên nhóm dịch">
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">Tên hiển thị của nhóm dịch</small>
                    </div>

                    {{-- Mô tả --}}
                    <div class="form-group">
                        <label>
                            <i class="fas fa-align-left text-muted"></i>
                            Mô tả
                        </label>
                        <textarea name="description" 
                                  class="form-control @error('description') is-invalid @enderror" 
                                  rows="4"
                                  placeholder="Giới thiệu ngắn gọn về nhóm dịch...">{{ old('description', $item->description) }}</textarea>
                        @error('description')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">Thông tin về nhóm dịch, thành viên, lĩnh vực...</small>
                    </div>

                    <hr>

                    {{-- Website --}}
                    <div class="form-group">
                        <label>
                            <i class="fas fa-globe text-muted"></i>
                            Website
                        </label>
                        <input type="url" 
                               name="site" 
                               class="form-control @error('site') is-invalid @enderror" 
                               value="{{ old('site', $item->site) }}" 
                               placeholder="https://example.com">
                        @error('site')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">Trang web chính thức của nhóm (nếu có)</small>
                    </div>

                    {{-- Donation --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>
                                    <i class="fas fa-hand-holding-usd text-muted"></i>
                                    Nội dung quyên góp
                                </label>
                                <input type="text" 
                                       name="donation_text" 
                                       class="form-control @error('donation_text') is-invalid @enderror" 
                                       value="{{ old('donation_text', $item->donation_text) }}"
                                       placeholder="Ủng hộ nhóm dịch">
                                @error('donation_text')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>
                                    <i class="fas fa-link text-muted"></i>
                                    Link quyên góp
                                </label>
                                <input type="url" 
                                       name="donation_url" 
                                       class="form-control @error('donation_url') is-invalid @enderror" 
                                       value="{{ old('donation_url', $item->donation_url) }}"
                                       placeholder="https://...">
                                @error('donation_url')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <small class="form-text text-muted mb-3">
                        <i class="fas fa-info-circle"></i>
                        Thông tin để độc giả có thể ủng hộ nhóm dịch
                    </small>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ $mode === 'create' ? 'Lưu nhóm dịch' : 'Cập nhật' }}
                    </button>
                    <a href="{{ route('admin.teams.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Sidebar for image --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-image"></i>
                    Ảnh đại diện
                </h3>
            </div>
            <form method="POST" action="{{ $action }}" enctype="multipart/form-data">
                @csrf
                @if($mode === 'edit') @method('PATCH') @endif
                
                <div class="card-body">
                    <x-admin.image-upload name="photo" :height="150"
                        :current="$item->photo ?: null"
                        urlName="photo_url" :urlValue="old('photo_url')"
                        hint="Link ảnh từ nguồn khác" />
                </div>
            </form>
        </div>

        @if($item->exists)
        <div class="card card-secondary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle"></i>
                    Thông tin
                </h3>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <strong>ID:</strong> 
                        <span class="badge badge-secondary">{{ $item->id }}</span>
                    </li>
                    <li class="mb-2">
                        <strong>Người tạo:</strong><br>
                        <small class="text-muted">
                            <i class="fas fa-user"></i> 
                            {{ optional($item->user)->username ?? 'N/A' }}
                        </small>
                    </li>
                    <li class="mb-2">
                        <strong>Tạo lúc:</strong><br>
                        <small class="text-muted">
                            <i class="fas fa-clock"></i> 
                            {{ $item->created_at?->format('d/m/Y H:i') }}
                        </small>
                    </li>
                </ul>
            </div>
        </div>
        @endif
    </div>
</div>

@endsection
