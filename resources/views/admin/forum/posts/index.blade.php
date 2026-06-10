@extends('layout.admin')

@section('template_title', 'Forum Posts')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Forum Posts @if($pendingCount > 0)<span class="badge badge-warning ml-2">{{ $pendingCount }} pending</span>@endif</h3>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="GET" class="mb-3">
            <div class="row">
                <div class="col-md-3">
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
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
                    <th>Author</th>
                    <th>Status</th>
                    <th>Views</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($posts as $post)
                    <tr>
                        <td>{{ $post->id }}</td>
                        <td>
                            {{ Str::limit($post->title_en, 50) }}
                            @if($post->is_pinned)<i class="fas fa-thumbtack text-danger ml-1"></i>@endif
                            @if($post->is_locked)<i class="fas fa-lock text-warning ml-1"></i>@endif
                        </td>
                        <td>{{ $post->category->title_en }}</td>
                        <td>{{ $post->user->username ?? 'System' }}</td>
                        <td>
                            @if($post->status == 'pending')
                                <span class="badge badge-warning">Pending</span>
                            @elseif($post->status == 'approved')
                                <span class="badge badge-success">Approved</span>
                            @else
                                <span class="badge badge-danger">Rejected</span>
                            @endif
                        </td>
                        <td>{{ $post->view_count }}</td>
                        <td>{{ $post->created_at->format('Y-m-d') }}</td>
                        <td>
                            <a href="{{ route('admin.forum.posts.show', $post->id) }}" class="btn btn-sm btn-info" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.forum.posts.edit', $post->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            @if($post->status == 'pending')
                                <form action="{{ route('admin.forum.posts.approve', $post->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.forum.posts.reject', $post->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-secondary" title="Reject">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            @endif
                            <form action="{{ route('admin.forum.posts.destroy', $post->id) }}" method="POST" class="d-inline formDelete">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger btnDelete" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">No posts found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{ $posts->links() }}
    </div>
</div>
@endsection
