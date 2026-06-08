@extends('layout.admin')
@section('template_title', 'Bộ sưu tập')

@section('content')
<div class="content"><div class="container-fluid">
    @includeWhen(session('success'), 'admin.partials.flash')
    <div class="d-flex justify-content-end mb-2"><a href="{{ route('admin.collections.create') }}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Tạo bộ sưu tập</a></div>
    <div class="card card-outline card-primary mb-3"><div class="card-body py-2">
        <form method="GET" class="form-row">
            <div class="col-md-4 mb-2"><div class="input-group input-group-sm">
                <input type="text" name="q" class="form-control" placeholder="Tìm theo tên..." value="{{ request('q') }}">
                <div class="input-group-append"><button class="btn btn-primary"><i class="fas fa-search"></i></button></div>
            </div></div>
            <div class="col-md-2 mb-2"><a href="{{ route('admin.collections.index') }}" class="btn btn-sm btn-outline-secondary btn-block"><i class="fas fa-times"></i> Xoá lọc</a></div>
        </form>
    </div></div>

    <div class="card"><div class="card-body p-0">
        @forelse($items as $item)
        @if($loop->first)
        <table class="table table-hover mb-0"><thead><tr>
            <th width="60">#</th><th>Tên</th><th>Số truyện</th><th>Hiển thị</th><th>Người tạo</th><th width="120" class="text-center">Thao tác</th>
        </tr></thead><tbody>
        @endif
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td class="font-weight-500">{{ $item->name }}</td>
                <td>{{ $item->articles_count }}</td>
                <td>@if($item->is_private)<span class="badge badge-secondary">Riêng tư</span>@else<span class="badge badge-success">Công khai</span>@endif</td>
                <td class="text-muted small">{{ optional($item->user)->username ?? '—' }}</td>
                <td class="text-center"><div class="btn-group btn-group-sm">
                    <a href="{{ route('admin.collections.edit', $item->id) }}" class="btn btn-outline-primary"><i class="fas fa-edit"></i></a>
                    <form method="post" action="{{ route('admin.collections.destroy', $item->id) }}" class="form-delete d-inline" data-confirm="Xoá bộ sưu tập «{{ $item->name }}»?">@csrf @method('delete')<button class="btn btn-outline-danger"><i class="fas fa-trash"></i></button></form>
                </div></td>
            </tr>
            @if($loop->last)</tbody></table>@endif
        @empty
            <div class="text-center py-5"><i class="fas fa-inbox fa-3x text-muted mb-3"></i><h5 class="text-muted">Chưa có bộ sưu tập nào</h5><a href="{{ route('admin.collections.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Tạo ngay</a></div>
        @endforelse
    </div>
    @if($items->hasPages())<div class="card-footer">{{ $items->withQueryString()->links() }}</div>@endif
    </div>
</div></div>
@endsection
