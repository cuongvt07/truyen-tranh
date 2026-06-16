@extends('layout.admin')
@section('template_title', 'Cài đặt SEO')

@php $s = fn($k, $d='') => old($k, $settings[$k] ?? $d); @endphp

@section('content')
<div class="content"><div class="container-fluid">
    @includeWhen(session('success'), 'admin.partials.flash')

    <form method="post" action="{{ route('admin.seo.update') }}" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <div class="card card-primary card-outline">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Thông tin cơ bản</h3></div>
                    <div class="card-body">
                        <div class="form-group"><label>Tên website</label><input type="text" name="site_name" class="form-control" value="{{ $s('site_name') }}"></div>
                        <div class="form-group"><label>Dấu phân cách title</label><input type="text" name="title_separator" class="form-control" value="{{ $s('title_separator') }}" placeholder=" · "></div>
                        <div class="form-group"><label>Mô tả mặc định</label><textarea name="default_description" class="form-control" rows="2" maxlength="160">{{ $s('default_description') }}</textarea><small class="text-muted">Tối ưu 130–155 ký tự</small></div>
                        <div class="form-group"><label>Từ khoá mặc định</label><input type="text" name="default_keywords" class="form-control" value="{{ $s('default_keywords') }}"></div>
                        <div class="form-group mb-0">
                            <x-admin.image-upload name="default_og_image_file" label="Ảnh OG mặc định (1200×630)" :height="80"
                                :current="$s('default_og_image') ? (\Illuminate\Support\Str::startsWith($s('default_og_image'), 'http') ? $s('default_og_image') : asset($s('default_og_image'))) : null"
                                urlName="default_og_image" :urlValue="$s('default_og_image')"
                                hint="Tải ảnh từ máy hoặc dán URL. Upload sẽ được ưu tiên." />
                        </div>
                    </div>
                </div>

                <div class="card card-secondary card-outline">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-share-nodes mr-2"></i>Mạng xã hội</h3></div>
                    <div class="card-body">
                        <div class="form-group"><label>OG Locale</label><input type="text" name="og_locale" class="form-control" value="{{ $s('og_locale', 'vi_VN') }}"></div>
                        <div class="form-group"><label>Facebook Page URL</label><input type="text" name="facebook_page_url" class="form-control" value="{{ $s('facebook_page_url') }}"></div>
                        <div class="form-group"><label>Facebook App ID</label><input type="text" name="facebook_app_id" class="form-control" value="{{ $s('facebook_app_id') }}"></div>
                        <div class="form-group mb-0"><label>Twitter/X username</label><input type="text" name="twitter_username" class="form-control" value="{{ $s('twitter_username') }}" placeholder="@username"></div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card card-primary card-outline">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-shield-halved mr-2"></i>Xác minh tìm kiếm</h3></div>
                    <div class="card-body">
                        <div class="form-group"><label>Google Search Console</label><input type="text" name="google_site_verify" class="form-control" value="{{ $s('google_site_verify') }}" placeholder="mã verification"></div>
                        <div class="form-group mb-0"><label>Bing Webmaster</label><input type="text" name="bing_site_verify" class="form-control" value="{{ $s('bing_site_verify') }}"></div>
                    </div>
                </div>

                <div class="card card-secondary card-outline">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-chart-line mr-2"></i>Công cụ phân tích</h3></div>
                    <div class="card-body">
                        <div class="form-group"><label>Google Analytics 4 ID</label><input type="text" name="google_analytics_id" class="form-control" value="{{ $s('google_analytics_id') }}" placeholder="G-XXXXXXX"></div>
                        <div class="form-group mb-0"><label>Google Tag Manager ID</label><input type="text" name="google_tag_manager" class="form-control" value="{{ $s('google_tag_manager') }}" placeholder="GTM-XXXXX"></div>
                    </div>
                </div>

                <div class="card card-secondary card-outline">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-gears mr-2"></i>Nâng cao</h3></div>
                    <div class="card-body">
                        <div class="form-group mb-0">
                            <label>robots.txt bổ sung</label>
                            <textarea name="robots_txt_extra" class="form-control text-monospace" rows="4">{{ $s('robots_txt_extra') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="card card-primary card-outline">
                    <div class="card-body"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu cài đặt SEO</button>
                    <a href="{{ url('/robots.txt') }}" target="_blank" class="btn btn-outline-secondary ml-2"><i class="fas fa-robot"></i> Xem robots.txt</a>
                    <a href="{{ url('/sitemap.xml') }}" target="_blank" class="btn btn-outline-secondary"><i class="fas fa-sitemap"></i> Xem sitemap</a></div>
                </div>
            </div>
        </div>
    </form>
</div></div>
@endsection
