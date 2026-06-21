@extends('layout.admin')
@section('template_title', 'Thành viên nhóm: ' . $team->name)

@section('content')
<div class="content">
    <div class="container-fluid">
        @includeWhen(session('success'), 'admin.partials.flash')
        @includeWhen(session('error'), 'admin.partials.flash-error')

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">
                <i class="fas fa-users text-primary"></i>
                Thành viên: <strong>{{ $team->name }}</strong>
            </h4>
            <a href="{{ route('admin.teams.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Về danh sách nhóm
            </a>
        </div>

        <div class="row">
            {{-- Add member form --}}
            <div class="col-md-4">
                <div class="card card-outline card-success">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-user-plus"></i> Thêm thành viên (trực tiếp)</h3></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.teams.members.add', $team->id) }}">
                            @csrf
                            <div class="form-group">
                                <label class="small font-weight-bold">Username</label>
                                <input type="text" name="username" class="form-control form-control-sm" placeholder="@username" required>
                            </div>
                            <div class="form-group">
                                <label class="small font-weight-bold">Vai trò</label>
                                <select name="role" class="form-control form-control-sm">
                                    @foreach(\App\Models\TeamMember::ROLES as $role => $label)
                                        <option value="{{ $role }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-success btn-sm btn-block">
                                <i class="fas fa-plus"></i> Thêm ngay (không cần duyệt)
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Team info --}}
                <div class="card card-outline card-primary mt-3">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle"></i> Thông tin nhóm</h3></div>
                    <div class="card-body p-2">
                        <table class="table table-sm table-borderless mb-0">
                            <tr><td class="text-muted small">ID</td><td><span class="badge badge-secondary">{{ $team->id }}</span></td></tr>
                            <tr><td class="text-muted small">Trưởng nhóm</td><td class="small">{{ optional($team->user)->username ?? '—' }}</td></tr>
                            <tr><td class="text-muted small">Website</td><td class="small">{{ $team->site ? \Str::limit($team->site, 25) : '—' }}</td></tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                {{-- Pending requests --}}
                @php $pending = $team->members->where('status', 'pending'); @endphp
                @if($pending->count())
                <div class="card card-outline card-warning mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-clock text-warning"></i> Chờ duyệt ({{ $pending->count() }})</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>User</th>
                                    <th>Yêu cầu bởi</th>
                                    <th>Thời gian</th>
                                    <th class="text-center">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pending as $m)
                                <tr>
                                    <td>
                                        <strong>{{ $m->user->username ?? '?' }}</strong>
                                        <div class="small text-muted">{{ $m->user->email ?? '' }}</div>
                                    </td>
                                    <td class="small text-muted">{{ optional($m->requester)->username ?? 'leader' }}</td>
                                    <td class="small text-muted">{{ $m->created_at->diffForHumans() }}</td>
                                    <td class="text-center">
                                        <form method="POST" action="{{ route('admin.teams.members.approve', [$team->id, $m->id]) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-xs btn-success"><i class="fas fa-check"></i> Duyệt</button>
                                        </form>
                                        <button class="btn btn-xs btn-danger" onclick="showReject({{ $m->id }})">
                                            <i class="fas fa-times"></i> Từ chối
                                        </button>
                                        <form id="reject-form-{{ $m->id }}" method="POST"
                                              action="{{ route('admin.teams.members.reject', [$team->id, $m->id]) }}"
                                              class="d-none mt-1">
                                            @csrf
                                            <div class="input-group input-group-sm">
                                                <input type="text" name="note" class="form-control" placeholder="Lý do (tuỳ chọn)">
                                                <div class="input-group-append">
                                                    <button type="submit" class="btn btn-danger btn-sm">Gửi</button>
                                                </div>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                {{-- Approved members --}}
                @php $approved = $team->members->where('status', 'approved'); @endphp
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-users"></i> Thành viên ({{ $approved->count() }})</h3>
                    </div>
                    <div class="card-body p-0">
                        @if($approved->isEmpty())
                            <div class="text-center py-4 text-muted small">Chưa có thành viên nào được duyệt.</div>
                        @else
                        <table class="table table-sm mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>User</th>
                                    <th width="100">Vai trò</th>
                                    <th width="120">Duyệt bởi</th>
                                    <th width="100">Thời gian</th>
                                    <th width="80" class="text-center">Xoá</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($approved->sortBy('role') as $m)
                                <tr>
                                    <td>
                                        <strong>{{ $m->user->username ?? '?' }}</strong>
                                        <span class="small text-muted ml-1">{{ $m->user->email ?? '' }}</span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $m->role === 'leader' ? 'badge-primary' : 'badge-secondary' }}">
                                            {{ $m->roleLabel() }}
                                        </span>
                                    </td>
                                    <td class="small text-muted">{{ optional($m->approver)->username ?? 'system' }}</td>
                                    <td class="small text-muted">{{ optional($m->approved_at)->format('d/m/Y') ?? '—' }}</td>
                                    <td class="text-center">
                                        @if($m->role !== 'leader')
                                        <form method="POST" action="{{ route('admin.teams.members.remove', [$team->id, $m->id]) }}"
                                              onsubmit="return confirm('Xoá {{ $m->user->username ?? '' }} khỏi nhóm?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-xs btn-outline-danger"><i class="fas fa-user-minus"></i></button>
                                        </form>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @endif
                    </div>
                </div>

                {{-- Rejected --}}
                @php $rejected = $team->members->where('status', 'rejected'); @endphp
                @if($rejected->count())
                <div class="card mt-3">
                    <div class="card-header collapsed" data-toggle="collapse" data-target="#rejected-list" style="cursor:pointer">
                        <h3 class="card-title text-muted"><i class="fas fa-ban"></i> Đã từ chối ({{ $rejected->count() }})</h3>
                    </div>
                    <div class="collapse" id="rejected-list">
                        <table class="table table-sm mb-0">
                            <thead class="thead-light"><tr><th>User</th><th>Lý do</th><th>Thời gian</th></tr></thead>
                            <tbody>
                                @foreach($rejected as $m)
                                <tr class="text-muted">
                                    <td class="small">{{ $m->user->username ?? '?' }}</td>
                                    <td class="small">{{ $m->note ?? '—' }}</td>
                                    <td class="small">{{ $m->updated_at->format('d/m/Y') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function showReject(id) {
    document.getElementById('reject-form-' + id).classList.toggle('d-none');
}
</script>
@endsection
