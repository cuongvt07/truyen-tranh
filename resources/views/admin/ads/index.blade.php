@extends('layout.admin')

@section('template_title', 'Quảng cáo')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center my-3">
        <h2 class="m-0"><i class="fa-solid fa-rectangle-ad mr-2"></i>Quản lý quảng cáo</h2>
        <a href="{{ route('admin.ads.create') }}" class="btn btn-primary">
            <i class="fa fa-plus mr-1"></i> Thêm quảng cáo
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0 align-middle">
                <thead class="thead-light">
                    <tr>
                        <th style="width:60px">Ảnh</th>
                        <th>Tên</th>
                        <th>Dạng chạy</th>
                        <th>Trang</th>
                        <th>Tần suất</th>
                        <th style="width:70px">Ưu tiên</th>
                        <th style="width:90px">Trạng thái</th>
                        <th style="width:160px" class="text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ads as $ad)
                        <tr>
                            <td>
                                @if($ad->image)
                                    <img src="{{ $ad->image }}" alt="" style="width:48px;height:48px;object-fit:cover;border-radius:4px">
                                @else
                                    <span class="text-muted"><i class="fa fa-image"></i></span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $ad->name }}</strong>
                                @if($ad->link)
                                    <br><small class="text-muted">{{ \Illuminate\Support\Str::limit($ad->link, 40) }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-info">{{ \App\Models\Ad::MODES[$ad->display_mode] ?? $ad->display_mode }}</span>
                                @if($ad->display_mode === 'banner' && $ad->placement)
                                    <br><small class="text-muted">{{ \App\Models\Ad::PLACEMENTS[$ad->placement] ?? $ad->placement }}</small>
                                @endif
                            </td>
                            <td>
                                @foreach(($ad->pages ?? []) as $p)
                                    <span class="badge badge-secondary">{{ \App\Models\Ad::PAGES[$p] ?? $p }}</span>
                                @endforeach
                            </td>
                            <td><small>{{ \App\Models\Ad::FREQUENCIES[$ad->frequency] ?? $ad->frequency }}@if($ad->frequency === 'every_n_views') ({{ $ad->frequency_value }})@endif</small></td>
                            <td>{{ $ad->priority }}</td>
                            <td>
                                <form action="{{ route('admin.ads.toggle', $ad) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm {{ $ad->is_active ? 'btn-success' : 'btn-outline-secondary' }}">
                                        {{ $ad->is_active ? 'Bật' : 'Tắt' }}
                                    </button>
                                </form>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.ads.edit', $ad) }}" class="btn btn-sm btn-outline-primary"><i class="fa fa-edit"></i></a>
                                <form action="{{ route('admin.ads.destroy', $ad) }}" method="POST" class="d-inline" onsubmit="return confirm('Xoá quảng cáo này?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">Chưa có quảng cáo nào. Bấm "Thêm quảng cáo" để tạo mới.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
