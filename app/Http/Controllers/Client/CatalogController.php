<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Genre;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::query()->with(['genres', 'authors']);

        // Tìm kiếm theo tên
        if ($search = trim((string) $request->get('search'))) {
            $query->where('title', 'like', '%' . $search . '%');
        }

        // Lọc theo thể loại (nhiều)
        $selectedGenres = array_filter((array) $request->get('genres', []));
        if (!empty($selectedGenres)) {
            $query->whereHas('genres', function ($q) use ($selectedGenres) {
                $q->whereIn('genres.id', $selectedGenres);
            });
        }

        // Trạng thái
        if ($request->filled('status')) {
            $query->where('is_completed', (int) $request->get('status'));
        }

        // Loại truyện
        if ($request->filled('type')) {
            $query->where('novel_type', (int) $request->get('type'));
        }

        // Quốc gia
        if ($request->filled('country')) {
            $query->where('country', (int) $request->get('country'));
        }

        // Sắp xếp
        $ordering = $request->get('ordering', '-time_updated');
        switch ($ordering) {
            case 'title':            $query->orderBy('title'); break;
            case '-time_created':    $query->orderByDesc('created_at'); break;
            case '-year_of_realese': $query->orderByDesc('year_of_release'); break;
            case 'popularity':       $query->orderByDesc('view'); break;
            case '-time_updated':
            default:                 $query->orderByDesc('updated_at'); break;
        }

        $articles = $query->paginate(30)->withQueryString();
        $genres   = Genre::orderBy('name')->get();

        // Get selected genre name if only one genre is selected
        $selectedGenreName = null;
        if (count($selectedGenres) === 1) {
            $selectedGenreName = Genre::find($selectedGenres[0])?->name;
        }

        return view('client.catalog.index', [
            'articles'          => $articles,
            'genres'            => $genres,
            'selectedGenres'    => $selectedGenres,
            'selectedGenreName' => $selectedGenreName,
            'filters'           => $request->only(['search', 'status', 'type', 'country', 'ordering']),
        ]);
    }

    /**
     * Live search (instant) — trả JSON {html} cho overlay tìm kiếm.
     */
    public function liveSearch(Request $request)
    {
        $term = trim((string) $request->get('search'));
        $results = collect();

        if (mb_strlen($term) >= 1) {
            $results = Article::query()
                ->where('title', 'like', '%' . $term . '%')
                ->orderByDesc('view')
                ->limit(8)
                ->get();
        }

        $html = view('client.catalog._live_results', ['results' => $results, 'term' => $term])->render();

        return response()->json(['html' => $html]);
    }
}
