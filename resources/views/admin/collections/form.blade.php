@extends('layout.admin')

@section('template_title', $mode === 'create' ? 'Thêm mới Bộ sưu tập' : 'Chỉnh sửa Bộ sưu tập: ' . $item->name)

@section('content')
@php
    $action = $mode === 'create' ? route('admin.collections.store') : route('admin.collections.update', $item->id);
    $selected = $item->exists ? $item->articles->pluck('id')->all() : [];
@endphp

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-layer-group"></i>
                    {{ $mode === 'create' ? 'Thêm mới Bộ sưu tập' : 'Chỉnh sửa Bộ sưu tập' }}
                </h3>
            </div>
            <form method="POST" action="{{ $action }}">
                @csrf
                @if($mode === 'edit') @method('PATCH') @endif
                
                <div class="card-body">
                    {{-- Tên bộ sưu tập --}}
                    <div class="form-group">
                        <label>
                            <i class="fas fa-heading text-muted"></i>
                            Tên bộ sưu tập <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               name="name" 
                               class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $item->name) }}" 
                               required 
                               autofocus
                               placeholder="Nhập tên bộ sưu tập">
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">Tên hiển thị của bộ sưu tập</small>
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
                                  placeholder="Mô tả ngắn gọn về bộ sưu tập này...">{{ old('description', $item->description) }}</textarea>
                        @error('description')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">Giới thiệu về chủ đề hoặc nội dung của bộ sưu tập</small>
                    </div>

                    <hr>

                    {{-- Chọn truyện --}}
                    <div class="form-group">
                        <label>
                            <i class="fas fa-book text-muted"></i>
                            Chọn truyện
                        </label>
                        <input type="text" 
                               id="book-filter" 
                               class="form-control mb-2" 
                               placeholder="🔍 Tìm kiếm truyện theo tên...">
                        
                        <div id="book-list" 
                             style="max-height: 400px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 6px; padding: 12px; background: #f8f9fa;">
                            @forelse($articles as $a)
                                <div class="form-check mb-2" data-name="{{ \Illuminate\Support\Str::lower($a->title) }}">
                                    <input type="checkbox" 
                                           class="form-check-input" 
                                           name="books[]" 
                                           value="{{ $a->id }}" 
                                           id="book{{ $a->id }}" 
                                           {{ in_array($a->id, old('books', $selected)) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="book{{ $a->id }}">
                                        {{ $a->title }}
                                    </label>
                                </div>
                            @empty
                                <div class="text-center text-muted py-3">
                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                    <p class="mb-0">Chưa có truyện nào</p>
                                </div>
                            @endforelse
                        </div>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i>
                            Chọn các truyện để thêm vào bộ sưu tập này
                        </small>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ $mode === 'create' ? 'Lưu bộ sưu tập' : 'Cập nhật' }}
                    </button>
                    <a href="{{ route('admin.collections.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="col-md-4">
        {{-- Options Card --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-cog"></i>
                    Tùy chọn
                </h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ $action }}">
                    @csrf
                    @if($mode === 'edit') @method('PATCH') @endif
                    
                    <div class="custom-control custom-switch">
                        <input type="checkbox" 
                               class="custom-control-input" 
                               id="is_private" 
                               name="is_private" 
                               value="1" 
                               {{ old('is_private', $item->is_private) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_private">
                            <i class="fas fa-lock"></i> Riêng tư
                        </label>
                    </div>
                    <small class="form-text text-muted">Chỉ bạn mới thấy bộ sưu tập này</small>
                </form>
            </div>
        </div>

        {{-- Info Card --}}
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
                        <strong>Số truyện:</strong> 
                        <span class="badge badge-info">{{ $item->articles()->count() }}</span>
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

@push('scripts')
<script>
// Live search filter for books
document.getElementById('book-filter')?.addEventListener('input', function() {
    const query = this.value.toLowerCase();
    document.querySelectorAll('#book-list .form-check').forEach(function(item) {
        const name = item.getAttribute('data-name');
        item.style.display = name.includes(query) ? 'block' : 'none';
    });
});
</script>
@endpush
@endsection
