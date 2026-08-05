<h1>Top Tags @if($activeGenre)<small class="alpha-search-tags__scope">· {{ $activeGenre->name }}</small>@endif</h1>
<div class="alpha-search-tags">
    @forelse($topTags as $tag)
        <a href="{{ route_path('home.search', array_filter(['keyword' => $tag->name, 'genre' => $activeGenre?->id])) }}">{{ $tag->name }}</a>
    @empty
        <span>No tags yet.</span>
    @endforelse
</div>
