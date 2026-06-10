@extends('layout.admin')

@section('template_title', 'FAQ Articles')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title">FAQ Articles</h3>
            <a href="{{ route('admin.faq.articles.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add Article
            </a>
        </div>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="GET" class="mb-3">
            <div class="row">
                <div class="col-md-3">
                    <select name="category_id" class="form-control">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->title_en }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </div>
        </form>

        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Sort Order</th>
                    <th>Views</th>
                    <th>Active</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($articles as $article)
                    <tr>
                        <td>{{ $article->id }}</td>
                        <td>
                            {{ Str::limit($article->title_en, 50) }}
                            @if($article->is_pinned)<i class="fas fa-thumbtack text-danger ml-1"></i>@endif
                        </td>
                        <td>{{ $article->category->title_en }}</td>
                        <td>{{ $article->sort_order }}</td>
                        <td>{{ $article->view_count }}</td>
                        <td>
                            @if($article->is_active)
                                <span class="badge badge-success">Yes</span>
                            @else
                                <span class="badge badge-secondary">No</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.faq.articles.edit', $article->id) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.faq.articles.destroy', $article->id) }}" method="POST" class="d-inline formDelete">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger btnDelete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">No articles found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{ $articles->links() }}
    </div>
</div>
@endsection
