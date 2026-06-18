<aside class="team-side">
    <a href="{{ route('teams.dashboard', $team->id) }}"
       class="team-nav-btn {{ request()->routeIs('teams.dashboard') ? 'is-active' : '' }}">
        Dashboard
    </a>
    <a href="{{ route('teams.edit', $team->id) }}"
       class="team-nav-btn {{ request()->routeIs('teams.edit') ? 'is-active' : '' }}">
        Update Information
    </a>
    <a href="{{ route('teams.members.manage', $team->id) }}"
       class="team-nav-btn {{ request()->routeIs('teams.members.manage') ? 'is-active' : '' }}">
        Members
    </a>
    <hr>
    <a href="{{ route('teams.show', $team->id) }}" class="team-nav-btn">
        Team page
    </a>
</aside>
