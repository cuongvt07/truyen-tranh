@extends('layout.admin')

@section('template_title', $title)

@section('content')
@php
    $currentRoute = \Illuminate\Support\Facades\Route::currentRouteName() ?: 'admin.users.index';
    $isBannedPage = $currentRoute === 'admin.users.banned';
@endphp

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title">{{ $title }}</h3>
            <a href="{{ $createUserRoute }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Thêm người dùng
            </a>
        </div>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif

        <form method="GET" class="mb-3">
            <div class="row">
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <input type="text"
                               name="search"
                               class="form-control"
                               placeholder="Tìm theo username, tên, email..."
                               value="{{ $filters['search'] ?? '' }}">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-primary" title="Tìm kiếm">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>

                @if($currentRoute === 'admin.users.index')
                    <div class="col-md-2">
                        <select name="role" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="">Tất cả vai trò</option>
                            @foreach(\App\Enums\UserRole::cases() as $role)
                                <option value="{{ $role->value }}" {{ (string)($filters['role'] ?? '') === (string)$role->value ? 'selected' : '' }}>
                                    {{ $role->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if(!$isBannedPage)
                    <div class="col-md-2">
                        <select name="ban_status" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="">Tất cả trạng thái</option>
                            <option value="active" {{ ($filters['ban_status'] ?? '') === 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                            <option value="banned" {{ ($filters['ban_status'] ?? '') === 'banned' ? 'selected' : '' }}>Đang bị cấm</option>
                        </select>
                    </div>
                @endif

                <div class="col-md-2">
                    <select name="verified" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">Email bất kỳ</option>
                        <option value="yes" {{ ($filters['verified'] ?? '') === 'yes' ? 'selected' : '' }}>Đã xác thực</option>
                        <option value="no" {{ ($filters['verified'] ?? '') === 'no' ? 'selected' : '' }}>Chưa xác thực</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <select name="sort" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="id_desc" {{ ($filters['sort'] ?? 'id_desc') === 'id_desc' ? 'selected' : '' }}>Mới nhất</option>
                        <option value="id_asc" {{ ($filters['sort'] ?? '') === 'id_asc' ? 'selected' : '' }}>Cũ nhất</option>
                        <option value="username" {{ ($filters['sort'] ?? '') === 'username' ? 'selected' : '' }}>Username A-Z</option>
                        <option value="name" {{ ($filters['sort'] ?? '') === 'name' ? 'selected' : '' }}>Tên A-Z</option>
                        <option value="points" {{ ($filters['sort'] ?? '') === 'points' ? 'selected' : '' }}>Điểm cao nhất</option>
                        <option value="articles_count" {{ ($filters['sort'] ?? '') === 'articles_count' ? 'selected' : '' }}>Nhiều truyện nhất</option>
                        <option value="comments_count" {{ ($filters['sort'] ?? '') === 'comments_count' ? 'selected' : '' }}>Nhiều bình luận nhất</option>
                    </select>
                </div>

                <div class="col-md-1">
                    <a href="{{ route($currentRoute) }}" class="btn btn-sm btn-secondary btn-block" title="Đặt lại">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </div>
        </form>

        <p class="text-muted small mb-2">
            Hiển thị {{ $users->firstItem() ?? 0 }}-{{ $users->lastItem() ?? 0 }} trong tổng số {{ $users->total() }} tài khoản
        </p>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th width="60">ID</th>
                        <th width="70" class="text-center">Ảnh</th>
                        <th>Tài khoản</th>
                        <th>Email</th>
                        <th width="130" class="text-center">Vai trò</th>
                        <th width="120" class="text-center">Trạng thái</th>
                        <th width="150" class="text-center">Hoạt động</th>
                        @if($isBannedPage)
                            <th>Lệnh cấm</th>
                        @endif
                        <th width="120" class="text-center">Điểm</th>
                        <th width="120" class="text-center">Ngày tạo</th>
                        <th width="190" class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        @php
                            $banned = $user->banned;
                            $roleBadge = match ($user->role) {
                                \App\Enums\UserRole::ADMIN->value => 'badge-danger',
                                \App\Enums\UserRole::POSTER->value => 'badge-info',
                                default => 'badge-secondary',
                            };
                        @endphp
                        <tr>
                            <td class="text-muted">{{ $user->id }}</td>
                            <td class="text-center">
                                <img src="{{ asset($user->avatar ?: '/images/users/default.jpg') }}"
                                     alt="{{ $user->username }}"
                                     style="width:42px;height:42px;object-fit:cover;border-radius:4px;border:1px solid #ddd">
                            </td>
                            <td>
                                <strong class="{{ $banned ? 'text-muted' : '' }}">
                                    @if($banned)<s>{{ $user->username }}</s>@else{{ $user->username }}@endif
                                </strong>
                                <br>
                                <small class="text-muted">{{ $user->name ?: 'Chưa có tên' }}</small>
                            </td>
                            <td>
                                {{ $user->email }}
                                <br>
                                @if($user->email_verified_at)
                                    <span class="badge badge-success">Đã xác thực</span>
                                @else
                                    <span class="badge badge-warning">Chưa xác thực</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $roleBadge }}">{{ $user->role_text }}</span>
                            </td>
                            <td class="text-center">
                                @if($banned)
                                    <span class="badge badge-danger">Bị cấm</span>
                                @else
                                    <span class="badge badge-success">Hoạt động</span>
                                @endif
                                @if($user->should_re_login)
                                    <br><span class="badge badge-light">Cần đăng nhập lại</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge badge-primary" title="Truyện">{{ $user->articles_count }} truyện</span>
                                <span class="badge badge-secondary" title="Bình luận">{{ $user->comments_count }} BL</span>
                                <br>
                                <span class="badge badge-light" title="Theo dõi">{{ $user->bookmarks_count }} theo dõi</span>
                                <span class="badge badge-light" title="Chương đã mở khóa">{{ $user->chapter_unlocks_count }} mở khóa</span>
                            </td>
                            @if($isBannedPage)
                                <td>
                                    <strong>{{ $banned?->reason }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        Còn lại: {{ $banned?->remaining_days ?? 'Không xác định' }}
                                        @if($banned?->admin)
                                            | bởi {{ $banned->admin->name }}
                                        @endif
                                    </small>
                                </td>
                            @endif
                            <td class="text-center">
                                <span class="badge badge-dark">{{ number_format((int) $user->points) }}</span>
                            </td>
                            <td class="text-center">
                                <span title="{{ $user->created_at }}">{{ $user->created_at_text }}</span>
                            </td>
                            <td class="text-center">
                                <a class="btn btn-sm btn-info"
                                   href="{{ route('admin.users.edit', $user->id) }}"
                                   title="Chi tiết">
                                    <i class="fas fa-eye"></i>
                                </a>

                                @if(!$banned)
                                    <a class="btn btn-sm btn-success"
                                       href="{{ route('admin.users.edit_role', $user->id) }}"
                                       title="Sửa vai trò">
                                        <i class="fas fa-user-shield"></i>
                                    </a>
                                    @if($user->role !== \App\Enums\UserRole::ADMIN->value)
                                        <a class="btn btn-sm btn-danger"
                                           href="{{ route('admin.users.create_ban', $user->id) }}"
                                           title="Cấm tài khoản">
                                            <i class="fas fa-ban"></i>
                                        </a>
                                    @endif
                                @else
                                    <a class="btn btn-sm btn-warning"
                                       href="{{ route('admin.users.edit_ban', $user->id) }}"
                                       title="Sửa lệnh cấm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.users.unban', $user->id) }}"
                                          method="POST"
                                          class="d-inline formUnban">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-secondary btnUnban" title="Bỏ cấm">
                                            <i class="fas fa-unlock"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isBannedPage ? 11 : 10 }}" class="text-center text-muted py-4">
                                <i class="fas fa-users fa-2x mb-2"></i>
                                <p class="mb-0">Không tìm thấy tài khoản nào</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
