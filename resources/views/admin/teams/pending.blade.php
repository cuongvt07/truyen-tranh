@extends('layout.admin')
@section('template_title', 'Duyet yeu cau nhom dich')

@section('content')
<div class="content">
    <div class="container-fluid">
        @includeWhen(session('success'), 'admin.partials.flash')

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">
                <i class="fas fa-clock text-warning"></i> Yeu cau nhom dang cho duyet
            </h4>
            <a href="{{ route('admin.teams.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Ve danh sach nhom
            </a>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <strong>Nhom moi cho duyet</strong>
            </div>
            <div class="card-body p-0">
                @if($teams->isEmpty())
                    <div class="text-center py-4 text-muted">Khong co nhom nao dang cho duyet</div>
                @else
                <table class="table table-hover table-bordered mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Nhom</th>
                            <th>Nguoi tao</th>
                            <th>Thoi gian</th>
                            <th width="220" class="text-center">Thao tac</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($teams as $team)
                        <tr>
                            <td class="align-middle">
                                <strong>{{ $team->name }}</strong>
                                @if($team->description)
                                    <div class="small text-muted">{{ \Str::limit(strip_tags($team->description), 80) }}</div>
                                @endif
                            </td>
                            <td class="align-middle">
                                <strong>@{{ optional($team->user)->username ?? 'N/A' }}</strong>
                                <div class="small text-muted">{{ optional($team->user)->email }}</div>
                            </td>
                            <td class="align-middle small text-muted">{{ $team->created_at->diffForHumans() }}</td>
                            <td class="align-middle text-center">
                                <form method="POST" action="{{ route('admin.teams.approve', $team->id) }}" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-success"><i class="fas fa-check"></i> Duyet</button>
                                </form>
                                <form method="POST" action="{{ route('admin.teams.reject', $team->id) }}" class="d-inline"
                                      onsubmit="return confirm('Tu choi nhom nay?')">
                                    @csrf
                                    <button class="btn btn-sm btn-danger"><i class="fas fa-times"></i> Tu choi</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
            @if($teams->hasPages())
                <div class="card-footer">{{ $teams->links() }}</div>
            @endif
        </div>

        <div class="card">
            <div class="card-header">
                <strong>Yeu cau them thanh vien</strong>
            </div>
            <div class="card-body p-0">
                @if($members->isEmpty())
                    <div class="text-center py-4 text-muted">Khong co yeu cau thanh vien nao dang cho</div>
                @else
                <table class="table table-hover table-bordered mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Nhom dich</th>
                            <th>User muon them</th>
                            <th>Nguoi yeu cau</th>
                            <th>Thoi gian</th>
                            <th width="220" class="text-center">Thao tac</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($members as $m)
                        <tr>
                            <td class="align-middle">
                                <a href="{{ route('admin.teams.members', $m->team_id) }}" class="font-weight-bold">
                                    {{ optional($m->team)->name ?? '#' . $m->team_id }}
                                </a>
                            </td>
                            <td class="align-middle">
                                <strong>@{{ $m->user->username ?? '?' }}</strong>
                                <div class="small text-muted">{{ $m->user->email ?? '' }}</div>
                            </td>
                            <td class="align-middle small text-muted">
                                @{{ optional($m->requester)->username ?? '-' }}
                            </td>
                            <td class="align-middle small text-muted">{{ $m->created_at->diffForHumans() }}</td>
                            <td class="align-middle text-center">
                                <form method="POST" action="{{ route('admin.teams.members.approve', [$m->team_id, $m->id]) }}" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-success"><i class="fas fa-check"></i> Duyet</button>
                                </form>
                                <button class="btn btn-sm btn-danger" onclick="showReject({{ $m->id }})">
                                    <i class="fas fa-times"></i> Tu choi
                                </button>
                                <form id="reject-form-{{ $m->id }}" method="POST"
                                      action="{{ route('admin.teams.members.reject', [$m->team_id, $m->id]) }}"
                                      class="d-none mt-2">
                                    @csrf
                                    <div class="input-group input-group-sm">
                                        <input type="text" name="note" class="form-control" placeholder="Ly do tu choi">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-danger">Gui</button>
                                        </div>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
            @if($members->hasPages())
                <div class="card-footer">{{ $members->links() }}</div>
            @endif
        </div>
    </div>
</div>

<script>
function showReject(id) {
    document.getElementById('reject-form-' + id).classList.toggle('d-none');
}
</script>
@endsection
