@extends('layout.novelight')
@section('template_title', 'Edit members - ' . $team->name)

@push('styles')
<link rel="stylesheet" href="{{ asset('static/team/css/team.css') }}">
@endpush

@section('content')
<div class="team-page">
    <h1 class="team-title">Edit members - {{ $team->name }}</h1>

    <div class="team-layout">
        <main class="team-main team-panel">
            @if(session('success'))<div class="review-alert" style="background:#317a31">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="review-alert" style="background:#a52a2a">{{ session('error') }}</div>@endif

            @if($team->isPending())
                <div class="review-alert">
                    <i class="fa fa-info-circle"></i>
                    The team is under review by administrators
                </div>
            @endif

            <div class="member-editor">
                <section class="member-editor-list">
                    <h2 class="team-section-title">Members</h2>
                    @foreach($team->members->where('status', 'approved')->sortBy(fn($m) => array_search($m->role, ['leader','admin','editor','member'])) as $m)
                        <button type="button"
                                class="member-row"
                                id="member-row-{{ $m->id }}"
                                @if($m->role !== 'leader')
                                    onclick="selectMember({{ $m->id }}, '{{ e(optional($m->user)->username ?? '?') }}', '{{ $m->role }}')"
                                @endif>
                            <span class="member-avatar">
                                <img src="{{ optional($m->user)->avatar ?: asset('static/core/images/no_cover.webp') }}" alt="">
                            </span>
                            <span>
                                <span class="member-name">{{ optional($m->user)->username ?? '?' }}</span>
                                <span class="member-role">{{ $m->role }}</span>
                            </span>
                        </button>
                    @endforeach

                    @php $pending = $team->members->where('status', 'pending'); @endphp
                    @if($pending->count())
                        <h2 class="team-section-title" style="margin-top:18px">Pending</h2>
                        @foreach($pending as $m)
                            <div class="member-row">
                                <span class="member-avatar">
                                    <img src="{{ optional($m->user)->avatar ?: asset('static/core/images/no_cover.webp') }}" alt="">
                                </span>
                                <span>
                                    <span class="member-name">{{ optional($m->user)->username ?? '?' }}</span>
                                    <span class="member-role">waiting admin review</span>
                                </span>
                            </div>
                        @endforeach
                    @endif
                </section>

                <section class="member-empty-panel" id="empty-panel">
                    You haven't selected a member yet
                </section>

                <section id="member-panel" style="display:none">
                    <h2 class="team-section-title" id="panel-title">Member</h2>
                    <form method="POST" id="member-form" class="team-form">
                        @csrf
                        @method('PATCH')
                        <div class="frow">
                            <label>Role</label>
                            <select name="role" id="panel-role">
                                <option value="admin">Admin</option>
                                <option value="editor">Editor</option>
                                <option value="member">Member</option>
                            </select>
                        </div>
                        <div class="team-form-actions">
                            <button type="submit" class="btn">Update</button>
                            <button type="button" class="btn btn-invincible" onclick="confirmRemove()">Remove</button>
                        </div>
                    </form>
                    <form method="POST" id="remove-form" style="display:none">
                        @csrf
                        @method('DELETE')
                    </form>
                </section>
            </div>

            <div class="member-note">
                * All coupons (currency) received from users for chapters go to the team creator
            </div>

            <form method="POST" action="{{ route('teams.members.request', $team->id) }}" class="team-form" style="margin-top:18px">
                @csrf
                <div class="frow">
                    <label>Request member by username</label>
                    <input type="text" name="username" placeholder="username" required>
                </div>
                <button type="submit" class="btn">Send request</button>
            </form>
        </main>

        @include('client.community._team_sidebar')
    </div>
</div>

<script>
var currentId = null;
var baseUrl = '{{ url("teams/" . $team->id . "/members") }}/';

function selectMember(id, username, role) {
    document.querySelectorAll('.member-row').forEach(function(row) {
        row.classList.remove('active');
    });
    var selected = document.getElementById('member-row-' + id);
    if (selected) selected.classList.add('active');

    currentId = id;
    document.getElementById('empty-panel').style.display = 'none';
    document.getElementById('member-panel').style.display = 'block';
    document.getElementById('panel-title').textContent = username;
    document.getElementById('panel-role').value = role;
    document.getElementById('member-form').action = baseUrl + id;
    document.getElementById('remove-form').action = baseUrl + id;
}

function confirmRemove() {
    if (!currentId || !confirm('Remove this member?')) return;
    document.getElementById('remove-form').submit();
}
</script>
@endsection
