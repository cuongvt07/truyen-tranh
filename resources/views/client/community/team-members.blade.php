@extends('layout.novelight')
@section('template_title', __('messages.community.edit_members_title', ['name' => $team->name]))

@push('styles')
@php $teamCssVer = file_exists(public_path('static/team/css/team.css')) ? filemtime(public_path('static/team/css/team.css')) : time(); @endphp
<link rel="stylesheet" href="{{ asset('static/team/css/team.css') }}?v={{ $teamCssVer }}">
@endpush

@section('content')
@php
    $teamRoleLabels = [
        'leader' => __('messages.community.role_leader'),
        'admin' => __('messages.community.role_admin'),
        'editor' => __('messages.community.role_editor'),
        'member' => __('messages.community.role_member'),
    ];
@endphp
<div class="team-page">
    <h1 class="team-title">{{ __('messages.community.edit_members_title', ['name' => $team->name]) }}</h1>

    <div class="team-layout">
        <main class="team-main team-panel">
            @if(session('success'))<div class="review-alert" style="background:#317a31">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="review-alert" style="background:#a52a2a">{{ session('error') }}</div>@endif

            @if($team->isPending())
                <div class="review-alert">
                    <i class="fa fa-info-circle"></i>
                    {{ __('messages.community.pending_review_notice') }}
                </div>
            @endif

            <div class="member-editor">
                <section class="member-editor-list">
                    <h2 class="team-section-title">{{ __('messages.community.members') }}</h2>
                    @foreach($team->members->where('status', 'approved')->sortBy(fn($m) => array_search($m->role, ['leader','admin','editor','member'])) as $m)
                        <button type="button"
                                class="member-row"
                                id="member-row-{{ $m->id }}"
                                @if($m->role !== 'leader')
                                    onclick="selectMember({{ $m->id }}, '{{ e(optional($m->user)->username ?? '?') }}', '{{ $m->role }}')"
                                @endif>
                            <span class="member-avatar">
                                <img src="{{ optional($m->user)->avatar ?: asset('static/core/images/no_cover.webp') }}" alt="{{ optional($m->user)->username }}">
                            </span>
                            <span>
                                <span class="member-name">{{ optional($m->user)->username ?? '?' }}</span>
                                <span class="member-role">{{ $teamRoleLabels[$m->role] ?? $m->role }}</span>
                            </span>
                        </button>
                    @endforeach

                    @php $pending = $team->members->where('status', 'pending'); @endphp
                    @if($pending->count())
                        <h2 class="team-section-title" style="margin-top:18px">{{ __('messages.community.pending') }}</h2>
                        @foreach($pending as $m)
                            <div class="member-row">
                                <span class="member-avatar">
                                    <img src="{{ optional($m->user)->avatar ?: asset('static/core/images/no_cover.webp') }}" alt="{{ optional($m->user)->username }}">
                                </span>
                                <span>
                                    <span class="member-name">{{ optional($m->user)->username ?? '?' }}</span>
                                    <span class="member-role">{{ __('messages.community.waiting_admin_review') }}</span>
                                </span>
                            </div>
                        @endforeach
                    @endif
                </section>

                <section class="member-empty-panel" id="empty-panel">
                    {{ __('messages.community.no_member_selected') }}
                </section>

                <section id="member-panel" style="display:none">
                    <h2 class="team-section-title" id="panel-title">{{ __('messages.community.member') }}</h2>
                    <form method="POST" id="member-form" class="team-form">
                        @csrf
                        @method('PATCH')
                        <div class="frow">
                            <label>{{ __('messages.community.role') }}</label>
                            <select name="role" id="panel-role">
                                <option value="admin">{{ __('messages.community.role_admin') }}</option>
                                <option value="editor">{{ __('messages.community.role_editor') }}</option>
                                <option value="member">{{ __('messages.community.role_member') }}</option>
                            </select>
                        </div>
                        <div class="team-form-actions">
                            <button type="submit" class="btn">{{ __('messages.community.update') }}</button>
                            <button type="button" class="btn btn-invincible" onclick="confirmRemove()">{{ __('messages.community.remove') }}</button>
                        </div>
                    </form>
                    <form method="POST" id="remove-form" style="display:none">
                        @csrf
                        @method('DELETE')
                    </form>
                </section>
            </div>

            <div class="member-note">
                {{ __('messages.community.member_coupon_note') }}
            </div>

            <form method="POST" action="{{ route('teams.members.request', $team->id) }}" class="team-form" style="margin-top:18px">
                @csrf
                <div class="frow">
                    <label>{{ __('messages.community.request_member_by_username') }}</label>
                    <input type="text" name="username" placeholder="{{ __('messages.community.username_placeholder') }}" required>
                </div>
                <button type="submit" class="btn">{{ __('messages.community.send_request') }}</button>
            </form>
        </main>

        @include('client.community._team_sidebar')
    </div>
</div>

<script>
var currentId = null;
var baseUrl = '{{ url("teams/" . $team->id . "/members") }}/';
var removeConfirmMessage = @json(__('messages.community.confirm_remove_member'));

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
    if (!currentId || !confirm(removeConfirmMessage)) return;
    document.getElementById('remove-form').submit();
}
</script>
@endsection
