@extends('layout.admin')
@section('template_title', 'Người đạt: ' . $achievement->name)

@section('content')
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h3 class="card-title mb-0">
            <i class="fas fa-award text-warning mr-1"></i>
            {{ $achievement->name }}
            @if($achievement->name_en) <small class="text-muted">/ {{ $achievement->name_en }}</small> @endif
        </h3>
        <a href="{{ route('admin.achievements.index') }}" class="btn btn-sm btn-secondary">
            <i class="fa fa-arrow-left"></i> Quay lại
        </a>
    </div>
    <div class="card-body p-0 table-responsive">
        @forelse($users as $ua)
        @if($loop->first)
        <table class="table table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Người dùng</th>
                    <th>Email</th>
                    <th width="160">Ngày đạt được</th>
                </tr>
            </thead>
            <tbody>
        @endif
            <tr>
                <td>
                    <a href="{{ route('admin.users.show', $ua->user->id) }}">{{ $ua->user->name ?? $ua->user->username }}</a>
                    @if($ua->user->username)<small class="text-muted ml-1">@{{ $ua->user->username }}</small>@endif
                </td>
                <td class="small text-muted">{{ $ua->user->email }}</td>
                <td class="small text-muted">{{ optional($ua->unlocked_at)->format('d/m/Y H:i') }}</td>
            </tr>
        @if($loop->last)</tbody></table>@endif
        @empty
            <div class="text-center py-5 text-muted">
                <i class="fas fa-users fa-2x mb-2 d-block"></i>
                Chưa có người nào đạt thành tích này.
            </div>
        @endforelse
    </div>
    @if($users->hasPages())
        <div class="card-footer">{{ $users->links() }}</div>
    @endif
</div>
@endsection
