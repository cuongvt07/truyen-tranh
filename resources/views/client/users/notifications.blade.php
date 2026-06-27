@extends('client.users.profile')
@section('template_title', __('messages.account.nav_notifications'))

@section('user_content')
<h2 class="user-tab-title">{{ __('messages.account.nav_notifications') }}</h2>

{{-- "Sắp ra" — live, mọi user đều thấy chung các chương hẹn giờ sắp tới --}}
@if(($upcomingChapters ?? collect())->isNotEmpty())
<div class="block" style="margin-bottom:16px">
    <h3 class="user-tab-title" style="margin-bottom:10px"><i class="fa fa-clock" style="color:#e0a020"></i> {{ __('messages.account.upcoming_title') }}</h3>
    <div class="notif-list">
        @foreach($upcomingChapters as $c)
            <a href="{{ url('articles/'.$c->article->getRouteKey()) }}" class="block notif-item notif-soon">
                <div class="notif-icon"><i class="fa fa-clock"></i></div>
                <div class="notif-body">
                    <div class="notif-text">
                        <strong>{{ $c->article->title }}</strong> —
                        {{ __('messages.account.chapter_soon_notif', ['number' => $c->number, 'date' => optional($c->published_at)->format('d/m/Y H:i')]) }}
                    </div>
                </div>
            </a>
        @endforeach
    </div>
</div>
@endif

@if($notifications->isEmpty())
    <div class="block">
        <div class="nothing" style="padding:40px 0;text-align:center;color:var(--meta-color)">
            <i class="fa fa-bell" style="font-size:32px;opacity:.4;display:block;margin-bottom:10px"></i>
            {{ __('messages.account.notifications_empty') }}
        </div>
    </div>
@else
    <div class="notif-list">
        @foreach($notifications as $n)
            @php
                $d = $n->data;
                $isNew = is_null($n->read_at);
                $isGift = ($d['type'] ?? '') === 'gift';
                $link = $isGift
                    ? ($d['url'] ?? url('/catalog'))
                    : ((!empty($d['article_slug']) && isset($d['chapter_number']))
                        ? route('articles.chapters.show', [$d['article_slug'], $d['chapter_number']])
                        : (!empty($d['article_slug']) ? url('articles/'.$d['article_slug']) : '#'));
            @endphp
            <a href="{{ route('notifications.read', $n->id) }}" class="block notif-item {{ $isNew ? 'is-new' : '' }} {{ $isGift ? 'notif-gift' : '' }}">
                <div class="notif-icon"><i class="fa {{ $isGift ? 'fa-gift' : (($d['mode'] ?? 'new') === 'soon' ? 'fa-clock' : 'fa-book-open') }}"></i></div>
                <div class="notif-body">
                    <div class="notif-text">
                        @if($isGift)
                            <strong>{{ $d['title'] ?? 'Gift' }}</strong>
                        @else
                            <strong>{{ $d['article_title'] ?? __('messages.account.nav_notifications') }}</strong>
                            — @if(($d['mode'] ?? 'new') === 'soon')
                                {{ __('messages.account.chapter_soon_notif', ['number' => $d['chapter_number'] ?? '', 'date' => $d['publish_at'] ?? '']) }}
                            @else
                                {{ __('messages.account.new_chapter_notif', ['number' => $d['chapter_number'] ?? '']) }}
                            @endif
                        @endif
                    </div>
                    <div class="notif-time">{{ optional($n->created_at)->diffForHumans() }}</div>
                </div>
                @if($isNew)<span class="notif-dot"></span>@endif
            </a>
        @endforeach
    </div>
    <div style="margin-top:16px">{{ $notifications->links() }}</div>
@endif

<style>
.notif-list { display:flex; flex-direction:column; gap:8px; }
.notif-item { display:flex; align-items:center; gap:14px; text-decoration:none; color:var(--text-color); }
.notif-item.is-new { border-left:3px solid var(--btn-primary-color,#0084d1); }
.notif-icon { width:42px;height:42px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;
    background:#e7f3ff;color:#0a6ebd;font-size:18px; }
.notif-body { flex:1;min-width:0; }
.notif-text { font-size:14px; }
.notif-time { font-size:12px;color:var(--meta-color,#888);margin-top:2px; }
.notif-dot { width:9px;height:9px;border-radius:50%;background:#ff4040;flex-shrink:0; }
.notif-gift .notif-icon { background:#fff7e0; color:#e0a020; }
.notif-soon .notif-icon { background:#fff3e0; color:#e0a020; }
</style>
@endsection
