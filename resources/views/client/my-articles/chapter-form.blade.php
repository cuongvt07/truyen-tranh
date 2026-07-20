@extends('layout.novelight')

@section('template_title', __('messages.myarticle.add_chapter_title', ['title' => $article->title]))

@section('content')
<div class="alpha-workspace alpha-writer-page">
    <div class="container">
        <section class="alpha-workspace-hero">
            <div class="alpha-workspace-hero__row">
                <div>
                    <small>Writer center</small>
                    <h1>{{ __('messages.myarticle.add_chapter') }}</h1>
                    <p>{{ $article->title }}</p>
                </div>
                <a href="{{ route_path('my-articles.index') }}" class="alpha-btn"><i class="fa fa-chevron-left"></i> {{ __('messages.myarticle.back') }}</a>
            </div>
        </section>

        <div class="alpha-panel alpha-panel--pad" style="max-width:920px;margin:0 auto">
            @if($errors->any())
                <div class="alpha-alert alpha-alert--danger">
                    @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
                </div>
            @endif

            <form method="post" action="{{ route_path('my-articles.store_chapter', $article->id) }}" class="alpha-form">
                @csrf

                <div class="alpha-form-grid alpha-form-grid--2">
                    <div class="alpha-field">
                        <label>{{ __('messages.myarticle.chapter_number') }} <span style="color:#e84040">*</span></label>
                        <input type="number" name="number" min="1" value="{{ old('number', $nextNumber) }}" required>
                    </div>
                    <div class="alpha-field">
                        <label>{{ __('messages.myarticle.chapter_title') }} <span style="color:#e84040">*</span></label>
                        <input type="text" name="title" value="{{ old('title') }}" required>
                    </div>
                </div>

                <div class="alpha-field">
                    <label>{{ __('messages.myarticle.content') }} <span style="color:#e84040">*</span></label>
                    <textarea name="content" rows="18" required>{{ old('content') }}</textarea>
                </div>

                <div class="alpha-form-grid alpha-form-grid--2">
                    <div class="alpha-field">
                        <label>{{ __('messages.myarticle.credit_cost_label') }}</label>
                        <input type="number" name="credit_cost" min="0" value="{{ old('credit_cost') }}" placeholder="{{ __('messages.myarticle.credit_cost_placeholder') }}">
                        @php $start = $article->credit_start_chapter; $perChap = $article->credit_per_chapter ?? 0; @endphp
                        <small class="meta-color">
                            @if($start)
                                {{ __('messages.myarticle.charge_from_chapter') }} <strong>{{ $start }}</strong>, {{ __('messages.myarticle.default_credit_per_chapter', ['credit' => $perChap]) }}
                            @else
                                {{ __('messages.myarticle.free_all_chapters') }}
                            @endif
                        </small>
                    </div>
                    <div class="alpha-field">
                        <label>{{ __('messages.myarticle.publish_schedule') }}</label>
                        <input type="text" name="published_at" value="{{ old('published_at') }}" placeholder="{{ __('messages.myarticle.publish_now_placeholder') }}">
                        @error('published_at')<small style="color:#e3342f">{{ $message }}</small>@enderror
                        <small class="meta-color">{{ __('messages.myarticle.publish_schedule_format') }}</small>
                    </div>
                </div>

                <div class="alpha-form-actions">
                    <button type="submit" class="alpha-btn alpha-btn--primary">{{ __('messages.myarticle.post_chapter') }}</button>
                    <a href="{{ route_path('my-articles.index') }}" class="alpha-btn">{{ __('messages.myarticle.back') }}</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
