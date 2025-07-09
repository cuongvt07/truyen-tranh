<div id="footer" class="footer">
    <div class="container">
        <div class="xs col-sm-5">
            <strong>
                Website Thích truyện - là trang đọc truyện chữ full hay online miễn phí với nhiều thể loại truyện tiểu thuyết ngôn tình có tình tiết lãng mạn ngọt ngào, đi kèm đam mỹ sắc đầy kịch tính và nóng bỏng cùng sự sủng, ngọt, hoàn ngược, HE. Các thể loại khác như Tiên Hiệp, Thám Hiểm, Quân Sự, Truyện Ma,...
            </strong> - Web đọc truyện
            <br />
        </div>
        <ul class="col-xs-12 col-sm-7 list-unstyled">
            <li class="text-right pull-right">
                <a class="backtop" title="Back to top" href="#wrap" rel="nofollow" aria-label="Back to top">
                    <span class="glyphicon glyphicon-upload"></span>
                </a>
            </li>
            <li class="hidden-xs tag-list">
                <?php
                    use App\Models\Genre;
                    $genres = Genre::all();
                ?>
                @foreach($genres as $genre)
                    <a href="{{ route('genres.show', $genre->id) }}" class="tag">
                        {{ $genre->name }}
                    </a>
                @endforeach
            </li>
        </ul>
    </div>
</div>
