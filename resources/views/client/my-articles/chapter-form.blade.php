@extends('layout.novelight')

@section('template_title', __('messages.myarticle.add_chapter_title', ['title' => $article->title]))

@section('content')
<div class="container">
    <header class="header-manga" style="margin-bottom:14px">
        <div class="container"><h1><i class="fa fa-plus"></i> {{ __('messages.myarticle.add_chapter') }}</h1>
            <p class="meta-color" style="font-size:14px;margin-top:4px">{{ $article->title }}</p>
        </div>
    </header>

    <div class="block" style="max-width:820px;margin:0 auto;padding:24px">
        @if($errors->any())
            <div style="background:#3a1010;border:1px solid #7a2020;padding:10px 14px;border-radius:6px;margin-bottom:14px;color:#f88">
                @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
            </div>
        @endif

        <form method="post" action="{{ route('my-articles.store_chapter', $article->id) }}" class="story-form">
            @csrf
            <div class="frow-2">
                <div class="frow">
                    <label>{{ __('messages.myarticle.chapter_number') }} <span style="color:#e84040">*</span></label>
                    <input type="number" name="number" min="1" value="{{ old('number', $nextNumber) }}" required>
                </div>
                <div class="frow">
                    <label>{{ __('messages.myarticle.chapter_title') }} <span style="color:#e84040">*</span></label>
                    <input type="text" name="title" value="{{ old('title') }}" required>
                </div>
            </div>
            <div class="frow">
                <label>{{ __('messages.myarticle.content') }} <span style="color:#e84040">*</span></label>
                <textarea name="content" rows="18" required>{{ old('content') }}</textarea>
            </div>
            <div class="frow">
                <label>Credit để mở chương này <span class="meta-color" style="font-weight:400">(ghi đè cấu hình truyện)</span></label>
                <input type="number" name="credit_cost" min="0" value="{{ old('credit_cost') }}"
                       placeholder="Để trống = dùng mặc định của truyện">
                @php $start = $article->credit_start_chapter; $perChap = $article->credit_per_chapter ?? 0; @endphp
                <small class="meta-color" style="font-size:12px">
                    Truyện cấu hình:
                    @if($start)
                        thu từ chương <strong>{{ $start }}</strong>, mặc định <strong>{{ $perChap }}</strong> credit/chương.
                    @else
                        miễn phí toàn bộ.
                    @endif
                </small>
            </div>

            <div class="frow">
                <label>Lịch đăng <span class="meta-color" style="font-weight:400">(delay publish)</span></label>
                <input type="text" name="published_at" value="{{ old('published_at') }}"
                       placeholder="Để trống = đăng ngay.  VD: 2026-06-15 08:00">
                @error('published_at')<small style="color:#e3342f;font-size:12px">{{ $message }}</small>@enderror
                <small class="meta-color" style="font-size:12px">
                    Để trống = đăng ngay. Nhập thời điểm tương lai để hẹn giờ (ẩn khỏi bạn đọc tới giờ đó).
                    Định dạng <code>YYYY-MM-DD HH:MM</code> hoặc <code>DD/MM/YYYY HH:MM</code> — copy-paste từ Excel được.
                </small>
            </div>
            <div style="display:flex;gap:10px">
                <button type="submit" class="btn btn-primary">{{ __('messages.myarticle.post_chapter') }}</button>
                <a href="{{ route('my-articles.index') }}" class="btn btn-invincible">{{ __('messages.myarticle.back') }}</a>
            </div>
        </form>
    </div>
</div>

<style>
.story-form .frow { margin-bottom:16px; }
.story-form .frow-2 { display:grid; grid-template-columns:140px 1fr; gap:14px; }
.story-form label { display:block; font-size:13px; margin-bottom:5px; color:var(--meta-color,#999); font-weight:500; }
.story-form input, .story-form textarea {
    width:100%; padding:9px 12px; border-radius:5px; border:1px solid var(--input-border-color,#2a2a3e);
    background:var(--bg,#fff); color:inherit; font-size:14px;
}
.story-form textarea { line-height:1.7; font-size:15px; }
@media (max-width:620px){ .story-form .frow-2 { grid-template-columns:1fr; } }
</style>
@endsection
