@extends('layout.admin')

@section('template_title')
    Chỉnh sửa người dùng
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h4>Chỉnh sửa người dùng</h4>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form method="POST" action="{{ route('admin.users.update', $user->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="username" class="form-label">Tên đăng nhập</label>
                            <input type="text" name="username" id="username" class="form-control"
                                   value="{{ old('username', $user->username) }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="name" class="form-label">Tên đầy đủ</label>
                            <input type="text" name="name" id="name" class="form-control"
                                   value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" class="form-control"
                                   value="{{ old('email', $user->email) }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Mật khẩu mới (nếu đổi)</label>
                            <input type="password" name="password" id="password" class="form-control">
                            <small class="form-text text-muted">Để trống nếu không muốn đổi mật khẩu.</small>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Vai trò</label>
                            <select name="role" id="role" class="form-control" required>
                                <option value="0" @if($user->role==0) selected @endif>Người dùng</option>
                                <option value="1" @if($user->role==1) selected @endif>Người đăng bài</option>
                                <option value="2" @if($user->role==2) selected @endif>Quản trị viên</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="is_active" class="form-label">Điểm</label>
                            <input type="number" name="points" id="points" class="form-control"
                                   value="{{ old('points', $user->points) }}" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Cập nhật người dùng</button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Hủy</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
