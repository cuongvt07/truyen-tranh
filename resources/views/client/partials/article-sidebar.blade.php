{{-- Truyện cùng tác giả --}}
@if($sameAuthorArticles->count())
    <div class="list list-truyen col-xs-12">
        <div class="title-list">
            <h4>Truyện cùng tác giả</h4>
        </div>
        @foreach($sameAuthorArticles as $item)
            <div class="row">
                <div class="col-xs-12">
                    <span class="glyphicon glyphicon-chevron-right"></span>
                    <h3>
                        <a href="{{ route('articles.show', $item->id) }}" title="{{ $item->title }}">
                            {{ $item->title }}
                        </a>
                    </h3>
                </div>
            </div>
        @endforeach
    </div>
@endif

{{-- Có thể bạn thích --}}
@if($suggestedArticles->count())
    <div class="list list-truyen col-xs-12">
        <div class="title-list">
            <h4>CÓ THỂ BẠN THÍCH</h4>
        </div>
        @foreach($suggestedArticles as $item)
            <div class="row">
                <div class="col-xs-12">
                    <span class="glyphicon glyphicon-chevron-right"></span>
                    <h3>
                        <a href="{{ route('articles.show', $item->id) }}" title="{{ $item->title }}">
                            {{ $item->title }}
                        </a>
                    </h3>
                </div>
            </div>
        @endforeach
    </div>
@endif
