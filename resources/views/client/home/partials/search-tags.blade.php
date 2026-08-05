{{-- Tag chạy chung hàng với chip thể loại; đổi cate thì khối này được thay lại. --}}
@foreach($topTags as $tag)
    <a class="alpha-search-genre alpha-search-genre--tag"
       href="{{ route_path('home.search', array_filter(['keyword' => $tag->name, 'genre' => $activeGenre?->id])) }}">{{ $tag->name }}</a>
@endforeach
