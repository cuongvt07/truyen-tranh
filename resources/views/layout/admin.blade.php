<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        @if (trim($__env->yieldContent('template_title')))
            @yield('template_title') |
        @endif {{ config('app.name', 'Laravel') }}
    </title>
    <link rel="icon" href="{{ setting('favicon_file') ? asset('storage/' . setting('favicon_file')) : asset('static/favicon.ico') }}">

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Tempusdominus Bootstrap 4 -->
    <link rel="stylesheet" href="/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
    <!-- iCheck -->
    <link rel="stylesheet" href="/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <!-- JQVMap -->
    <link rel="stylesheet" href="/plugins/jqvmap/jqvmap.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="/dist/css/adminlte.min.css">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
    <!-- Daterange picker -->
    <link rel="stylesheet" href="/plugins/daterangepicker/daterangepicker.css">
    <!-- summernote -->
    <link rel="stylesheet" href="/plugins/summernote/summernote-bs4.min.css">

    <link rel="stylesheet" href="/dist/css/custom.css">

    {{-- Theme nâng cấp (navy + Be Vietnam Pro) — đặt SAU CSS gốc để override --}}
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin-theme.css') }}?v=1">
    <style>
        /* Badge đếm: hình tròn, nền đỏ, chữ trắng */
        .badge-count {
            background-color: #dc3545 !important;
            color: #fff !important;
            border-radius: 50% !important;
            width: 20px;
            height: 20px;
            min-width: 20px;
            padding: 0 !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
            line-height: 1;
            box-sizing: border-box;
        }
        /* Số ≥3 chữ số (vd 99+) thì giãn thành viên thuốc cho khỏi tràn */
        .badge-count.badge-count--wide { width: auto; border-radius: 999px !important; padding: 0 6px !important; }
        /* Khi nằm trong sidebar (.right đẩy sang phải) vẫn căn giữa dọc */
        .nav-sidebar .badge-count.right { top: 50%; transform: translateY(-50%); }
    </style>
    @stack('styles')
</head>

<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    @php
        $isAdminUser = (bool) optional($currentUser ?? null)->is_admin;
        $navPendingArticles = $isAdminUser ? \App\Models\Article::withoutGlobalScope(\App\Scopes\ApprovedArticleScope::class)->where('status', \App\Enums\ArticleStatus::PENDING->value)->count() : 0;
        $navOpenReports = $isAdminUser ? \App\Models\CommentReport::where('resolved', false)->count() : 0;
        $navOpenChapterReports = $isAdminUser ? \App\Models\ChapterReport::where('resolved', false)->count() : 0;
        $navPendingTotal = $navPendingArticles + $navOpenReports;
    @endphp

    <!-- Preloader -->
    <!-- <div id="preloader" class="preloader">
        <div class="spinner"></div>
    </div> -->

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i
                        class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('home.index') }}" role="button">Về trang khách</a>
            </li>

        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            @if($isAdminUser)
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#" title="Cần xử lý">
                    <i class="far fa-bell"></i>
                    @if($navPendingTotal)
                        <span class="badge badge-count navbar-badge {{ $navPendingTotal > 99 ? 'badge-count--wide' : '' }}">{{ $navPendingTotal > 99 ? '99+' : $navPendingTotal }}</span>
                    @endif
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <span class="dropdown-item dropdown-header">{{ $navPendingTotal }} mục cần xử lý</span>
                    <div class="dropdown-divider"></div>
                    <a href="{{ route('admin.articles.index', ['status' => 0]) }}" class="dropdown-item">
                        <i class="fas fa-newspaper mr-2 text-warning"></i> Truyện chờ duyệt
                        <span class="float-right badge badge-warning badge-pill">{{ $navPendingArticles }}</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="{{ route('admin.comment_reports.index') }}" class="dropdown-item">
                        <i class="fas fa-flag mr-2 text-danger"></i> Báo cáo bình luận
                        <span class="float-right badge badge-danger badge-pill">{{ $navOpenReports }}</span>
                    </a>
                </div>
            </li>
            @endif
            @if(config('locales.switchable', true))
                @php $curLocale = app()->getLocale(); $adminLocales = config('locales.supported', []); @endphp
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#" title="Language / Ngôn ngữ">
                        {{ $adminLocales[$curLocale]['flag'] ?? '🌐' }}
                        <span class="d-none d-md-inline">{{ $adminLocales[$curLocale]['name'] ?? strtoupper($curLocale) }}</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        @foreach($adminLocales as $code => $loc)
                            <a href="{{ route('locale.switch', $code) }}" class="dropdown-item {{ $curLocale === $code ? 'active' : '' }}">
                                {{ $loc['flag'] ?? '' }} {{ $loc['name'] ?? strtoupper($code) }}
                            </a>
                        @endforeach
                    </div>
                </li>
            @endif
            <li class="nav-item">
                <a class="nav-link" href="{{ route('users.change_password') }}"
                   role="button" title="Đổi mật khẩu">
                    <i class="fa-solid fa-key"></i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-widget="fullscreen" href="#" role="button"
                   title="Hiển thị toàn màn hình">
                    <i class="fas fa-expand-arrows-alt"></i>
                </a>
            </li>
            <li class="nav-item">
                <form action="{{ route('logout') }}" method="post">
                    @csrf
                    <button class="btn btn-link nav-link" role="button" title="Đăng xuất">
                        <i class="fa fa-sign-out" aria-hidden="true"></i>
                    </button>
                </form>
            </li>
        </ul>
    </nav>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="/" class="brand-link">
            <img src="/dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3"
                 style="opacity: .8">
            <span class="brand-text font-weight-light">{{ config('app.name', 'Laravel') }}</span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar user panel (optional) -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <img src="{{ $currentUser->avatar }}" class="img-circle elevation-2"
                         alt="{{ $currentUser->username }}">
                </div>
                <div class="info">
                    <a href="{{ route('users.show', $currentUser->id) }}"
                       class="d-block">{!! $currentUser->renderUserName() !!}</a>
                </div>
            </div>

            <!-- SidebarSearch Form -->
            <div class="form-inline">
                <div class="input-group" data-widget="sidebar-search">
                    <input class="form-control form-control-sidebar" type="search"
                           placeholder="Nhập nội dung tìm kiếm" aria-label="Search">
                    <div class="input-group-append">
                        <button class="btn btn-sidebar">
                            <i class="fas fa-search fa-fw"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                    data-accordion="false">
                    {{-- ===== TỔNG QUAN ===== --}}
                    @if($currentUser->is_poster || $currentUser->is_admin)
                        <li class="nav-header">TỔNG QUAN</li>
                        <li class="nav-item">
                            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ set_active('admin.dashboard') }}">
                                <i class="nav-icon fas fa-tachometer-alt"></i><p>Trang tổng quan</p>
                            </a>
                        </li>
                    @endif

                    {{-- ===== QUẢN LÝ NỘI DUNG ===== --}}
                    @if($currentUser->is_poster || $currentUser->is_admin)
                        @php
                            $openContent = request()->routeIs('admin.articles.*','admin.chapters.*','admin.characters.*','admin.teams.*','admin.collections.*','admin.authors.*','admin.genres.*','admin.tags.*','admin.comments.*','admin.comment_reports.*','admin.chapter_reports.*');
                            $pendingArticles = $navPendingArticles ?? 0;
                            $openReports = $navOpenReports ?? 0;
                        @endphp
                        <li class="nav-item has-treeview {{ $openContent ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ $openContent ? 'active' : '' }}">
                                <i class="nav-icon fa-solid fa-folder-open"></i>
                                <p>Quản lý nội dung <i class="right fas fa-angle-left"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('admin.articles.index') }}" class="nav-link {{ set_active('admin.articles.*') }}">
                                        <i class="nav-icon fa-solid fa-newspaper"></i>
                                        <p>Truyện @if($pendingArticles)<span class="badge badge-count right {{ $pendingArticles > 99 ? 'badge-count--wide' : '' }}">{{ $pendingArticles > 99 ? '99+' : $pendingArticles }}</span>@endif</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.chapters.all') }}" class="nav-link {{ set_active('admin.chapters.all') }}">
                                        <i class="nav-icon fa-solid fa-list-ol"></i><p>Chương</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.characters.index') }}" class="nav-link {{ set_active('admin.characters.*') }}">
                                        <i class="nav-icon fa-solid fa-user-pen"></i><p>Nhân vật</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.teams.index') }}" class="nav-link {{ set_active('admin.teams.*') }}">
                                        <i class="nav-icon fa-solid fa-user-group"></i><p>Nhóm dịch</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.collections.index') }}" class="nav-link {{ set_active('admin.collections.*') }}">
                                        <i class="nav-icon fa-solid fa-layer-group"></i><p>Bộ sưu tập</p>
                                    </a>
                                </li>
                                @if($currentUser->is_admin)
                                <li class="nav-item">
                                    <a href="{{ route('admin.authors.index') }}" class="nav-link {{ set_active('admin.authors.*') }}">
                                        <i class="nav-icon fa-solid fa-pen"></i><p>Tác giả</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.genres.index') }}" class="nav-link {{ set_active('admin.genres.*') }}">
                                        <i class="nav-icon fa-solid fa-bars"></i><p>Thể loại</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.tags.index') }}" class="nav-link {{ set_active('admin.tags.*') }}">
                                        <i class="nav-icon fa-solid fa-tags"></i><p>Tags</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.comments.index') }}" class="nav-link {{ set_active('admin.comments.*') }}">
                                        <i class="nav-icon fa-solid fa-comments"></i><p>Bình luận</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.comment_reports.index') }}" class="nav-link {{ set_active('admin.comment_reports.*') }}">
                                        <i class="nav-icon fa-solid fa-flag"></i>
                                        <p>Báo cáo bình luận @if($openReports)<span class="badge badge-count right {{ $openReports > 99 ? 'badge-count--wide' : '' }}">{{ $openReports > 99 ? '99+' : $openReports }}</span>@endif</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.chapter_reports.index') }}" class="nav-link {{ set_active('admin.chapter_reports.*') }}">
                                        <i class="nav-icon fa-solid fa-triangle-exclamation"></i>
                                        <p>Báo cáo lỗi chương @if(($navOpenChapterReports ?? 0))<span class="badge badge-count right {{ $navOpenChapterReports > 99 ? 'badge-count--wide' : '' }}">{{ $navOpenChapterReports > 99 ? '99+' : $navOpenChapterReports }}</span>@endif</p>
                                    </a>
                                </li>
                                @endif
                            </ul>
                        </li>
                    @endif

                    {{-- ===== CREDIT & THANH TOÁN ===== --}}
                    @if($currentUser->is_admin)
                        @php $openCredit = request()->routeIs('admin.credit-packages.*','admin.transactions.*','admin.vips.*','admin.payment_settings.*'); @endphp
                        <li class="nav-item has-treeview {{ $openCredit ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ $openCredit ? 'active' : '' }}">
                                <i class="nav-icon fa-solid fa-coins"></i>
                                <p>Credit &amp; VIP <i class="right fas fa-angle-left"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('admin.credit-packages.index') }}" class="nav-link {{ set_active('admin.credit-packages.*') }}">
                                        <i class="nav-icon fa-solid fa-box-open"></i><p>Gói Credit</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.transactions.index') }}" class="nav-link {{ set_active('admin.transactions.*') }}">
                                        <i class="nav-icon fa-solid fa-receipt"></i><p>Giao dịch</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.vips.index') }}" class="nav-link {{ set_active('admin.vips.*') }}">
                                        <i class="nav-icon fa-solid fa-crown"></i><p>Tài khoản VIP</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.payment_settings.index') }}" class="nav-link {{ set_active('admin.payment_settings.*') }}">
                                        <i class="nav-icon fa-solid fa-key"></i><p>Cấu hình thanh toán</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif

                    {{-- ===== TÀI KHOẢN ===== --}}
                    @if($currentUser->is_admin)
                        @php $openAccount = request()->routeIs('admin.users.*'); @endphp
                        <li class="nav-item has-treeview {{ $openAccount ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ $openAccount ? 'active' : '' }}">
                                <i class="nav-icon fa-solid fa-users-gear"></i>
                                <p>Tài khoản <i class="right fas fa-angle-left"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('admin.users.index') }}" class="nav-link {{ set_active('admin.users.index') }}">
                                        <i class="nav-icon fa-solid fa-users"></i><p>Tất cả tài khoản</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.users.poster') }}" class="nav-link {{ set_active('admin.users.poster') }}">
                                        <i class="nav-icon fa-solid fa-user-edit"></i><p>Người đăng bài</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.users.admin') }}" class="nav-link {{ set_active('admin.users.admin') }}">
                                        <i class="nav-icon fa-solid fa-user-shield"></i><p>Quản trị viên</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.users.banned') }}" class="nav-link {{ set_active('admin.users.banned') }}">
                                        <i class="nav-icon fa-solid fa-user-slash"></i><p>Tài khoản bị cấm</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif

                    {{-- ===== FORUM MODULE ===== --}}
                    @if($currentUser->is_admin)
                        @php
                            $openForum = request()->routeIs('admin.forum.*');
                            $forumPendingPosts = \App\Models\ForumPost::where('status', 'pending')->count();
                        @endphp
                        <li class="nav-item has-treeview {{ $openForum ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ $openForum ? 'active' : '' }}">
                                <i class="nav-icon fa-solid fa-comments"></i>
                                <p>Forum <i class="right fas fa-angle-left"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('admin.forum.categories.index') }}" class="nav-link {{ set_active('admin.forum.categories.*') }}">
                                        <i class="nav-icon fa-solid fa-folder"></i><p>Categories</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.forum.posts.index') }}" class="nav-link {{ set_active('admin.forum.posts.*') }}">
                                        <i class="nav-icon fa-solid fa-file-lines"></i>
                                        <p>Posts @if($forumPendingPosts)<span class="badge badge-count right {{ $forumPendingPosts > 99 ? 'badge-count--wide' : '' }}">{{ $forumPendingPosts > 99 ? '99+' : $forumPendingPosts }}</span>@endif</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.forum.comments.index') }}" class="nav-link {{ set_active('admin.forum.comments.*') }}">
                                        <i class="nav-icon fa-solid fa-comment"></i><p>Comments</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.forum.settings.index') }}" class="nav-link {{ set_active('admin.forum.settings.*') }}">
                                        <i class="nav-icon fa-solid fa-gear"></i><p>Settings</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif

                    {{-- ===== FAQ MODULE ===== --}}
                    @if($currentUser->is_admin)
                        @php $openFaq = request()->routeIs('admin.faq.*'); @endphp
                        <li class="nav-item has-treeview {{ $openFaq ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ $openFaq ? 'active' : '' }}">
                                <i class="nav-icon fa-solid fa-circle-question"></i>
                                <p>FAQ <i class="right fas fa-angle-left"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('admin.faq.categories.index') }}" class="nav-link {{ set_active('admin.faq.categories.*') }}">
                                        <i class="nav-icon fa-solid fa-folder"></i><p>Categories</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.faq.articles.index') }}" class="nav-link {{ set_active('admin.faq.articles.*') }}">
                                        <i class="nav-icon fa-solid fa-newspaper"></i><p>Articles</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.faq.comments.index') }}" class="nav-link {{ set_active('admin.faq.comments.*') }}">
                                        <i class="nav-icon fa-solid fa-comment"></i><p>Comments</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.faq.settings.index') }}" class="nav-link {{ set_active('admin.faq.settings.*') }}">
                                        <i class="nav-icon fa-solid fa-gear"></i><p>Settings</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif

                    {{-- ===== SEO & MARKETING ===== --}}
                    @if($currentUser->is_admin)
                        @php $openSeo = request()->routeIs('admin.seo.*'); @endphp
                        <li class="nav-item has-treeview {{ $openSeo ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ $openSeo ? 'active' : '' }}">
                                <i class="nav-icon fa-solid fa-bullhorn"></i>
                                <p>SEO &amp; Marketing <i class="right fas fa-angle-left"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('admin.seo.settings') }}" class="nav-link {{ set_active('admin.seo.*') }}">
                                        <i class="nav-icon fa-solid fa-magnifying-glass-chart"></i><p>SEO</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ url('/sitemap.xml') }}" target="_blank" class="nav-link">
                                        <i class="nav-icon fa-solid fa-sitemap"></i><p>Sitemap</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif

                    {{-- ===== CÀI ĐẶT ===== --}}
                    @if($currentUser->is_admin)
                        @php $openSettings = request()->routeIs('admin.settings.*','admin.static-pages.*','admin.menus.*','admin.ads.*'); @endphp
                        <li class="nav-item has-treeview {{ $openSettings ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ $openSettings ? 'active' : '' }}">
                                <i class="nav-icon fa-solid fa-gear"></i>
                                <p>Cài đặt <i class="right fas fa-angle-left"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('admin.settings.index') }}" class="nav-link {{ set_active('admin.settings.*') }}">
                                        <i class="nav-icon fa-solid fa-sliders"></i><p>Cấu hình chung</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.static-pages.index') }}" class="nav-link {{ set_active('admin.static-pages.*') }}">
                                        <i class="nav-icon fa-solid fa-file-lines"></i><p>Cấu hình trang</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.menus.index') }}" class="nav-link {{ set_active('admin.menus.*') }}">
                                        <i class="nav-icon fa-solid fa-bars"></i><p>Menu điều hướng</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.ads.index') }}" class="nav-link {{ set_active('admin.ads.*') }}">
                                        <i class="nav-icon fa-solid fa-rectangle-ad"></i><p>Quảng cáo</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif
                </ul>
            </nav>
            <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">
                            @if (trim($__env->yieldContent('template_title')))
                                @yield('template_title')
                            @endif
                        </h1>
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Home</a></li>
                            <li class="breadcrumb-item active">
                                @if (trim($__env->yieldContent('template_title')))
                                    @yield('template_title')
                                @endif
                            </li>
                        </ol>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                @yield('content')
            </div><!-- /.container-fluid -->
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->
    <!-- <footer class="main-footer">
        <strong>Copyright &copy; 2014-2021 <a href="https://adminlte.io">AdminLTE.io</a>.</strong>
        All rights reserved.
        <div class="float-right d-none d-sm-inline-block">
            <b>Version</b> 3.2.0
        </div>
    </footer> -->

    <!-- Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" role="dialog"
         aria-labelledby="changePasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changePasswordModalLabel">Đổi mật khẩu</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Your password change form goes here -->
                    <form method="POST" action="{{ route('admin.dashboard') }}">
                        @csrf
                        <div class="form-group">
                            <label for="new_password">Mật khẩu mới</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Thay đổi</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Control Sidebar -->
    <aside class="control-sidebar control-sidebar-dark">
        <!-- Control sidebar content goes here -->
    </aside>
    <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="/plugins/jquery/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="/plugins/jquery-ui/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
    $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->
<script src="/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- ChartJS -->
<script src="/plugins/chart.js/Chart.min.js"></script>
<!-- Sparkline -->
<script src="/plugins/sparklines/sparkline.js"></script>
<!-- JQVMap -->
<script src="/plugins/jqvmap/jquery.vmap.min.js"></script>
<script src="/plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
<!-- jQuery Knob Chart -->
<script src="/plugins/jquery-knob/jquery.knob.min.js"></script>
<!-- daterangepicker -->
<script src="/plugins/moment/moment.min.js"></script>
<script src="/plugins/daterangepicker/daterangepicker.js"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<!-- Summernote -->
<script src="/plugins/summernote/summernote-bs4.min.js"></script>
<!-- overlayScrollbars -->
<script src="/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<!-- AdminLTE App -->
<script src="/dist/js/adminlte.js"></script>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<script src="/dist/js/pages/dashboard.js"></script>
<script src="/dist/js/custom.js"></script>
{{-- UI enhancement (sidebar memory, lazy-load, confirm delete, cover preview) --}}
<script src="{{ asset('js/admin-ui.js') }}?v=1"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.5/xlsx.full.min.js"></script>
<script !src="">
    function debounce(func, wait, immediate) {
        let timeout;
        return function() {
            let context = this, args = arguments;
            let later = function() {
                timeout = null;
                if (!immediate) func.apply(context, args);
            };
            let callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func.apply(context, args);
        };
    }
</script>
@yield('ArticleScripts');
<script>
    function logout() {
        event.preventDefault();
        if (confirm('Bạn có muốn đăng xuất không ?')) {
            document.getElementById('logout-form').submit();
        }
    }
</script>
<script>
    $(document).ready(function () {
        $('.formDelete').each(function (i, el) {

            $(el).find('.btnDelete').on('click', function (event) {
                event.preventDefault();
                if (confirm("Bạn có chắc chắn muốn xoá không?")) {
                    $(el).submit();
                }
            });
        });
        $('.formUnban').each(function (i, el) {

            $(el).find('.btnUnban').on('click', function (event) {
                event.preventDefault();
                if (confirm("Bạn có chắc chắn muốn gỡ lệnh cấm cho người dùng này không?")) {
                    $(el).submit();
                }
            });
        });
        $('.formSetCompleted').each(function (i, el) {

            $(el).find('.btnSetCompleted').on('click', function (event) {
                event.preventDefault();
                if (confirm("Bạn có chắc chắn muốn đặt bài viết thành đã hoàn thành?")) {
                    $(el).submit();
                }
            });
            $(el).find('.btnSetNotCompleted').on('click', function (event) {
                event.preventDefault();
                if (confirm("Bạn có chắc chắn muốn đặt bài viết thành chưa hoàn thành?")) {
                    $(el).submit();
                }
            });
        });
        $('.formApprove').each(function (i, el) {

            $(el).find('.btnApprove').on('click', function (event) {
                event.preventDefault();
                if (confirm("Bạn có chắc chắn duyệt bài này không?")) {
                    $(el).submit();
                }
            });
            $(el).find('.btnVisible').on('click', function (event) {
                event.preventDefault();
                if (confirm("Bạn có chắc chắn hiển thị bài này không?")) {
                    $(el).submit();
                }
            });
        });
        $('.formHidden').each(function (i, el) {

            $(el).find('.btnHidden').on('click', function (event) {
                event.preventDefault();
                if (confirm("Bạn có chắc chắn muốn ẩn bài viết này không?")) {
                    $(el).submit();
                }
            });
        });
    });
</script>

@stack('scripts')

</body>

</html>
