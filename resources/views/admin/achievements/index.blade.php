@extends('layout.admin')
@section('template_title', 'Thành tích (Achievements)')

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
@endif
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
@endif

@php
    $catLabels = ['reading' => '📖 Đọc truyện', 'social' => '💬 Cộng đồng', 'support' => '💎 Ủng hộ'];
    $metricLabels = [
        // Đọc
        'chapters_read'     => '📖 Chương đã đọc',
        'articles_read'     => '📚 Truyện đã đọc',
        // Tương tác
        'comments_posted'   => '💬 Bình luận',
        'chapter_likes'     => '❤️ Thích chương',
        'comment_votes'     => '👍 Vote bình luận',
        // Mở khoá
        'chapters_unlocked' => '🔓 Chương đã mở khoá',
        'credits_spent'     => '💸 Xu đã chi',
        // Theo dõi
        'bookmarks'         => '🔖 Theo dõi truyện',
        // Diễn đàn
        'forum_posts'       => '📝 Bài đăng diễn đàn',
        'forum_comments'    => '🗨️ Bình luận diễn đàn',
        // Nạp tiền
        'deposit_count'     => '💰 Số lần nạp',
        'deposit_total'     => '💎 Tổng nạp (VNĐ)',
    ];
    $coinLabel = coin_name();
@endphp

{{-- ===== CREATE FORM ===== --}}
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center" style="cursor:pointer" onclick="document.getElementById('ach-create-body').classList.toggle('d-none')">
        <h3 class="card-title mb-0"><i class="fas fa-plus-circle text-success mr-1"></i> Tạo thành tích mới</h3>
        <i class="fas fa-chevron-down text-muted"></i>
    </div>
    <div class="card-body d-none" id="ach-create-body">
        <form action="{{ route('admin.achievements.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="small font-weight-bold">Tên (VI) <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" class="form-control form-control-sm" required>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="small font-weight-bold">Tên (EN)</label>
                        <input type="text" name="name_en" value="{{ old('name_en') }}" class="form-control form-control-sm">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="small font-weight-bold">Mô tả (VI)</label>
                        <input type="text" name="description" value="{{ old('description') }}" class="form-control form-control-sm">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="small font-weight-bold">Mô tả (EN)</label>
                        <input type="text" name="description_en" value="{{ old('description_en') }}" class="form-control form-control-sm">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="small font-weight-bold">Nhóm <span class="text-danger">*</span></label>
                        <select name="category" class="form-control form-control-sm" required>
                            @foreach($catLabels as $val => $lbl)
                            <option value="{{ $val }}" {{ old('category')==$val?'selected':'' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="small font-weight-bold">Metric <span class="text-danger">*</span></label>
                        <select name="metric" class="form-control form-control-sm" required>
                            <option value="">-- Chọn metric --</option>
                            @foreach($metricLabels as $val => $lbl)
                            <option value="{{ $val }}" {{ old('metric')==$val?'selected':'' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-1">
                    <div class="form-group">
                        <label class="small font-weight-bold">Mục tiêu <span class="text-danger">*</span></label>
                        <input type="number" name="target" value="{{ old('target',1) }}" class="form-control form-control-sm" min="1" required>
                    </div>
                </div>
                <div class="col-md-1">
                    <div class="form-group">
                        <label class="small font-weight-bold">Thưởng {{ $coinLabel }}</label>
                        <input type="number" name="reward_credits" value="{{ old('reward_credits',1) }}" class="form-control form-control-sm" min="0" required>
                    </div>
                </div>
                <div class="col-md-1">
                    <div class="form-group">
                        <label class="small font-weight-bold">Thứ tự</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order',0) }}" class="form-control form-control-sm" min="0" required>
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="form-group">
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="fa fa-plus"></i> Tạo mới
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ===== TABLE ===== --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">
            <i class="fas fa-award mr-1 text-warning"></i> Thành tích
            <span class="badge badge-info ml-1">{{ $achievements->count() }}</span>
        </h3>
        <span class="text-muted small">Bấm <i class="fa fa-edit text-info"></i> để chỉnh sửa trực tiếp trong bảng</span>
    </div>
    <div class="card-body p-0">
        @foreach($achievements->groupBy('category') as $cat => $group)
        <div class="px-3 pt-3 pb-1">
            <strong class="text-muted small text-uppercase">{{ $catLabels[$cat] ?? $cat }}</strong>
        </div>
        <table class="table table-bordered table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th width="40" class="text-center">#</th>
                    <th>Tên (VI)</th>
                    <th>Tên (EN)</th>
                    <th>Mô tả</th>
                    <th width="130">Metric</th>
                    <th width="90" class="text-center">Mục tiêu</th>
                    <th width="100" class="text-center">Thưởng {{ $coinLabel }}</th>
                    <th width="70" class="text-center">Thứ tự</th>
                    <th width="90" class="text-center">Đã đạt</th>
                    <th width="120" class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @foreach($group as $ach)
                {{-- VIEW ROW --}}
                <tr id="ach-row-{{ $ach->id }}">
                    <td class="text-center text-muted small">{{ $ach->sort_order }}</td>
                    <td><strong>{{ $ach->name }}</strong></td>
                    <td class="text-muted small">{{ $ach->name_en }}</td>
                    <td class="small text-muted">{{ \Illuminate\Support\Str::limit($ach->description ?? $ach->description_en, 60) }}</td>
                    <td class="small"><span class="badge badge-light">{{ $metricLabels[$ach->metric] ?? $ach->metric }}</span></td>
                    <td class="text-center"><span class="badge badge-secondary">{{ number_format($ach->target) }}</span></td>
                    <td class="text-center"><span class="badge badge-warning">{{ $ach->reward_credits }} {{ $coinLabel }}</span></td>
                    <td class="text-center"><span class="badge badge-light">{{ $ach->sort_order }}</span></td>
                    <td class="text-center">
                        <a href="{{ route('admin.achievements.users', $ach->id) }}" class="badge badge-info" style="font-size:12px">
                            {{ number_format($ach->users_count) }} người
                        </a>
                    </td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-info" onclick="achToggleEdit({{ $ach->id }})" title="Chỉnh sửa">
                            <i class="fa fa-edit"></i>
                        </button>
                        <form action="{{ route('admin.achievements.destroy', $ach->id) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Xoá thành tích này? Tất cả user đã đạt cũng bị xoá.')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger" title="Xoá"><i class="fa fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                {{-- EDIT ROW --}}
                <tr id="ach-edit-{{ $ach->id }}" style="display:none;background:var(--light,#f8f9fa)">
                    <td class="text-center text-muted small">{{ $ach->id }}</td>
                    <td colspan="8">
                        <form action="{{ route('admin.achievements.update', $ach->id) }}" method="POST">
                            @csrf @method('PUT')
                            <div class="d-flex flex-wrap align-items-end" style="gap:8px">
                                <div>
                                    <label class="small mb-1">Tên (VI)<span class="text-danger">*</span></label>
                                    <input type="text" name="name" value="{{ $ach->name }}" class="form-control form-control-sm" style="width:150px" required>
                                </div>
                                <div>
                                    <label class="small mb-1">Tên (EN)</label>
                                    <input type="text" name="name_en" value="{{ $ach->name_en }}" class="form-control form-control-sm" style="width:150px">
                                </div>
                                <div>
                                    <label class="small mb-1">Mô tả (VI)</label>
                                    <input type="text" name="description" value="{{ $ach->description }}" class="form-control form-control-sm" style="width:180px">
                                </div>
                                <div>
                                    <label class="small mb-1">Mô tả (EN)</label>
                                    <input type="text" name="description_en" value="{{ $ach->description_en }}" class="form-control form-control-sm" style="width:180px">
                                </div>
                                <div>
                                    <label class="small mb-1">Mục tiêu</label>
                                    <input type="number" name="target" value="{{ $ach->target }}" class="form-control form-control-sm" style="width:85px" min="1" required>
                                </div>
                                <div>
                                    <label class="small mb-1">Thưởng {{ $coinLabel }}</label>
                                    <input type="number" name="reward_credits" value="{{ $ach->reward_credits }}" class="form-control form-control-sm" style="width:75px" min="0" required>
                                </div>
                                <div>
                                    <label class="small mb-1">Thứ tự</label>
                                    <input type="number" name="sort_order" value="{{ $ach->sort_order }}" class="form-control form-control-sm" style="width:65px" min="0" required>
                                </div>
                                <div class="d-flex" style="gap:6px;margin-top:18px">
                                    <button class="btn btn-sm btn-success"><i class="fa fa-check"></i> Lưu</button>
                                    <button type="button" class="btn btn-sm btn-secondary" onclick="achToggleEdit({{ $ach->id }})">Huỷ</button>
                                </div>
                            </div>
                        </form>
                    </td>
                    <td></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endforeach
    </div>
</div>

<script>
function achToggleEdit(id) {
    var row  = document.getElementById('ach-row-' + id);
    var edit = document.getElementById('ach-edit-' + id);
    var show = edit.style.display === 'none';
    row.style.display  = show ? 'none' : '';
    edit.style.display = show ? '' : 'none';
}

// Mở create form nếu có lỗi validation
@if($errors->isNotEmpty())
document.getElementById('ach-create-body').classList.remove('d-none');
@endif
</script>
@endsection
