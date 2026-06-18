@extends('layout.novelight')
@section('template_title', 'Thành viên — ' . $team->name)

@section('content')
<div class="container">
    <h1 class="page-title">Thành viên — {{ $team->name }}</h1>

    <div class="flex-content">
        <div class="main block">
            @if(session('success'))<div style="background:#1e3a1e;border:1px solid #2e5e2e;padding:10px 14px;border-radius:6px;margin-bottom:14px;color:#9f9">{{ session('success') }}</div>@endif
            @if(session('error'))<div style="background:#3a1010;border:1px solid #7a2020;padding:10px 14px;border-radius:6px;margin-bottom:14px;color:#f88">{{ session('error') }}</div>@endif

            {{-- Add member request form --}}
            <div class="section" style="margin-bottom:24px">
                <h2 class="section-title"><i class="fa fa-user-plus"></i> Yêu cầu thêm thành viên</h2>
                <form method="POST" action="{{ route('teams.members.request', $team->id) }}" style="display:flex;gap:10px;flex-wrap:wrap;margin-top:10px">
                    @csrf
                    <input type="text" name="username" placeholder="@username" required
                           style="flex:1;min-width:180px;background:var(--input-bg,#1a1a2e);border:1px solid var(--border,#2a2a3e);border-radius:6px;padding:8px 12px;color:inherit">
                    <button type="submit" class="btn">Gửi yêu cầu</button>
                </form>
                <p style="font-size:12px;color:var(--meta-color);margin-top:8px">
                    <i class="fa fa-info-circle"></i> Yêu cầu sẽ được admin hệ thống xét duyệt. Sau khi duyệt user sẽ xuất hiện trong danh sách.
                </p>
            </div>

            <div class="member-settings">
                <div class="member-sections">
                    {{-- Pending --}}
                    @php $pending = $team->members->where('status','pending'); @endphp
                    @if($pending->count())
                    <div class="section" style="margin-bottom:20px">
                        <h2 class="section-title" style="color:#f5a623"><i class="fa fa-clock"></i> Chờ duyệt ({{ $pending->count() }})</h2>
                        <div class="member-list">
                            @foreach($pending as $m)
                            <div class="member-row pending">
                                <img src="{{ optional($m->user)->photo ?: asset('static/core/images/no_cover.webp') }}"
                                     class="member-avatar" alt="">
                                <div class="member-info">
                                    <div class="nickname">@{{ optional($m->user)->username ?? '?' }}</div>
                                    <div class="role-label" style="color:#f5a623">Chờ admin duyệt</div>
                                </div>
                                <form method="POST" action="{{ route('teams.members.remove', [$team->id, $m->id]) }}"
                                      onsubmit="return confirm('Huỷ yêu cầu này?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-icon" title="Huỷ yêu cầu"><i class="fa fa-times"></i></button>
                                </form>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Approved --}}
                    @php $approved = $team->members->where('status','approved'); @endphp
                    <div class="section">
                        <h2 class="section-title"><i class="fa fa-users"></i> Thành viên ({{ $approved->count() }})</h2>
                        <div class="member-list" style="margin-top:10px">
                            @forelse($approved->sortBy(fn($m) => array_search($m->role, ['leader','admin','editor','member'])) as $m)
                            <div class="member-row" onclick="selectMember({{ $m->id }}, '{{ optional($m->user)->username }}', '{{ $m->role }}')" style="cursor:pointer" id="mr-{{ $m->id }}">
                                <img src="{{ optional($m->user)->photo ?: asset('static/core/images/no_cover.webp') }}"
                                     class="member-avatar" alt="">
                                <div class="member-info">
                                    <div class="nickname">@{{ optional($m->user)->username ?? '?' }}</div>
                                    <div class="role-label">{{ \App\Models\TeamMember::ROLES[$m->role] ?? $m->role }}</div>
                                </div>
                                @if($m->role !== 'leader')
                                    <i class="fa fa-chevron-right" style="color:var(--meta-color);font-size:12px"></i>
                                @endif
                            </div>
                            @empty
                            <div style="color:var(--meta-color);font-size:13px;padding:12px 0">Chưa có thành viên nào.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Right panel --}}
                <div class="member-settings__info" id="member-panel" style="display:none">
                    <h2 class="section-title" id="panel-title">Chỉnh sửa thành viên</h2>
                    <form method="POST" id="member-form">
                        @csrf @method('PATCH')
                        <div class="form-container">
                            <div style="margin-bottom:14px">
                                <label style="font-size:13px;color:var(--meta-color);display:block;margin-bottom:6px">Vai trò</label>
                                <select name="role" id="panel-role" style="width:100%;background:var(--input-bg,#1a1a2e);border:1px solid var(--border,#2a2a3e);border-radius:6px;padding:8px 10px;color:inherit">
                                    <option value="admin">Admin</option>
                                    <option value="editor">Biên tập</option>
                                    <option value="member">Thành viên</option>
                                </select>
                            </div>
                        </div>
                        <div class="btns" style="display:flex;gap:8px;margin-top:14px">
                            <button id="save-member" type="submit" class="btn">Lưu</button>
                            <button id="remove-member" type="button" class="btn btn-invincible" onclick="confirmRemove()">Xoá</button>
                        </div>
                    </form>
                    <form method="POST" id="remove-form" style="display:none">
                        @csrf @method('DELETE')
                    </form>
                </div>
            </div>

            <div class="meta-color" style="font-size:12px;margin-top:20px">
                * Trưởng nhóm không thể bị xoá khỏi nhóm qua trang này.
            </div>
        </div>

        @include('client.community._team_sidebar')
    </div>
</div>

<style>
.flex-content { display:flex; gap:20px; align-items:flex-start; }
.flex-content .main { flex:1; min-width:0; }
.second-information { width:200px; flex-shrink:0; }
.btn-list { display:flex; flex-direction:column; gap:6px; padding:16px; }
.btn-list .btn, .btn-list .btn-invincible { display:block; text-align:center; }
.page-title { font-size:22px; font-weight:700; margin-bottom:20px; }
.section-title { font-size:15px; font-weight:700; margin-bottom:4px; }
.member-settings { display:flex; gap:20px; align-items:flex-start; }
.member-sections { flex:1; min-width:0; }
.member-settings__info { width:220px; flex-shrink:0; background:var(--card-bg,#13131f); border:1px solid var(--border,#2a2a3e); border-radius:10px; padding:16px; }
.member-list { display:flex; flex-direction:column; gap:2px; }
.member-row { display:flex; align-items:center; gap:10px; padding:10px 10px; border-radius:8px; border:1px solid transparent; transition:all .15s; }
.member-row:hover, .member-row.active { background:var(--card-bg,#13131f); border-color:var(--border,#2a2a3e); }
.member-row.pending { opacity:.7; }
.member-avatar { width:36px; height:36px; border-radius:50%; object-fit:cover; flex-shrink:0; }
.member-info { flex:1; min-width:0; }
.member-info .nickname { font-weight:600; font-size:13px; }
.member-info .role-label { font-size:11px; color:var(--meta-color); }
.btn-icon { background:none; border:none; color:#e84040; cursor:pointer; padding:4px 8px; border-radius:4px; }
.btn-icon:hover { background:rgba(232,64,64,.12); }
@media(max-width:640px) { .flex-content { flex-direction:column; } .second-information { width:100%; } .member-settings { flex-direction:column; } .member-settings__info { width:100%; } }
</style>

<script>
var currentId = null;
var baseUrl = '{{ url("teams/" . $team->id . "/members") }}/';

function selectMember(id, username, role) {
    // deactivate previous
    document.querySelectorAll('.member-row').forEach(r => r.classList.remove('active'));
    var row = document.getElementById('mr-' + id);
    if (row) row.classList.add('active');

    currentId = id;
    document.getElementById('panel-title').textContent = '@' + username;
    document.getElementById('panel-role').value = role;
    document.getElementById('member-form').action = baseUrl + id;
    document.getElementById('remove-form').action = baseUrl + id;
    document.getElementById('member-panel').style.display = 'block';
}

function confirmRemove() {
    if (!currentId) return;
    if (!confirm('Xoá thành viên này khỏi nhóm?')) return;
    document.getElementById('remove-form').submit();
}
</script>
@endsection
