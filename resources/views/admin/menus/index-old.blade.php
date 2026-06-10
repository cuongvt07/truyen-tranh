@extends('layout.admin')
@section('template_title', 'Menu')

@section('content')
<div class="content"><div class="container-fluid">
    <p class="text-muted mb-2"><small>Kéo <i class="fas fa-grip-vertical"></i> để sắp xếp, kéo thụt vào để tạo menu con (dropdown). Nhớ bấm <b>Lưu sắp xếp</b>.</small></p>
    @include('admin.partials.flash')

    {{-- Tabs chọn vị trí --}}
    <ul class="nav nav-pills mb-3">
        @foreach($menus as $m)
            <li class="nav-item">
                <a class="nav-link {{ $menu && $menu->id === $m->id ? 'active' : '' }}"
                   href="{{ route('admin.menus.index', ['menu' => $m->location]) }}">
                    {{ $m->name }} <span class="badge badge-light border ml-1">{{ $m->items()->count() }}</span>
                </a>
            </li>
        @endforeach
    </ul>

    @if($menu && $menu->location === 'browse')
        <div class="alert alert-info py-2"><i class="fas fa-info-circle"></i>
            Để menu này <b>trống</b> = dropdown "Browse" tự liệt kê tất cả thể loại. Thêm mục để tự điều khiển.
        </div>
    @endif

    <div class="row">
        {{-- Thêm mục (đơn giản: icon / title / url) --}}
        <div class="col-lg-4">
            <div class="card card-primary card-outline">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-plus"></i> Thêm mục</h3></div>
                <form method="POST" action="{{ route('admin.menus.items.store', $menu->id) }}">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label class="mb-1">Tiêu đề <span class="text-danger">*</span></label>
                            <input type="text" name="label" class="form-control form-control-sm" value="{{ old('label') }}" placeholder="Trang chủ" required>
                        </div>
                        <div class="form-group">
                            <label class="mb-1">URL</label>
                            <input type="text" name="url" class="form-control form-control-sm" value="{{ old('url', '/') }}" placeholder="/catalog hoặc #browse">
                        </div>
                        <div class="form-group mb-0">
                            <label class="mb-1">Icon <small class="text-muted">(FontAwesome)</small></label>
                            <input type="text" name="icon" class="form-control form-control-sm" value="{{ old('icon') }}" placeholder="fa fa-home">
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Thêm</button>
                    </div>
                </form>
            </div>
            <div class="card card-secondary card-outline">
                <div class="card-body small text-muted py-2">
                    <b>URL đặc biệt:</b> <code>#browse</code> mở dropdown thể loại, <code>#search</code> mở ô tìm kiếm.
                </div>
            </div>
        </div>

        {{-- Builder kéo-thả --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Cấu trúc menu</h3>
                    <div>
                        <span id="save-status" class="text-muted small mr-2"></span>
                        <button type="button" id="save-order" class="btn btn-success btn-sm"
                                data-url="{{ route('admin.menus.reorder', $menu->id) }}">
                            <i class="fas fa-save"></i> Lưu sắp xếp
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if($tree->count())
                        <div class="dd" id="menu-nestable">
                            <ol class="dd-list">
                                @foreach($tree as $it)
                                    @include('admin.menus.nestable-item', ['it' => $it])
                                @endforeach
                            </ol>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">Chưa có mục nào. Thêm ở cột bên trái.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div></div>

{{-- Modal sửa mục --}}
<div class="modal fade" id="edit-item-modal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="edit-item-form">
            @csrf @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit"></i> Sửa mục menu</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="mb-1">Tiêu đề <span class="text-danger">*</span></label>
                        <input type="text" name="label" class="form-control form-control-sm" required>
                    </div>
                    <div class="form-group">
                        <label class="mb-1">URL</label>
                        <input type="text" name="url" class="form-control form-control-sm">
                    </div>
                    <div class="form-row">
                        <div class="form-group col-6">
                            <label class="mb-1">Icon</label>
                            <input type="text" name="icon" class="form-control form-control-sm" placeholder="fa fa-home">
                        </div>
                        <div class="form-group col-6">
                            <label class="mb-1">Mở ở</label>
                            <select name="target" class="form-control form-control-sm">
                                <option value="_self">Cùng tab</option>
                                <option value="_blank">Tab mới</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="mb-1">Khóa dịch <small class="text-muted">(tùy chọn, đa ngôn ngữ)</small></label>
                        <input type="text" name="label_key" class="form-control form-control-sm" placeholder="messages.nav.home">
                    </div>
                    <div class="form-group mb-0">
                        <label class="mb-0"><input type="checkbox" name="is_active" value="1"> Hiển thị</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Huỷ</button>
                    <button type="submit" class="btn btn-primary">Lưu</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Builder kéo-thả kiểu WordPress — tự viết toàn bộ (KHÔNG dùng CSS mặc định Nestable để tránh chọi) */
#menu-nestable { position: relative; }
#menu-nestable .dd-list { list-style: none; margin: 0; padding: 0; }
#menu-nestable .dd-list .dd-list { padding-left: 32px; }
#menu-nestable .dd-item { display: block; margin: 10px 0; }
#menu-nestable .dd-item > .mi-wrap {
    display: flex; align-items: stretch; background: #fff;
    border: 1px solid #e2e6ee; border-radius: 8px; overflow: hidden;
    transition: box-shadow .15s ease, border-color .15s ease;
}
#menu-nestable .dd-item > .mi-wrap:hover { border-color: #c3ccdb; box-shadow: 0 2px 10px rgba(20,30,60,.07); }
#menu-nestable .dd-handle {
    display: flex; align-items: center; justify-content: center; flex: 0 0 44px; align-self: stretch;
    background: #f6f8fb; color: #aab2c0; cursor: grab; border-right: 1px solid #eef1f6; text-decoration: none;
}
#menu-nestable .dd-handle:hover { background: #edf3ff; color: #4f8ef7; }
#menu-nestable .dd-handle:active { cursor: grabbing; }
#menu-nestable .mi-content { display: flex; align-items: center; gap: 10px; flex: 1 1 auto; padding: 0 14px; min-width: 0; min-height: 46px; }
#menu-nestable .mi-content > i:first-child { width: 18px; text-align: center; color: #5b6472; }
#menu-nestable .mi-title { font-weight: 600; color: #2b3445; white-space: nowrap; }
#menu-nestable .mi-url { font-size: 12px; color: #8a93a5; background: #f4f6fa; padding: 2px 8px; border-radius: 4px; white-space: nowrap; }
#menu-nestable .mi-actions { margin-left: auto; display: flex; gap: 6px; white-space: nowrap; padding-left: 8px; }
#menu-nestable .mi-inactive { opacity: .5; }
/* Trạng thái kéo */
#menu-nestable .dd-placeholder {
    background: #eaf2ff; border: 2px dashed #4f8ef7; border-radius: 8px; min-height: 48px; margin: 10px 0; box-sizing: border-box;
}
#menu-nestable .dd-empty { min-height: 48px; }
#menu-nestable .dd-dragel { position: absolute; pointer-events: none; z-index: 9999; }
#menu-nestable .dd-dragel > .dd-item > .mi-wrap { box-shadow: 0 14px 34px rgba(20,30,60,.22); }
/* ẩn nút collapse/expand mặc định của nestable */
#menu-nestable .dd-item > button[data-action] { display: none; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/nestable2@1.6.0/dist/jquery.nestable.min.js"></script>
<script>
$(function () {
    var CSRF = '{{ csrf_token() }}';

    // Nestable: kéo-thả, lồng tối đa 2 cấp (cha -> con dropdown)
    if ($.fn.nestable) {
        $('#menu-nestable').nestable({ handleClass: 'dd-handle', maxDepth: 2 });
    }

    // Lưu sắp xếp
    $('#save-order').on('click', function () {
        var $btn = $(this).prop('disabled', true);
        var data = $('#menu-nestable').nestable('serialize');
        $.ajax({
            url: $btn.data('url'), method: 'POST', dataType: 'json',
            headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            data: { items: JSON.stringify(data) }
        }).done(function (res) {
            $('#save-status').html(res.ok ? '<span class="text-success"><i class="fas fa-check"></i> Đã lưu</span>' : '');
        }).fail(function () {
            $('#save-status').html('<span class="text-danger">Lỗi lưu</span>');
        }).always(function () {
            $btn.prop('disabled', false);
            setTimeout(function () { $('#save-status').html(''); }, 2500);
        });
    });

    // Mở modal sửa
    $('#menu-nestable').on('click', '.btn-edit-item', function () {
        var d = $(this).data();
        var $f = $('#edit-item-form');
        $f.attr('action', d.action);
        $f.find('[name=label]').val(d.label);
        $f.find('[name=url]').val(d.url);
        $f.find('[name=icon]').val(d.icon || '');
        $f.find('[name=target]').val(d.target || '_self');
        $f.find('[name=label_key]').val(d.labelKey || '');
        $f.find('[name=is_active]').prop('checked', d.active == 1);
        $('#edit-item-modal').modal('show');
    });
});
</script>
@endpush
