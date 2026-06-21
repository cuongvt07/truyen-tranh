@extends('layout.novelight')

@section('template_title', __('messages.myarticle.my_stories'))

@section('content')
<div class="container">
    <header class="header-manga" style="margin-bottom:14px">
        <div class="container" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
            <h1><i class="fa fa-book"></i> {{ __('messages.myarticle.my_stories') }}</h1>
            <a href="{{ route('my-articles.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> {{ __('messages.myarticle.post_story') }}</a>
        </div>
    </header>

    @if(session('success'))
        <div style="background:#1e3a1e;border:1px solid #2e5e2e;padding:10px 14px;border-radius:6px;margin-bottom:14px;color:#9f9">{{ session('success') }}</div>
    @endif

    <div class="block" style="padding:16px">
        @forelse($articles as $article)
            <div class="my-story">
                <a href="{{ route('articles.show', $article) }}" class="my-story__poster">
                    <img src="{{ novel_poster($article) }}" alt="{{ $article->title }}">
                </a>
                <div class="my-story__info">
                    <a href="{{ route('articles.show', $article) }}" class="my-story__title">{{ $article->title }}</a>
                    <div class="meta-color" style="font-size:13px">
                        <i class="fa fa-book"></i> {{ __('messages.myarticle.chapters_count', ['count' => $article->chapters_count]) }}
                        • {{ $article->is_completed ? __('messages.myarticle.status_completed') : __('messages.myarticle.status_ongoing') }}
                        @if($article->status == \App\Enums\ArticleStatus::PENDING->value)
                            • <span style="color:#e0a020">{{ __('messages.myarticle.status_pending') }}</span>
                        @endif
                        @if($article->team)
                            • <i class="fa fa-user-friends"></i> {{ $article->team->name }}
                        @endif
                        • <i class="fa fa-eye"></i> {{ number_format($article->view) }}
                    </div>
                </div>
                <div class="my-story__actions">
                    <a href="{{ route('my-articles.create_chapter', $article->id) }}" class="btn btn-invincible" title="{{ __('messages.myarticle.add_chapter') }}"><i class="fa fa-plus"></i> {{ __('messages.myarticle.chapter') }}</a>
                    <a href="{{ route('my-articles.edit', $article->id) }}" class="btn btn-invincible" title="{{ __('messages.myarticle.edit') }}"><i class="fa fa-edit"></i></a>
                    @if($article->can_delete ?? false)
                        <form method="post" action="{{ route('my-articles.destroy', $article->id) }}" onsubmit="return confirm('{{ __('messages.myarticle.delete_story_confirm') }}')" style="display:inline">
                            @csrf @method('delete')
                            <button type="submit" class="btn btn-invincible" title="{{ __('messages.myarticle.delete') }}" style="color:#e84040"><i class="fa fa-trash"></i></button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="nothing" style="padding:40px 0;text-align:center;color:var(--meta-color)">
                <i class="fa fa-book" style="font-size:32px;opacity:.4;display:block;margin-bottom:10px"></i>
                {{ __('messages.myarticle.no_stories') }} <a href="{{ route('my-articles.create') }}">{{ __('messages.myarticle.post_now') }}</a>
            </div>
        @endforelse

        <div style="margin-top:16px">{{ $articles->links('vendor.pagination.novelight') }}</div>
    </div>
</div>

<style>
.my-story { display:flex; align-items:center; gap:14px; padding:12px 0; border-bottom:1px solid var(--border,#2a2a3e); }
.my-story__poster { width:54px; height:74px; flex-shrink:0; border-radius:5px; overflow:hidden; }
.my-story__poster img { width:100%; height:100%; object-fit:cover; }
.my-story__info { flex:1; min-width:0; }
.my-story__title { font-weight:600; display:block; margin-bottom:4px; color:inherit; text-decoration:none; }
.my-story__actions { display:flex; gap:6px; align-items:center; flex-shrink:0; }
.my-story__actions .btn { padding:6px 10px; font-size:13px; }
@media (max-width:620px){ .my-story { flex-wrap:wrap; } .my-story__actions { width:100%; } }
</style>
@endsection
