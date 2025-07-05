<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Nette\Utils\Paginator;

class ArticleController extends Controller
{
    public function show(Article $article)
    {
        $article->increaseViewCount();

        $chapters = $article->chapters()->paginate();
        $comments = $article->getNewestCommentsPaginate();

        // Truyện cùng tác giả (loại bỏ chính nó)
        $firstAuthor = $article->authors->first();

        $sameAuthorArticles = collect();

        if ($firstAuthor) {
            $sameAuthorArticles = $firstAuthor->articles()
                ->where('articles.id', '!=', $article->id)
                ->latest()
                ->limit(5)
                ->get();
        }

        $suggestedArticles = Article::where('id', '!=', $article->id)
            ->orderByDesc('view')
            ->limit(10)
            ->get();

        return view('client.articles.show', [
            'article' => $article,
            'chapters' => $chapters,
            'comments' => $comments,
            'sameAuthorArticles' => $sameAuthorArticles,
            'suggestedArticles' => $suggestedArticles,
        ]);
    }
}
