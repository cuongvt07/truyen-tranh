<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Country;
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

        // Lọc theo thể loại (nhiều); ?genre=3 là alias đơn cho ?genres[]=3
        $selectedGenres = array_filter((array) $request->get('genres', []));
        if (empty($selectedGenres) && $request->filled('genre')) {
            $selectedGenres = [(int) $request->get('genre')];
        }
        if (!empty($selectedGenres)) {
            $query->whereHas('genres', function ($q) use ($selectedGenres) {
                $q->whereIn('genres.id', $selectedGenres);
            });
        }

        // Trạng thái (single: 0=ongoing, 1=completed)
        if ($request->filled('status')) {
            $query->where('is_completed', (int) $request->get('status'));
        }

        // Loại truyện (checkbox array: types[])
        $selectedTypes = array_filter((array) $request->get('types', []), fn($v) => $v !== '');
        if (!empty($selectedTypes)) {
            $query->whereIn('novel_type', array_map('intval', $selectedTypes));
        }

        // Quốc gia (checkbox array: countries[])
        $selectedCountries = array_filter((array) $request->get('countries', []), fn($v) => $v !== '');
        // alias đơn ?country=X
        if (empty($selectedCountries) && $request->filled('country')) {
            $selectedCountries = [(int) $request->get('country')];
        }
        if (!empty($selectedCountries)) {
            $query->whereIn('country', array_map('intval', $selectedCountries));
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

        $articles  = $query->paginate(30)->withQueryString();
        $genres    = Genre::orderBy('name')->get();
        $countries = Country::orderBy('sort_order')->get();

        // Get selected genre name if only one genre is selected
        $selectedGenreName = null;
        if (count($selectedGenres) === 1) {
            $selectedGenreName = Genre::find($selectedGenres[0])?->name;
        }

        return view('client.catalog.index', [
            'articles'          => $articles,
            'genres'            => $genres,
            'countries'         => $countries,
            'selectedGenres'    => $selectedGenres,
            'selectedTypes'     => array_map('strval', $selectedTypes),
            'selectedCountries' => array_map('strval', $selectedCountries),
            'selectedGenreName' => $selectedGenreName,
            'filters'           => $request->only(['search', 'status', 'types', 'countries', 'ordering']),
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
