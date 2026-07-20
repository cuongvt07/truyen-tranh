<aside class="team-side">
    <a href="{{ route_path('teams.dashboard', $team->id) }}"
       class="team-nav-btn {{ request()->routeIs('teams.dashboard') ? 'is-active' : '' }}">
        {{ __('messages.community.dashboard') }}
    </a>
    <a href="{{ route_path('teams.edit', $team->id) }}"
       class="team-nav-btn {{ request()->routeIs('teams.edit') ? 'is-active' : '' }}">
        {{ __('messages.community.update_information') }}
    </a>
    <a href="{{ route_path('teams.members.manage', $team->id) }}"
       class="team-nav-btn {{ request()->routeIs('teams.members.manage') ? 'is-active' : '' }}">
        {{ __('messages.community.members') }}
    </a>
    <hr>
    <a href="{{ route_path('teams.show', $team->id) }}" class="team-nav-btn">
        {{ __('messages.community.team_page') }}
    </a>
</aside>
