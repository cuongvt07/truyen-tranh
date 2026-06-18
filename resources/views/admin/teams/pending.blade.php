@extends('layout.admin')
@section('template_title', 'Duyệt thành viên nhóm dịch')

@section('content')
<div class="content">
    <div class="container-fluid">
        @includeWhen(session('success'), 'admin.partials.flash')

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">
                <i class="fas fa-clock text-warning"></i> Yêu cầu thêm thành viên đang chờ duyệt
            </h4>
            <a href="{{ route('admin.teams.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Về danh sách nhóm
            </a>
        </div>

        <div class="card">
            <div class="card-body p-0">
                @if($members->isEmpty())
                    <div class="text-center py-5">
                        <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                        <h5 class="text-muted">Không có yêu cầu nào đang chờ</h5>
                    </div>
                @else
                <table class="table table-hover table-bordered mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Nhóm dịch</th>
                            <th>User muốn thêm</th>
                            <th>Trưởng nhóm yêu cầu</th>
                            <th>Thời gian</th>
                            <th width="200" class="text-center">Thao tác</th>
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
                                @{{ optional($m->requester)->username ?? '—' }}
                            </td>
                            <td class="align-middle small text-muted">{{ $m->created_at->diffForHumans() }}</td>
                            <td class="align-middle text-center">
                                <form method="POST" action="{{ route('admin.teams.members.approve', [$m->team_id, $m->id]) }}" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-success"><i class="fas fa-check"></i> Duyệt</button>
                                </form>
                                <button class="btn btn-sm btn-danger" onclick="showReject({{ $m->id }})">
                                    <i class="fas fa-times"></i> Từ chối
                                </button>
                                <form id="reject-form-{{ $m->id }}" method="POST"
                                      action="{{ route('admin.teams.members.reject', [$m->team_id, $m->id]) }}"
                                      class="d-none mt-2">
                                    @csrf
                                    <div class="input-group input-group-sm">
                                        <input type="text" name="note" class="form-control" placeholder="Lý do từ chối (tuỳ chọn)">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-danger">Gửi</button>
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
