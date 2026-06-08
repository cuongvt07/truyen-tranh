@extends('layout.admin')
@section('template_title', 'Tất cả chương')

@section('content')
<div class="content"><div class="container-fluid">
    @includeWhen(session('success'), 'admin.partials.flash')

    <div class="card card-outline card-primary mb-3"><div class="card-body py-2">
        <form method="GET" class="form-row align-items-center">
            <div class="col-md-4 mb-2"><div class="input-group input-group-sm">
                <input type="text" name="q" class="form-control" placeholder="Tìm theo tên chương..." value="{{ request('q') }}">
                <div class="input-group-append"><button class="btn btn-primary"><i class="fas fa-search"></i></button></div>
            </div></div>
            <div class="col-md-4 mb-2">
                <select name="article_id" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="">— Tất cả truyện —</option>
                    @foreach($articles as $a)
                        <option value="{{ $a->id }}" @selected(request('article_id')==$a->id)>{{ \Illuminate\Support\Str::limit($a->title, 50) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mb-2"><a href="{{ route('admin.chapters.all') }}" class="btn btn-sm btn-outline-secondary btn-block"><i class="fas fa-times"></i> Xoá lọc</a></div>
        </form>
    </div></div>

    <div class="card"><div class="card-body p-0">
        @forelse($chapters as $ch)
        @if($loop->first)<table class="table table-hover mb-0"><thead><tr>
            <th width="80">Chương</th><th>Tên chương</th><th>Truyện</th><th width="90">Lượt xem</th><th width="110">Ngày</th><th width="120" class="text-center">Thao tác</th>
        </tr></thead><tbody>@endif
            <tr>
                <td><span class="badge badge-secondary">#{{ $ch->number }}</span></td>
                <td class="font-weight-500">{{ \Illuminate\Support\Str::limit($ch->title, 60) }}</td>
                <td class="text-muted small">{{ \Illuminate\Support\Str::limit(optional($ch->article)->title, 40) ?? '—' }}</td>
                <td>{{ number_format($ch->view) }}</td>
                <td class="text-muted small">{{ optional($ch->created_at)->format('d/m/Y') }}</td>
                <td class="text-center">
                    @if($ch->article)
                    <div class="btn-group btn-group-sm">
                        <a href="{{ route('articles.chapters.show', [$ch->article_id, $ch->number]) }}" target="_blank" class="btn btn-outline-secondary" title="Xem"><i class="fas fa-eye"></i></a>
                        <a href="{{ route('admin.articles.edit_chapter', [$ch->article_id, $ch->id]) }}" class="btn btn-outline-primary" title="Sửa"><i class="fas fa-edit"></i></a>
                    </div>
                    @endif
                </td>
            </tr>
            @if($loop->last)</tbody></table>@endif
        @empty
            <div class="text-center py-5"><i class="fas fa-inbox fa-3x text-muted mb-3"></i><h5 class="text-muted">Chưa có chương nào</h5></div>
        @endforelse
    </div>
    @if($chapters->hasPages())<div class="card-footer">{{ $chapters->withQueryString()->links() }}</div>@endif
    </div>
</div></div>
@endsection
