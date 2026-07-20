<div id="footer" class="footer">
    <div class="container">
        <div class="xs col-sm-5">
            <strong>
                {{ __('messages.footer.description', ['name' => config('app.name')]) }}
            </strong> - {{ __('messages.footer.reading_site') }}
            <br />
        </div>
        <ul class="col-xs-12 col-sm-7 list-unstyled">
            <li class="text-right pull-right">
                <a class="backtop" title="{{ __('messages.footer.back_to_top') }}" href="#wrap" rel="nofollow" aria-label="{{ __('messages.footer.back_to_top') }}">
                    <span class="glyphicon glyphicon-upload"></span>
                </a>
            </li>
            <li class="hidden-xs tag-list">
                <?php
                    use App\Models\Genre;
                    $genres = Genre::all();
                ?>
                @foreach($genres as $genre)
                    <a href="{{ route_path('genres.show', $genre) }}" class="tag">
                        {{ $genre->name }}
                    </a>
                @endforeach
            </li>
        </ul>
    </div>
</div>
