@extends('layout.admin')

@section('template_title', 'Khối trang chủ')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title">Khối hiển thị ngoài trang chủ</h3>
            <a href="{{ route('admin.home-blocks.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Thêm khối
            </a>
        </div>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif

        <p class="text-muted small mb-3">
            Trang chủ render các khối đang bật, theo thứ tự cột "Thứ tự" (nhỏ hiện trước).
            Mỗi khối là một truy vấn riêng nên càng nhiều khối trang càng nặng — dữ liệu được
            cache 3 phút và tự xoá cache ngay khi bạn lưu ở đây.
        </p>

        <table class="table table-striped table-sm">
            <thead>
                <tr>
                    <th style="width:70px">Thứ tự</th>
                    <th>Tiêu đề</th>
                    <th>Nguồn truyện</th>
                    <th style="width:80px">Số truyện</th>
                    <th>Kiểu</th>
                    <th style="width:90px">Trạng thái</th>
                    <th style="width:150px"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($blocks as $block)
                    <tr>
                        <td>{{ $block->order }}</td>
                        <td><strong>{{ $block->title }}</strong></td>
                        <td>
                            {{ $block->source_label }}
                            @if($block->source === 'genre')
                                <span class="badge badge-info">{{ $block->genre->name ?? 'thể loại đã xoá' }}</span>
                            @endif
                        </td>
                        <td>{{ $block->limit }}</td>
                        <td>{{ \App\Models\HomeBlock::VARIANTS[$block->variant] ?? $block->variant }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.home-blocks.toggle', $block) }}">
                                @csrf
                                <button class="btn btn-xs {{ $block->is_active ? 'btn-success' : 'btn-secondary' }}">
                                    {{ $block->is_active ? 'Đang bật' : 'Đang tắt' }}
                                </button>
                            </form>
                        </td>
                        <td class="text-right">
                            <a href="{{ route('admin.home-blocks.edit', $block) }}" class="btn btn-sm btn-primary">Sửa</a>
                            <form method="POST" action="{{ route('admin.home-blocks.destroy', $block) }}"
                                  class="d-inline" onsubmit="return confirm('Xoá khối này khỏi trang chủ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger">Xoá</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">
                            Chưa có khối nào — trang chủ sẽ không hiện phần khám phá.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
