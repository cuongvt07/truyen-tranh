@extends('layout.admin')

@section('template_title', 'Forum Comments')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Forum Comments</h3>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Post</th>
                    <th>Author</th>
                    <th>Content</th>
                    <th>Score</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($comments as $comment)
                    <tr>
                        <td>{{ $comment->id }}</td>
                        <td>{{ Str::limit($comment->post->title_en, 30) }}</td>
                        <td>{{ $comment->user->username ?? 'Unknown' }}</td>
                        <td>{{ Str::limit($comment->content, 50) }}</td>
                        <td>{{ $comment->score }}</td>
                        <td>{{ $comment->created_at->format('Y-m-d H:i') }}</td>
                        <td>
                            <form action="{{ route('admin.forum.comments.destroy', $comment->id) }}" method="POST" class="d-inline formDelete">
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
                        <td colspan="7" class="text-center">No comments found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{ $comments->links() }}
    </div>
</div>
@endsection
