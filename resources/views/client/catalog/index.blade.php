@extends('layout.novelight')

@section('template_title', $selectedGenreName ? __('messages.catalog.genre_label') . ': ' . $selectedGenreName : __('messages.catalog.page_title'))

@section('page_css')
<link rel="stylesheet" href="{{ asset('static/core/css/catalogee8b.css') }}?ver=1.8.0">
<link rel="stylesheet" href="{{ asset('static/core/css/indexee8b.css') }}?ver=1.8.0">
@endsection

@section('content')
<div class="container">
    <div class="flex-content catalog-flex">
        {{-- Results --}}
        <div class="main block">
            <div class="page-title__catalog">
                <h1 class="page-title">
                    @if($selectedGenreName)
                        <i class="fa fa-layer-group"></i> {{ $selectedGenreName }}
                    @else
                        {{ __('messages.catalog.page_title') }}
                    @endif
                </h1>
                <div class="text-input checkbox-input select">
                    <div class="text-input__wrapper">
                        <select name="ordering" id="catalog-ordering"
                                onchange="(function(v){var p=new URLSearchParams(window.location.search);p.set('ordering',v);p.delete('page');window.location.search=p.toString();})(this.value)">
                            <option value="-time_updated" {{ ($filters['ordering'] ?? '-time_updated')=='-time_updated'?'selected':'' }}>{{ __('messages.catalog.order_recently_updated') }}</option>
                            <option value="-time_created" {{ ($filters['ordering'] ?? '')=='-time_created'?'selected':'' }}>{{ __('messages.catalog.order_recently_added') }}</option>
                            <option value="popularity"    {{ ($filters['ordering'] ?? '')=='popularity'?'selected':'' }}>{{ __('messages.catalog.order_popular') }}</option>
                            <option value="title"         {{ ($filters['ordering'] ?? '')=='title'?'selected':'' }}>{{ __('messages.catalog.order_name_az') }}</option>
                            <option value="-year_of_realese" {{ ($filters['ordering'] ?? '')=='-year_of_realese'?'selected':'' }}>{{ __('messages.catalog.order_release_year') }}</option>
                        </select>
                    </div>
                </div>
            </div>

            @if($articles->total() > 0)
                <p class="meta-color" style="margin:0 0 12px;font-size:14px">{{ __('messages.catalog.found_results', ['count' => number_format($articles->total())]) }}</p>
            @endif

            <div class="manga-grid-list">
                @forelse($articles as $article)
                    <a href="{{ route('articles.show', $article) }}" class="item">
                        <div class="poster image image-cover lazy-load-bg">
                            <img class="lazy-image" loading="eager" src="{{ novel_poster($article) }}" alt="{{ $article->title }}">
                            @if($article->is_completed)<span class="grid-badge">Full</span>@endif
                        </div>
                        <div class="title clamp clamp-2">{{ $article->title }}</div>
                    </a>
                @empty
                    <div class="nothing" style="grid-column:1/-1;text-align:center;padding:40px 0">{{ __('messages.catalog.no_matching_results') }}</div>
                @endforelse
            </div>

            {{ $articles->links('vendor.pagination.novelight') }}
        </div>

        {{-- Filter sidebar --}}
        <div class="second-information block">
            <form class="filter-container" method="get" action="{{ route('catalog.index') }}">
                <input type="hidden" name="ordering" value="{{ $filters['ordering'] ?? '-time_updated' }}">

                <div class="search">
                    <div class="text-input">
                        <input type="text" name="search" placeholder="{{ __('messages.catalog.search_placeholder') }}" value="{{ $filters['search'] ?? '' }}">
                        <button type="submit" class="right-icon"><i class="fa fa-search"></i></button>
                    </div>
                </div>

                <div class="filters">
                    <div class="expand">
                        <div class="filter-name name open-close" p-target="genres-content" nolock>
                            {{ __('messages.catalog.genres') }} <i class="fa fa-angle-down"></i>
                        </div>
                        <div id="genres-content" class="expand-content">
                            <div class="checkbox">
                                @foreach($genres as $genre)
                                    <div>
                                        <label>
                                            <input type="checkbox" name="genres[]" value="{{ $genre->id }}"
                                                   {{ in_array($genre->id, $selectedGenres ?? []) ? 'checked' : '' }}>
                                            {{ $genre->name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="expand">
                        <div class="filter-name name open-close" p-target="status-content" nolock>
                            {{ __('messages.catalog.status') }} <i class="fa fa-angle-down"></i>
                        </div>
                        <div id="status-content" class="expand-content">
                            <div class="checkbox">
                                @php $curStatus = $filters['status'] ?? ''; @endphp
                                <div>
                                    <label>
                                        <input type="radio" name="status" value="" {{ $curStatus==='' ? 'checked':'' }}>
                                        {{ __('messages.catalog.all') }}
                                    </label>
                                </div>
                                <div>
                                    <label>
                                        <input type="radio" name="status" value="0" {{ $curStatus==='0' ? 'checked':'' }}>
                                        {{ __('messages.catalog.status_ongoing') }}
                                    </label>
                                </div>
                                <div>
                                    <label>
                                        <input type="radio" name="status" value="1" {{ $curStatus==='1' ? 'checked':'' }}>
                                        {{ __('messages.catalog.status_completed') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="expand">
                        <div class="filter-name name open-close" p-target="type-content" nolock>
                            {{ __('messages.catalog.type') }} <i class="fa fa-angle-down"></i>
                        </div>
                        <div id="type-content" class="expand-content">
                            <div class="checkbox">
                                <div>
                                    <label>
                                        <input type="checkbox" name="types[]" value="0" {{ in_array('0', $selectedTypes ?? []) ? 'checked':'' }}>
                                        Web Novel
                                    </label>
                                </div>
                                <div>
                                    <label>
                                        <input type="checkbox" name="types[]" value="1" {{ in_array('1', $selectedTypes ?? []) ? 'checked':'' }}>
                                        Light Novel
                                    </label>
                                </div>
                                <div>
                                    <label>
                                        <input type="checkbox" name="types[]" value="2" {{ in_array('2', $selectedTypes ?? []) ? 'checked':'' }}>
                                        {{ __('messages.catalog.type_published') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="expand">
                        <div class="filter-name name open-close" p-target="country-content" nolock>
                            {{ __('messages.catalog.country') }} <i class="fa fa-angle-down"></i>
                        </div>
                        <div id="country-content" class="expand-content">
                            <div class="checkbox">
                                @foreach($countries as $c)
                                <div>
                                    <label>
                                        <input type="checkbox" name="countries[]" value="{{ $c->id }}"
                                               {{ in_array((string)$c->id, $selectedCountries ?? []) ? 'checked':'' }}>
                                        {{ $c->display_name }}
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="btns">
                    <a href="{{ route('catalog.index') }}" class="btn btn-invincible">{{ __('messages.catalog.reset') }}</a>
                    <button type="submit" class="btn btn-primary">{{ __('messages.catalog.filter') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.catalog-flex { display:flex; gap:24px; align-items:flex-start; }
.catalog-flex .main { width:calc(100% - 300px); min-width:0; }
.page-title__catalog { display:flex; justify-content:space-between; align-items:center; margin-bottom:6px; }
.page-title__catalog .page-title { font-size:28px; margin:0; }
.page-title__catalog select { padding:8px 12px; border-radius:6px; border:1px solid var(--input-border-color,#2a2a3e); background:var(--bg,#fff); color:inherit; min-width:170px; cursor:pointer; }
.catalog-flex .second-information { width:280px; flex-shrink:0; position:sticky; top:90px; padding:16px; border-radius:8px; }
.grid-badge { background:var(--primary,#e84040); color:#fff; font-size:11px; padding:2px 6px; border-radius:3px; position:absolute; top:4px; right:4px; }

/* Grid layout: 5 columns on desktop, 2 on mobile */
.manga-grid-list {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}
.manga-grid-list .item {
    display: block;
    text-decoration: none;
    color: inherit;
    transition: transform 0.2s;
}
.manga-grid-list .item:hover {
    transform: translateY(-4px);
}
.manga-grid-list .item .poster {
    width: 100%;
    padding-top: 140%;
    position: relative;
    border-radius: 6px;
    overflow: hidden;
    margin-bottom: 8px;
}
.manga-grid-list .item .poster img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.manga-grid-list .item .title {
    font-size: 14px;
    line-height: 1.4;
    font-weight: 500;
}

.filter-container .search { margin-bottom:14px; }
.filter-container .text-input { display:flex; align-items:center; border:1px solid var(--input-border-color,#2a2a3e); border-radius:5px; overflow:hidden; }
.filter-container .search input { flex:1; border:none; background:transparent; padding:9px 10px; color:inherit; }
.filter-container .search .right-icon { background:none; border:none; padding:0 10px; cursor:pointer; color:var(--meta-color); }
.filter-container .filter-name { font-weight:600; margin:6px 0; font-size:14px; cursor:pointer; }
.filter-container .option { margin-bottom:10px; }
.filter-container select { width:100%; padding:8px 10px; border-radius:5px; border:1px solid var(--input-border-color,#2a2a3e); background:var(--bg,#fff); color:inherit; }
.filter-container .checkbox { max-height:220px; overflow-y:auto; padding-right:4px; }
.filter-container .checkbox label { display:flex; align-items:center; gap:8px; padding:4px 0; font-size:14px; cursor:pointer; }
.filter-container .expand-content.hide { display:none; }
.filter-container .btns { display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-top:16px; }
.filter-container .btns .btn { text-align:center; }

/* Tablet: 3 columns */
@media (max-width:1055px){ 
    .catalog-flex { flex-direction:column-reverse; } 
    .catalog-flex .main, .catalog-flex .second-information { width:100%; position:static; }
    .manga-grid-list { grid-template-columns: repeat(3, 1fr); }
}

/* Mobile: 2 columns */
@media (max-width:768px){ 
    .manga-grid-list { 
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }
    .manga-grid-list .item .title {
        font-size: 13px;
    }
}
</style>
@endsection
