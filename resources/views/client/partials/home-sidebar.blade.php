<div class="list list-truyen list-cat col-xs-12">
    <div class="title-list" onclick="toggleGenres()" style="display: flex; justify-content: space-between; align-items: center;">
        <h4>{{ __('messages.ui.genres') }}</h4>
        <i id="genre-arrow" class="fa fa-chevron-down" aria-hidden="true"></i>
    </div>
    <div class="row" id="genre-content" style="display: none;">
        @foreach ($genres as $genre)
            <div class="col-xs-6"><a href="{{ route_path('genres.show', $genre) }}" title="{{ $genre->name }}">{{ $genre->name }}</a></div>
        @endforeach
    </div>
</div>

<script>
function toggleGenres() {
    const content = document.getElementById('genre-content');
    const arrow = document.getElementById('genre-arrow');
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        arrow.className = 'fa fa-chevron-up';
    } else {
        content.style.display = 'none';
        arrow.className = 'fa fa-chevron-down';
    }
}
</script>
