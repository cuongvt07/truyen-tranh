@extends('layout.admin')

@section('template_title', $block->exists ? 'Sửa khối trang chủ' : 'Thêm khối trang chủ')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ $block->exists ? 'Sửa khối: '.$block->title : 'Thêm khối trang chủ' }}</h3>
    </div>

    <form method="POST"
          action="{{ $block->exists ? route('admin.home-blocks.update', $block) : route('admin.home-blocks.store') }}">
        @csrf
        @if($block->exists) @method('PUT') @endif

        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <div class="form-group">
                <label>Tiêu đề khối <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control"
                       value="{{ old('title', $block->title) }}" required>
                <small class="text-muted">Chữ hiển thị ngay trên khối, ví dụ "Top Werewolf".</small>
            </div>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label>Nguồn truyện <span class="text-danger">*</span></label>
                    <select name="source" id="source-select" class="form-control">
                        @foreach(\App\Models\HomeBlock::SOURCES as $val => $label)
                            <option value="{{ $val }}" @selected(old('source', $block->source) === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6 form-group" id="genre-wrap">
                    <label>Thể loại</label>
                    <select name="genre_id" class="form-control">
                        <option value="">— chọn thể loại —</option>
                        @foreach($genres as $genre)
                            <option value="{{ $genre->id }}" @selected((int) old('genre_id', $block->genre_id) === $genre->id)>{{ $genre->name }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Chỉ dùng khi nguồn là "Theo thể loại".</small>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 form-group">
                    <label>Số truyện hiển thị <span class="text-danger">*</span></label>
                    <input type="number" name="limit" class="form-control" min="1" max="24"
                           value="{{ old('limit', $block->limit) }}" required>
                    <small class="text-muted">Tối đa 24. Càng lớn trang chủ càng nặng.</small>
                </div>

                <div class="col-md-4 form-group">
                    <label>Kiểu hiển thị</label>
                    <select name="variant" class="form-control">
                        @foreach(\App\Models\HomeBlock::VARIANTS as $val => $label)
                            <option value="{{ $val }}" @selected(old('variant', $block->variant) === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 form-group">
                    <label>Thứ tự</label>
                    <input type="number" name="order" class="form-control" min="0"
                           value="{{ old('order', $block->order) }}" required>
                    <small class="text-muted">Nhỏ hiện trước.</small>
                </div>
            </div>

            <div class="form-group">
                <label>Link "See All" (tuỳ chọn)</label>
                <input type="text" name="url" class="form-control"
                       value="{{ old('url', $block->url) }}" placeholder="Để trống sẽ tự suy ra từ nguồn/thể loại">
            </div>

            <div class="form-check">
                <input type="hidden" name="show_see_all" value="0">
                <input type="checkbox" name="show_see_all" value="1" class="form-check-input" id="see-all"
                       @checked(old('show_see_all', $block->show_see_all))>
                <label class="form-check-label" for="see-all">Hiện nút "See All"</label>
            </div>

            <div class="form-check">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is-active"
                       @checked(old('is_active', $block->is_active))>
                <label class="form-check-label" for="is-active">Bật khối này</label>
            </div>
        </div>

        <div class="card-footer">
            <button class="btn btn-primary">Lưu</button>
            <a href="{{ route('admin.home-blocks.index') }}" class="btn btn-secondary">Huỷ</a>
        </div>
    </form>
</div>

<script>
// Ô thể loại chỉ có nghĩa khi nguồn = genre.
(function () {
    var sel = document.getElementById('source-select');
    var wrap = document.getElementById('genre-wrap');
    function sync() { wrap.style.display = sel.value === 'genre' ? '' : 'none'; }
    sel.addEventListener('change', sync);
    sync();
})();
</script>
@endsection
