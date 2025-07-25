<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ArticleStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Article\StoreArticleRequest;
use App\Http\Requests\Admin\Article\UpdateArticleRequest;
use App\Http\Requests\Article\ChangeStatusRequest;
use App\Models\Article;
use App\Models\Author;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;
class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        // if currentUser is Admin
        if ($currentUser->is_admin) {
            $articles = Article::query();
        } else {
            // else currentUser is Poster
            $articles = $currentUser->articles();
        }
        $articles->orderByDesc("id");
        if ($request->has('search')) {
            $searchText = $request->input('search');
            $articles->where('title', 'like', '%'.$searchText.'%');
        }

        $articles = $articles->paginate();
        return view('admin.articles.index', ['articles' => $articles]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $article = new Article();
        $authors = Author::all();
        $genres = Genre::all();
        return view('admin.articles.create', [
            'article' => $article,
            'authors' => $authors,
            'genres' => $genres,
            'selectedGenres' => array(),
            'selectedAuthors' => array(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreArticleRequest $request)
    {
        $request->validated();
        $validateData = $request->all();
        $validateData['user_id'] = Auth::id();

        $validateData = $this->uploadCoverImage($request, $validateData);
        $article = Article::create($validateData);
        $article->genres()->attach($validateData['genres']);
        $article->authors()->attach($validateData['authors']);

        if ($request->has('affiliate_links')) {
            foreach ($request->affiliate_links as $index => $linkData) {
                $affiliateLink = new \App\Models\AffiliateLink();
                $affiliateLink->article_id = $article->id;
                $affiliateLink->link = $linkData['link'];

                // Nếu có file
                if (isset($linkData['image_file']) && $request->file("affiliate_links.$index.image_file")) {
                    $file = $request->file("affiliate_links.$index.image_file");
                    $fileName = time() . '-' . $file->getClientOriginalName();
                    $file->move(public_path('images/articles/affiliates'), $fileName);
                    $affiliateLink->image_path = '/images/articles/affiliates/' . $fileName;
                } else {
                    $affiliateLink->image_path = '/images/articles/default.jpg';
                }

                $affiliateLink->save();
            }
        }

        return redirect()->route('admin.articles.index')->with('success', 'Tạo truyện thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Article $article)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Article $article)
    {
        $authors = Author::all();
        $genres = Genre::all();
        $selectedGenres = $article->genres->pluck('id')->toArray();
        $selectedAuthors = $article->authors->pluck('id')->toArray();
        return view('admin.articles.edit', [
            'article' => $article,
            'authors' => $authors,
            'genres' => $genres,
            'selectedGenres' => $selectedGenres,
            'selectedAuthors' => $selectedAuthors,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateArticleRequest $request, Article $article)
    {
        $request->validated();
        $data = $request->all();

        $data = $this->uploadCoverImage($request, $data);

        $article->update($data);
        $article->genres()->sync($data['genres'] ?? []);
        $article->authors()->sync($data['authors'] ?? []);

        $article->affiliateLinks()->delete();

        if ($request->has('affiliate_links')) {
            foreach ($request->affiliate_links as $index => $linkData) {
                $affiliateLink = new \App\Models\AffiliateLink();
                $affiliateLink->article_id = $article->id;
                $affiliateLink->link = $linkData['link'];

                if (isset($linkData['image_file']) && $request->file("affiliate_links.$index.image_file")) {
                    $file = $request->file("affiliate_links.$index.image_file");
                    $fileName = time() . '-' . $file->getClientOriginalName();
                    $file->move(public_path('images/articles/affiliates'), $fileName);
                    $affiliateLink->image_path = '/images/articles/affiliates/' . $fileName;
                } else {
                    $affiliateLink->image_path = '/images/articles/default.jpg';
                }

                $affiliateLink->save();
            }
        }

        return redirect()->route('admin.articles.index')->with('success', 'Sửa thông tin truyện thành công!');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Article $article)
    {
        $article->delete();
        return redirect()->route('admin.articles.index')
            ->with('success', 'Xoá truyện thành công!');
    }

    public function updateStatus(
        Article $article,
        $status
    ) {
        if (!validateArticleStatus($status)) {
            return redirect()->route('admin.articles.index');
        }
        if ($article->status != ArticleStatus::HIDDEN->label()) {
            $statusText = mb_strtolower(ArticleStatus::from($status)->label());
        } else {
            $statusText = "đã được hiển thị lại";
        }

        $message = 'Truyện "'.$article->title.'" '.$statusText.'!';

        $article->status = $status;
        $article->save();
        return redirect()->route('admin.articles.index')
            ->with('success', $message);
    }

    public function updateCompleteStatus(Article $article)
    {
        $article->is_completed = !$article->is_completed;
        $article->save();
        if ($article->is_completed) {
            $message = 'Thay đổi trạng thái thành đã hoàn thành thành công!';
        } else {
            $message = 'Thay đổi trạng thái thành chưa hoàn thành thành công!';
        }
        return redirect()->route('admin.articles.index')
            ->with('success', $message);
    }

    /**
     * @param  FormRequest $request
     * @param  array  $validateData
     *
     * @return array
     */
    private function uploadCoverImage(
        FormRequest $request,
        array $validateData
    ): array {
        if ($request->hasFile('cover_image')) {
            $image = $request->file('cover_image');
            $imageName = time().'-'.$image->getClientOriginalName();
            $image->move(public_path('images/articles'), $imageName);
            $validateData['cover_image'] = '/images/articles/'.$imageName;
        } else {
            if ($validateData['cover_image_url']) {
                $validateData['cover_image'] = $validateData['cover_image_url'];
            } else {
                $validateData['cover_image'] = '/images/articles/default.jpg';
            }
        }
        return $validateData;
    }

    /**
     * Upload affiliate image if exists, otherwise use default or provided URL.
     *
     * @param  UpdateArticleRequest  $request
     * @param  array  $data
     *
     * @return array
     */
    private function uploadAffiImage(UpdateArticleRequest $request, array $data): array
    {
        if ($request->hasFile('affi_image')) {
            $image = $request->file('affi_image');
            $imageName = time() . '-' . $image->getClientOriginalName();
            $image->move(public_path('images/articles/affiliates'), $imageName);
            $data['affi_image'] = '/images/articles/affiliates/' . $imageName;
        } elseif (!empty($data['affi_image_url'])) {
            $data['affi_image'] = $data['affi_image_url'];
        } else {
            $data['affi_image'] = '/images/articles/default.jpg';
        }

        return $data;
    }

}
