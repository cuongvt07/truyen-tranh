@extends('layout.admin')
@section('template_title', 'Chương (theo truyện)')

@section('content')
<div class="content"><div class="container-fluid">
    @includeWhen(session('success'), 'admin.partials.flash')

    {{-- Thanh tìm + chọn số item/trang --}}
    <div class="card card-outline card-primary mb-3"><div class="card-body py-2 d-flex flex-wrap align-items-center justify-content-between" style="gap:10px">
        <form method="GET" class="form-inline" style="gap:6px">
            <input type="hidden" name="per_page" value="{{ $perPage }}">
            <input type="text" name="q" class="form-control form-control-sm" placeholder="Tìm theo tên truyện..." value="{{ request('q') }}" style="min-width:240px">
            <button class="btn btn-sm btn-primary"><i class="fas fa-search"></i></button>
            @if(request('q'))
                <a href="{{ route('admin.chapters.all') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times"></i> Xoá lọc</a>
            @endif
        </form>
        <form method="GET" class="form-inline" style="gap:6px">
            @if(request('q'))<input type="hidden" name="q" value="{{ request('q') }}">@endif
            <span class="small text-muted">Hiển thị</span>
            <select name="per_page" class="form-control form-control-sm" style="width:auto" onchange="this.form.submit()">
                @foreach($perPageOptions as $opt)
                    <option value="{{ $opt }}" {{ $perPage == $opt ? 'selected' : '' }}>{{ $opt }}/trang</option>
                @endforeach
            </select>
        </form>
    </div></div>

    <div class="card"><div class="card-body p-0 table-responsive">
        @forelse($articles as $article)
        @if($loop->first)<table class="table table-hover mb-0 align-middle"><thead><tr>
            <th width="60">#</th>
            <th>Truyện</th>
            <th width="120" class="text-center">Số chương</th>
            <th width="140">Chương mới nhất</th>
            <th width="220" class="text-center">Thao tác</th>
        </tr></thead><tbody>@endif
            <tr>
                <td class="text-muted">{{ $article->id }}</td>
                <td>
                    <div class="font-weight-500">{{ \Illuminate\Support\Str::limit($article->title, 70) }}</div>
                </td>
                <td class="text-center"><span class="badge badge-info badge-pill">{{ number_format($article->chapters_count) }}</span></td>
                <td class="text-muted small">{{ $article->chapters_max_created_at ? \Illuminate\Support\Carbon::parse($article->chapters_max_created_at)->format('d/m/Y') : '—' }}</td>
                <td class="text-center">
                    <a href="{{ route('admin.articles.show_chapters', $article->id) }}" class="btn btn-sm btn-primary"><i class="fas fa-list-ol"></i> Chi tiết</a>
                    <a href="{{ route('admin.articles.create_chapter', $article->id) }}" class="btn btn-sm btn-outline-info"><i class="fas fa-plus"></i> Thêm</a>
                </td>
            </tr>
            @if($loop->last)</tbody></table>@endif
        @empty
            <div class="text-center py-5"><i class="fas fa-inbox fa-3x text-muted mb-3"></i><h5 class="text-muted">Không có truyện nào</h5></div>
        @endforelse
    </div>
    @if($articles->hasPages())<div class="card-footer">{{ $articles->links() }}</div>@endif
    </div>
</div></div>
@endsection
