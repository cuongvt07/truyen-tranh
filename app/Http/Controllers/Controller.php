<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Pagination\Paginator;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function __construct()
    {
        $this->setupPaginationTheme();
    }

    protected function setupPaginationTheme(): void
    {
        if (is_route('admin.*')) {
            Paginator::defaultView('pagination::admin');
        } else {
            Paginator::defaultView('pagination::client');
        }
    }

    /**
     * Chỉ cho phép user đã từng mua gói (có lịch sử thanh toán) dùng các chức năng
     * cộng đồng (đăng truyện / tạo nhóm dịch / tạo bộ sưu tập).
     */
    protected function ensurePurchased(): void
    {
        abort_unless(
            \Illuminate\Support\Facades\Auth::check() && \Illuminate\Support\Facades\Auth::user()->hasPurchased(),
            403,
            __('messages.community.purchase_required')
        );
    }
}
