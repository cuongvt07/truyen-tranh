<div class="second-information">
    <div class="block btn-list">
        <a href="{{ route('teams.dashboard', $team->id) }}"
           class="btn {{ request()->routeIs('teams.dashboard') ? '' : 'btn-invincible' }}">
            <i class="fa fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="{{ route('teams.edit', $team->id) }}"
           class="btn {{ request()->routeIs('teams.edit') ? '' : 'btn-invincible' }}">
            <i class="fa fa-edit"></i> Cập nhật thông tin
        </a>
        <a href="{{ route('teams.members.manage', $team->id) }}"
           class="btn {{ request()->routeIs('teams.members.manage') ? '' : 'btn-invincible' }}">
            <i class="fa fa-users"></i> Thành viên
        </a>
        <hr>
        <a href="{{ route('teams.show', $team->id) }}" class="btn btn-invincible">
            <i class="fa fa-eye"></i> Trang nhóm
        </a>
    </div>
</div>
