@extends('layout.admin')
@section('template_title', 'Nhân vật')

@section('content')
<div class="content"><div class="container-fluid">
    @includeWhen(session('success'), 'admin.partials.flash')
    <div class="d-flex justify-content-end mb-2"><a href="{{ route('admin.characters.create') }}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Thêm nhân vật</a></div>

    {{-- Filter --}}
    <div class="card card-outline card-primary mb-3">
        <div class="card-body py-2">
            <form method="GET" class="form-row align-items-center">
                <div class="col-md-4 mb-2">
                    <div class="input-group input-group-sm">
                        <input type="text" name="q" class="form-control" placeholder="Tìm theo tên..." value="{{ request('q') }}">
                        <div class="input-group-append"><button class="btn btn-primary"><i class="fas fa-search"></i></button></div>
                    </div>
                </div>
                <div class="col-md-3 mb-2">
                    <select name="type" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">Tất cả loại</option>
                        <option value="0" @selected(request('type')==='0')>Nhân vật chính</option>
                        <option value="1" @selected(request('type')==='1')>Nhân vật phụ</option>
                        <option value="2" @selected(request('type')==='2')>Khác</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <a href="{{ route('admin.characters.index') }}" class="btn btn-sm btn-outline-secondary btn-block"><i class="fas fa-times"></i> Xoá lọc</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="card-header"><span class="text-muted small">Hiển thị {{ $items->firstItem() ?? 0 }}–{{ $items->lastItem() ?? 0 }} / {{ $items->total() }}</span></div>
        <div class="card-body p-0">
            @forelse($items as $item)
            @if($loop->first)
            <table class="table table-hover mb-0">
                <thead><tr>
                    <th width="60">#</th><th width="70">Ảnh</th><th>Tên</th><th>Loại</th><th>Số truyện</th><th>Người tạo</th><th width="120" class="text-center">Thao tác</th>
                </tr></thead>
                <tbody>
            @endif
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td><img src="{{ $item->photo ?: asset('static/account/images/no-ava.jpg') }}" style="width:44px;height:44px;border-radius:50%;object-fit:cover"></td>
                    <td class="font-weight-500">{{ $item->name }}</td>
                    <td><span class="badge badge-info">{{ ['Chính','Phụ','Khác'][$item->type] ?? 'Khác' }}</span></td>
                    <td>{{ $item->articles_count }}</td>
                    <td class="text-muted small">{{ optional($item->user)->username ?? '—' }}</td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('admin.characters.edit', $item->id) }}" class="btn btn-outline-primary" title="Sửa"><i class="fas fa-edit"></i></a>
                            <form method="post" action="{{ route('admin.characters.destroy', $item->id) }}" class="form-delete d-inline" data-confirm="Xoá nhân vật «{{ $item->name }}»?">
                                @csrf @method('delete')
                                <button class="btn btn-outline-danger" title="Xoá"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @if($loop->last)</tbody></table>@endif
            @empty
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Chưa có nhân vật nào</h5>
                    <a href="{{ route('admin.characters.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Thêm ngay</a>
                </div>
            @endforelse
        </div>
        @if($items->hasPages())
        <div class="card-footer">{{ $items->withQueryString()->links() }}</div>
        @endif
    </div>
</div></div>
@endsection
