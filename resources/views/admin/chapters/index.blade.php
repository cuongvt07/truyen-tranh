@extends('layout.admin')

@section('template_title')
    {{ __('Danh sách chương của truyện ' . $article->title) }}
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <div class="create" style="margin-bottom: 10px">
                        <a href="{{ route('admin.articles.create_chapter', $article->id) }}" class="btn btn-primary"
                           data-placement="left">
                            {{ __('Thêm chương mới') }}
                        </a>
                        <div class="float-right">
                            <a class="btn btn-primary"
                               href="{{ route('admin.articles.index') }}"> {{ __('Trở lại') }}</a>
                        </div>
                    </div>

                    <h5 class="mt-2 mb-2"><i class="fas fa-list-ol text-muted mr-1"></i> {{ $article->title }}</h5>
                    <div class="search d-flex flex-wrap align-items-center justify-content-between" style="gap:8px">
                        <form id="searchForm" action="{{ route('admin.articles.show_chapters', $article->id) }}"
                              method="GET" class="form-inline" style="gap:6px">
                            <input type="hidden" name="per_page" value="{{ $perPage }}">
                            <input type="search" id="searchInput" class="form-control form-control-sm"
                                   placeholder="Tìm theo tên chương" name="search" value="{{ request('search') }}">
                            <button class="btn btn-sm btn-outline-primary"><i class="fa fa-search"></i></button>
                            @if(request('search'))
                                <a href="{{ route('admin.articles.show_chapters', $article->id) }}" class="btn btn-sm btn-outline-secondary">Bỏ lọc</a>
                            @endif
                        </form>
                        <form method="GET" action="{{ route('admin.articles.show_chapters', $article->id) }}"
                              class="form-inline" style="gap:6px">
                            @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
                            <span class="small text-muted">Tổng {{ number_format($chapters->total()) }} chương · Hiển thị</span>
                            <select name="per_page" class="form-control form-control-sm" style="width:auto" onchange="this.form.submit()">
                                @foreach($perPageOptions as $opt)
                                    <option value="{{ $opt }}" {{ $perPage == $opt ? 'selected' : '' }}>{{ $opt }}/trang</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    @if ($message = session('success'))
                        <div class="alert alert-success">
                            <p>{{ $message }}</p>
                        </div>
                    @endif
                    <div class="dataTables_wrapper dt-bootstrap4">
                        <div class="row">
                            <div class="col-sm-12">
                                <table data-bs-spy="scroll"
                                       class="table table-bordered table-striped dataTable dtr-inline table-hover"
                                       aria-describedby="example1_info">
                                    <thead>
                                    <tr>
                                        <th>Thứ tự chương</th>
                                        <th>Tên chương</th>
                                        <th>Lượt xem</th>
                                        <th>Ngày tạo</th>
                                        <th>Ngày sửa</th>
                                        <th>Hành động</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($chapters as $chapter)
                                        <tr class="even">
                                            <td>{{ $chapter->number_text }}</td>
                                            <td>
                                                <a href="{{ route('articles.chapters.show', [$article, $chapter->number]) }}">{{ $chapter->title }}</a>
                                            </td>
                                            <td>{{ $chapter->view_text }}</td>
                                            <td title="{{ $chapter->created_at }}">{{ $chapter->created_at_text }}</td>
                                            <td title="{{ $chapter->updated_at }}">{{ $chapter->updated_at_text }}</td>
                                            <td>
                                                <form
                                                    action="{{ route('admin.articles.destroy_chapter', [$article->id, $chapter->id]) }}"
                                                    method="POST" class="formDelete">
                                                    <a class="btn btn-sm btn-primary"
                                                       href="{{ route('articles.chapters.show', [$article, $chapter->number]) }}"><i
                                                            class="fa fa-fw fa-eye"></i> {{ __('Chi tiết') }}</a>
                                                    <a class="btn btn-sm btn-success"
                                                       href="{{ route('admin.articles.edit_chapter', [$article->id, $chapter->id]) }}"><i
                                                            class="fa fa-fw fa-edit"></i> {{ __('Sửa') }}</a>
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm btnDelete">
                                                        <i
                                                            class="fa fa-fw fa-trash"></i> {{ __('Xoá') }}
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 col-md-7">
                                {!! $chapters->links() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
