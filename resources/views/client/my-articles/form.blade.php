@extends('layout.novelight')

@section('template_title', $mode === 'create' ? __('messages.myarticle.post_story') : __('messages.myarticle.edit_story'))

@php
    $action = $mode === 'create' ? route_path('my-articles.store') : route_path('my-articles.update', $article->id);
    $selectedGenres = $article->exists ? $article->genres->pluck('id')->all() : [];
    $authorName = $article->exists ? optional($article->authors->first())->name : '';
@endphp

@section('content')
<div class="alpha-workspace alpha-writer-page">
    <div class="container">
        <section class="alpha-workspace-hero">
            <div class="alpha-workspace-hero__row">
                <div>
                    <small>Writer center</small>
                    <h1>{{ $mode === 'create' ? __('messages.myarticle.post_new_story') : __('messages.myarticle.edit_story') }}</h1>
                    <p>Fill in the story information. Submitted stories remain pending until approved.</p>
                </div>
                <a href="{{ route_path('my-articles.index') }}" class="alpha-btn"><i class="fa fa-chevron-left"></i> {{ __('messages.myarticle.cancel') }}</a>
            </div>
        </section>

        <div class="alpha-panel alpha-panel--pad" style="max-width:920px;margin:0 auto">
            @if($errors->any())
                <div class="alpha-alert alpha-alert--danger">
                    @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
                </div>
            @endif

            <form method="post" action="{{ $action }}" enctype="multipart/form-data" class="alpha-form">
                @csrf
                @if($mode === 'edit') @method('patch') @endif

                <div class="alpha-field">
                    <label>{{ __('messages.myarticle.story_name') }} <span style="color:#e84040">*</span></label>
                    <input type="text" name="title" value="{{ old('title', $article->title) }}" required>
                </div>

                <div class="alpha-form-grid alpha-form-grid--2">
                    <div class="alpha-field">
                        <label>{{ __('messages.myarticle.alt_title') }}</label>
                        <input type="text" name="alt_title" value="{{ old('alt_title', $article->alt_title) }}" placeholder="{{ __('messages.myarticle.alt_title_placeholder') }}">
                    </div>
                    <div class="alpha-field">
                        <label>{{ __('messages.myarticle.author') }}</label>
                        <input type="text" name="author_name" value="{{ old('author_name', $authorName) }}" placeholder="{{ __('messages.myarticle.author_placeholder') }}">
                    </div>
                </div>

                <div class="alpha-field">
                    <label>{{ __('messages.myarticle.description') }}</label>
                    <textarea name="description" rows="5">{{ old('description', $article->description) }}</textarea>
                </div>

                <div class="alpha-form-grid alpha-form-grid--3">
                    <div class="alpha-field">
                        <label>{{ __('messages.myarticle.illustrator') }}</label>
                        <input type="text" name="illustrator" value="{{ old('illustrator', $article->illustrator) }}" placeholder="{{ __('messages.myarticle.illustrator_placeholder') }}">
                    </div>
                    <div class="alpha-field">
                        <label>{{ __('messages.myarticle.type') }}</label>
                        <select name="novel_type">
                            <option value="0" {{ old('novel_type', $article->novel_type)==0?'selected':'' }}>{{ __('messages.myarticle.web_novel') }}</option>
                            <option value="1" {{ old('novel_type', $article->novel_type)==1?'selected':'' }}>{{ __('messages.myarticle.light_novel') }}</option>
                            <option value="2" {{ old('novel_type', $article->novel_type)==2?'selected':'' }}>{{ __('messages.myarticle.published_book') }}</option>
                        </select>
                    </div>
                    <div class="alpha-field">
                        <label>{{ __('messages.myarticle.year_of_release') }}</label>
                        <input type="number" name="year_of_release" min="1900" max="2100" value="{{ old('year_of_release', $article->year_of_release) }}">
                    </div>
                </div>

                <div class="alpha-form-grid alpha-form-grid--3">
                    <div class="alpha-field">
                        <label>{{ __('messages.myarticle.country') }}</label>
                        <select name="country">
                            <option value="">-</option>
                            @foreach($countries as $c)
                                <option value="{{ $c->id }}" {{ old('country', $article->country)==$c->id?'selected':'' }}>{{ $c->display_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="alpha-field">
                        <label>{{ __('messages.myarticle.credit_cost_label') }}</label>
                        <input type="number" name="credit_start_chapter" min="1" value="{{ old('credit_start_chapter', $article->credit_start_chapter) }}" placeholder="Start chapter">
                    </div>
                    <div class="alpha-field">
                        <label>&nbsp;</label>
                        <input type="number" name="credit_per_chapter" min="0" value="{{ old('credit_per_chapter', $article->credit_per_chapter) }}" placeholder="Credits per chapter">
                    </div>
                </div>

                <div class="alpha-field">
                    <span>{{ __('messages.myarticle.genres') }}</span>
                    <div class="alpha-check-grid">
                        @foreach($genres as $g)
                            <label class="alpha-check">
                                <input type="checkbox" name="genres[]" value="{{ $g->id }}" {{ in_array($g->id, old('genres', $selectedGenres)) ? 'checked' : '' }}>
                                {{ $g->name }}
                            </label>
                        @endforeach
                    </div>
                </div>

                @php $articleTags = $article->exists ? $article->tags->pluck('name')->implode(', ') : ''; @endphp
                <div class="alpha-field">
                    <label>{{ __('messages.myarticle.tags') }}</label>
                    <input type="text" name="tags" value="{{ old('tags', $articleTags) }}" placeholder="{{ __('messages.myarticle.tags_placeholder') }}">
                </div>

                @if(($myTeams ?? collect())->count())
                    <div class="alpha-field">
                        <label>{{ __('messages.myarticle.translation_team') }}</label>
                        <select name="team_id">
                            <option value="">{{ __('messages.myarticle.personal_post') }}</option>
                            @foreach($myTeams as $team)
                                <option value="{{ $team->id }}" {{ (string) old('team_id', $article->team_id) === (string) $team->id ? 'selected' : '' }}>
                                    {{ $team->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if(($myCharacters ?? collect())->count())
                    @php $selChars = $article->exists ? $article->characters->pluck('id')->all() : []; @endphp
                    <div class="alpha-field">
                        <span>{{ __('messages.myarticle.characters_yours') }}</span>
                        <div class="alpha-check-grid">
                            @foreach($myCharacters as $ch)
                                <label class="alpha-check">
                                    <input type="checkbox" name="characters[]" value="{{ $ch->id }}" {{ in_array($ch->id, old('characters', $selChars)) ? 'checked' : '' }}>
                                    {{ $ch->name }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="alpha-form-actions">
                    <label class="alpha-check">
                        <input type="checkbox" name="is_completed" value="1" {{ old('is_completed', $article->is_completed) ? 'checked' : '' }}>
                        {{ __('messages.myarticle.completed') }}
                    </label>
                    <label class="alpha-check">
                        <input type="checkbox" name="is_adult" value="1" {{ old('is_adult', $article->is_adult) ? 'checked' : '' }}>
                        {{ __('messages.myarticle.adult_content') }}
                    </label>
                </div>

                <div class="alpha-form-actions">
                    <button type="submit" class="alpha-btn alpha-btn--primary">
                        {{ $mode === 'create' ? __('messages.myarticle.post_story') : __('messages.myarticle.save_changes') }}
                    </button>
                    <a href="{{ route_path('my-articles.index') }}" class="alpha-btn">{{ __('messages.myarticle.cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
