@extends('layout.novelight')

@section('template_title', $mode === 'create' ? __('messages.myarticle.post_story') : __('messages.myarticle.edit_story'))

@php
    $action = $mode === 'create' ? route('my-articles.store') : route('my-articles.update', $article->id);
    $selectedGenres = $article->exists ? $article->genres->pluck('id')->all() : [];
    $authorName = $article->exists ? optional($article->authors->first())->name : '';
@endphp

@section('content')
<div class="container">
    <header class="header-manga" style="margin-bottom:14px">
        <div class="container"><h1><i class="fa fa-{{ $mode === 'create' ? 'plus' : 'edit' }}"></i> {{ $mode === 'create' ? __('messages.myarticle.post_new_story') : __('messages.myarticle.edit_story') }}</h1></div>
    </header>

    <div class="block" style="max-width:760px;margin:0 auto;padding:24px">
        @if($errors->any())
            <div style="background:#3a1010;border:1px solid #7a2020;padding:10px 14px;border-radius:6px;margin-bottom:14px;color:#f88">
                @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
            </div>
        @endif

        <form method="post" action="{{ $action }}" enctype="multipart/form-data" class="story-form">
            @csrf
            @if($mode === 'edit') @method('patch') @endif

            <div class="frow">
                <label>{{ __('messages.myarticle.story_name') }} <span style="color:#e84040">*</span></label>
                <input type="text" name="title" value="{{ old('title', $article->title) }}" required>
            </div>

            <div class="frow">
                <label>{{ __('messages.myarticle.alt_title') }}</label>
                <input type="text" name="alt_title" value="{{ old('alt_title', $article->alt_title) }}" placeholder="{{ __('messages.myarticle.alt_title_placeholder') }}">
            </div>

            <div class="frow">
                <label>{{ __('messages.myarticle.description') }}</label>
                <textarea name="description" rows="5">{{ old('description', $article->description) }}</textarea>
            </div>

            {{-- Truyện do user gửi = chỉ TEXT (yêu cầu dịch). Không cho upload ảnh bìa/nền;
                 admin sẽ thêm ảnh khi nhận dịch. User chỉ up được avatar ở trang hồ sơ. --}}

            <div class="frow-2">
                <div class="frow">
                    <label>{{ __('messages.myarticle.author') }}</label>
                    <input type="text" name="author_name" value="{{ old('author_name', $authorName) }}" placeholder="{{ __('messages.myarticle.author_placeholder') }}">
                </div>
                <div class="frow">
                    <label>{{ __('messages.myarticle.illustrator') }}</label>
                    <input type="text" name="illustrator" value="{{ old('illustrator', $article->illustrator) }}" placeholder="{{ __('messages.myarticle.illustrator_placeholder') }}">
                </div>
            </div>

            <div class="frow-3">
                <div class="frow">
                    <label>{{ __('messages.myarticle.type') }}</label>
                    <select name="novel_type">
                        <option value="0" {{ old('novel_type', $article->novel_type)==0?'selected':'' }}>Web Novel</option>
                        <option value="1" {{ old('novel_type', $article->novel_type)==1?'selected':'' }}>Light Novel</option>
                        <option value="2" {{ old('novel_type', $article->novel_type)==2?'selected':'' }}>{{ __('messages.myarticle.published_book') }}</option>
                    </select>
                </div>
                <div class="frow">
                    <label>{{ __('messages.myarticle.country') }}</label>
                    <select name="country">
                        <option value="">—</option>
                        @foreach($countries as $c)
                            <option value="{{ $c->id }}" {{ old('country', $article->country)==$c->id?'selected':'' }}>{{ $c->display_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="frow">
                    <label>{{ __('messages.myarticle.year_of_release') }}</label>
                    <input type="number" name="year_of_release" min="1900" max="2100" value="{{ old('year_of_release', $article->year_of_release) }}">
                </div>
            </div>

            <div class="frow">
                <label>{{ __('messages.myarticle.genres') }}</label>
                <div class="genre-grid">
                    @foreach($genres as $g)
                        <label class="genre-chk">
                            <input type="checkbox" name="genres[]" value="{{ $g->id }}" {{ in_array($g->id, old('genres', $selectedGenres)) ? 'checked' : '' }}>
                            {{ $g->name }}
                        </label>
                    @endforeach
                </div>
            </div>

            @php $articleTags = $article->exists ? $article->tags->pluck('name')->implode(', ') : ''; @endphp
            <div class="frow">
                <label>Tags <span class="meta-color" style="font-weight:400">{{ __('messages.myarticle.tags_hint') }}</span></label>
                <input type="text" name="tags" value="{{ old('tags', $articleTags) }}" placeholder="{{ __('messages.myarticle.tags_placeholder') }}">
            </div>

            @if(($myCharacters ?? collect())->count())
                @php $selChars = $article->exists ? $article->characters->pluck('id')->all() : []; @endphp
                <div class="frow">
                    <label>{{ __('messages.myarticle.characters_yours') }}</label>
                    <div class="genre-grid">
                        @foreach($myCharacters as $ch)
                            <label class="genre-chk">
                                <input type="checkbox" name="characters[]" value="{{ $ch->id }}" {{ in_array($ch->id, old('characters', $selChars)) ? 'checked' : '' }}>
                                {{ $ch->name }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="frow" style="display:flex;gap:24px;flex-wrap:wrap">
                <label class="genre-chk">
                    <input type="checkbox" name="is_completed" value="1" {{ old('is_completed', $article->is_completed) ? 'checked' : '' }}>
                    {{ __('messages.myarticle.completed') }}
                </label>
                <label class="genre-chk">
                    <input type="checkbox" name="is_adult" value="1" {{ old('is_adult', $article->is_adult) ? 'checked' : '' }}>
                    {{ __('messages.myarticle.adult_content') }}
                </label>
            </div>

            <div style="display:flex;gap:10px;margin-top:8px">
                <button type="submit" class="btn btn-primary">{{ $mode === 'create' ? __('messages.myarticle.post_story') : __('messages.myarticle.save_changes') }}</button>
                <a href="{{ route('my-articles.index') }}" class="btn btn-invincible">{{ __('messages.myarticle.cancel') }}</a>
            </div>
        </form>
    </div>
</div>

<style>
.story-form .frow { margin-bottom:16px; }
.story-form .frow-2 { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
.story-form .frow-3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:14px; }
.story-form label { display:block; font-size:13px; margin-bottom:5px; color:var(--meta-color,#999); font-weight:500; }
.story-form input[type=text], .story-form input[type=number], .story-form select, .story-form textarea {
    width:100%; padding:9px 12px; border-radius:5px; border:1px solid var(--input-border-color,#2a2a3e);
    background:var(--bg,#fff); color:inherit; font-size:14px;
}
.genre-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(130px,1fr)); gap:6px; }
.genre-chk { display:flex !important; align-items:center; gap:8px; font-size:14px; color:inherit !important; font-weight:400 !important; cursor:pointer; }
@media (max-width:620px){ .story-form .frow-2, .story-form .frow-3 { grid-template-columns:1fr; } }
</style>
@endsection
