@extends('client.users.profile')
@section('template_title', __('messages.account.nav_notifications'))

@section('user_content')

@php $hasUpcoming = ($upcomingChapters ?? collect())->isNotEmpty(); @endphp

{{-- Tất cả thông báo nằm chung 1 khung; mỗi tin là 1 dòng, có gạch ngăn. --}}
<div class="block notif-block">
    <h2 class="user-tab-title">{{ __('messages.account.nav_notifications') }}</h2>

    {{-- Thông báo cá nhân TRƯỚC (chưa đọc đã được sắp lên đầu) --}}
    @if($notifications->isNotEmpty())
        @foreach($notifications as $n)
            @php
                $d = $n->data;
                $isNew = is_null($n->read_at);
                $isGift = ($d['type'] ?? '') === 'gift';
            @endphp
            <a href="{{ route_path('notifications.read', $n->id) }}" class="notif-item {{ $isNew ? 'is-new' : '' }} {{ $isGift ? 'notif-gift' : '' }}">
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
    @endif

    {{-- "Sắp ra" (live) — xuống cuối, không phải tin chưa đọc nên không lên đầu --}}
    @if($hasUpcoming && $notifications->onFirstPage())
        @foreach($upcomingChapters as $c)
            <a href="{{ url('articles/'.$c->article->getRouteKey()) }}" class="notif-item notif-soon">
                <div class="notif-icon"><i class="fa fa-clock"></i></div>
                <div class="notif-body">
                    <div class="notif-text">
                        <strong>{{ $c->article->title }}</strong> —
                        {{ __('messages.account.chapter_soon_notif', ['number' => $c->number, 'date' => optional($c->published_at)->format('d/m/Y H:i')]) }}
                    </div>
                </div>
            </a>
        @endforeach
    @endif

    {{-- Trống hoàn toàn --}}
    @if(!$hasUpcoming && $notifications->isEmpty())
        <div class="nothing" style="padding:40px 0;text-align:center;color:var(--meta-color)">
            <i class="fa fa-bell" style="font-size:32px;opacity:.4;display:block;margin-bottom:10px"></i>
            {{ __('messages.account.notifications_empty') }}
        </div>
    @endif
</div>

@if($notifications->hasPages())
    <div style="margin-top:16px">{{ $notifications->links() }}</div>
@endif

<style>
.notif-block { padding:6px; }
.notif-section-title { font-size:12px;font-weight:700;color:var(--meta-color,#888);text-transform:uppercase;letter-spacing:.04em;padding:12px 12px 6px; }
.notif-item { display:flex; align-items:center; gap:14px; text-decoration:none; color:var(--text-color); padding:12px; border-radius:8px; }
.notif-item + .notif-item { border-top:1px solid var(--border-color,#eee); }
.notif-section-title + .notif-item { border-top:0; }
.notif-icon { width:42px;height:42px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;
    background:#e7f3ff;color:#0a6ebd;font-size:18px; }
.notif-body { flex:1;min-width:0; }
.notif-text { font-size:14px; }
.notif-time { font-size:12px;color:var(--meta-color,#888);margin-top:2px; }
.notif-dot { width:9px;height:9px;border-radius:50%;background:#ff4040;flex-shrink:0; }
.notif-item.is-new { background:rgba(0,132,209,.05); }
.notif-gift .notif-icon { background:#fff7e0; color:#e0a020; }
.notif-soon .notif-icon { background:#fff3e0; color:#e0a020; }
</style>
@endsection
