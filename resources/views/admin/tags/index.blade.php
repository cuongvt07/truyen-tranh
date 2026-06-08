@extends('layout.admin')
@section('template_title', 'Tags')

@section('content')
<div class="content"><div class="container-fluid">
    @includeWhen(session('success'), 'admin.partials.flash')
    @if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif

    <div class="row">
        {{-- Thêm tag + gộp tag --}}
        <div class="col-md-4">
            <div class="card card-primary card-outline">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-plus mr-2"></i>Thêm tag</h3></div>
                <div class="card-body">
                    <form method="post" action="{{ route('admin.tags.store') }}" class="input-group">
                        @csrf
                        <input type="text" name="name" class="form-control" placeholder="Tên tag mới..." required>
                        <div class="input-group-append"><button class="btn btn-primary">Thêm</button></div>
                    </form>
                </div>
            </div>

            <div class="card card-secondary card-outline">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-code-merge mr-2"></i>Gộp tag</h3></div>
                <div class="card-body">
                    <form method="post" action="{{ route('admin.tags.merge') }}">
                        @csrf
                        <div class="form-group">
                            <label class="small">Gộp các tag (nguồn):</label>
                            <select name="source_ids[]" class="form-control" multiple size="6">
                                @foreach($allTags as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach
                            </select>
                            <small class="text-muted">Ctrl/Shift để chọn nhiều</small>
                        </div>
                        <div class="form-group">
                            <label class="small">Vào tag đích:</label>
                            <select name="target_id" class="form-control" required>
                                <option value="">-- chọn tag giữ lại --</option>
                                @foreach($allTags as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach
                            </select>
                        </div>
                        <button class="btn btn-warning btn-sm" onclick="return confirm('Gộp các tag đã chọn? Tag nguồn sẽ bị xoá.')"><i class="fas fa-code-merge"></i> Gộp</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Danh sách --}}
        <div class="col-md-8">
            <div class="card card-outline card-primary mb-2"><div class="card-body py-2">
                <form method="GET" class="input-group input-group-sm">
                    <input type="text" name="q" class="form-control" placeholder="Tìm tag..." value="{{ request('q') }}">
                    <div class="input-group-append"><button class="btn btn-primary"><i class="fas fa-search"></i></button></div>
                </form>
            </div></div>

            <div class="card">
                <div class="card-header"><span class="text-muted small">Tổng {{ number_format($total) }} tag</span></div>
                <div class="card-body p-0">
                    @forelse($items as $item)
                    @if($loop->first)<table class="table table-hover mb-0"><thead><tr><th>Tên tag</th><th width="100">Số truyện</th><th width="160" class="text-center">Thao tác</th></tr></thead><tbody>@endif
                        <tr>
                            <td>
                                <form method="post" action="{{ route('admin.tags.update', $item->id) }}" class="input-group input-group-sm" style="max-width:320px">
                                    @csrf @method('patch')
                                    <input type="text" name="name" value="{{ $item->name }}" class="form-control">
                                    <div class="input-group-append"><button class="btn btn-outline-primary" title="Lưu"><i class="fas fa-save"></i></button></div>
                                </form>
                            </td>
                            <td><span class="badge badge-info">{{ $item->articles_count }}</span></td>
                            <td class="text-center">
                                <form method="post" action="{{ route('admin.tags.destroy', $item->id) }}" class="form-delete d-inline" data-confirm="Xoá tag «{{ $item->name }}»?">
                                    @csrf @method('delete')
                                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i> Xoá</button>
                                </form>
                            </td>
                        </tr>
                        @if($loop->last)</tbody></table>@endif
                    @empty
                        <div class="text-center py-5"><i class="fas fa-tags fa-3x text-muted mb-3"></i><h5 class="text-muted">Chưa có tag nào</h5></div>
                    @endforelse
                </div>
                @if($items->hasPages())<div class="card-footer">{{ $items->withQueryString()->links() }}</div>@endif
            </div>
        </div>
    </div>
</div></div>
@endsection
