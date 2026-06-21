<?php

namespace App\Models;

use App\Enums\ArticleCompleteStatus;
use App\Enums\ArticleStatus;
use App\Scopes\ApprovedArticleScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Auth;

class Article extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();
        if (!is_route('admin.*')) {
            static::addGlobalScope(new ApprovedArticleScope());
        }
    }

    protected $fillable = [
        'title', 'alt_title', 'illustrator', 'description', 'user_id', 'team_id',
        'cover_image', 'background_image',
        'affi_link', 'affi_image',
        'novel_type', 'is_adult', 'year_of_release', 'country',
        'is_completed', 'rating', 'rating_count',
        'similar_article_ids', 'translation_request_article_ids',
        'related_genre_ids', 'view', 'is_user_submitted',
        'credit_start_chapter', 'credit_per_chapter',
    ];

    protected $casts = [
        'similar_article_ids' => 'array',
        'translation_request_article_ids' => 'array',
        'related_genre_ids' => 'array',
        'is_user_submitted' => 'boolean',
    ];

    protected static function booted(): void
    {
        // Làm mới sitemap khi nội dung đổi; bỏ qua thay đổi chỉ liên quan lượt xem/đánh giá.
        static::saved(function (self $article) {
            $ignore = ['view', 'rating', 'rating_count', 'updated_at'];
            if ($article->wasRecentlyCreated || $article->wasChanged('title') || !$article->slug()->exists()) {
                Slug::ensureFor($article, 'article', $article->title);
            }
            if (count(array_diff(array_keys($article->getChanges()), $ignore)) > 0) {
                bump_sitemap_version();
            }
        });
        static::deleted(fn () => bump_sitemap_version());
    }

    protected function getCompletedTextAttribute()
    {
        $value = $this->is_completed;
        return ArticleCompleteStatus::from($value)->label();
    }

    protected function getStatusTextAttribute()
    {
        $value = $this->status;
        return ArticleStatus::from($value)->label();
    }

    protected function getViewTextAttribute()
    {
        $value = $this->view;
        return $value.' lượt xem';
    }

    protected function getChaptersTextAttribute()
    {
        // Dùng chapters_count (withCount) nếu có để tránh load toàn bộ chương (N+1)
        $value = $this->chapters_count ?? $this->chapters()->count();
        return $value.' chương';
    }

    protected function getNewestChapterAttribute(): ?Chapter
    {
        $newestChapterNumber = $this->chapters->max('number');
        $newestChapter = $this->chapters
            ->where('number', $newestChapterNumber)
            ->first();
        if (!empty($newestChapter)) {
            return $newestChapter;
        } else {
            return null;
        }
    }

    protected function getFirstChapterAttribute(): ?Chapter
    {
        $firstChapterNumber = $this->chapters()->min('number');
        $firstChapter = $this->chapters
            ->where('number', $firstChapterNumber)
            ->first();
        if (!empty($firstChapter)) {
            return $firstChapter;
        } else {
            return null;
        }
    }

    protected function getCreatedAtTextAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    protected function getUpdatedAtTextAttribute()
    {
        return $this->updated_at->diffForHumans();
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function slug(): MorphOne
    {
        return $this->morphOne(Slug::class, 'sluggable')->where('type', 'article');
    }

    public function getRouteKey()
    {
        return $this->relationLoaded('slug')
            ? ($this->slug?->slug ?? $this->getKey())
            : ($this->slug()->value('slug') ?? $this->getKey());
    }

    public function authors(
    ): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Author::class, 'articles_authors',
            'article_id', 'author_id');
    }

    public function genres(
    ): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Genre::class, 'articles_genres',
            'article_id', 'genre_id');
    }

    public function chapters(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Chapter::class, 'article_id', 'id');
    }

    public function tags(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'article_tag');
    }

    public function characters(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Character::class, 'article_character');
    }

    public function collections(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Collection::class, 'collection_article');
    }

    public function comments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Comment::class, 'article_id', 'id');
    }

    public function bookmarks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Bookmark::class, 'article_id', 'id');
    }

    public function getBookmarkForCurrentUser() : ?Bookmark
    {
        // Lấy bookmark của bài viết hiện tại cho người dùng đang đăng nhập
        $bookmark = Auth::user()->bookmarks()->where('article_id', $this->id)->first();

        return $bookmark; // Nếu không tìm thấy bookmark nó sẽ trả về null mặc định
    }

    public static function getHotArticles(
    ): \Illuminate\Database\Eloquent\Builder
    {
        return self::query()
            ->orderByDesc('view');
    }

    public static function getNewUpdateArticles(
    ): \Illuminate\Database\Eloquent\Builder
    {
        return self::query()
            ->orderByDesc('updated_at');
    }

    public static function getCompletedArticles(
    ): \Illuminate\Database\Eloquent\Builder
    {
        return self::query()
            ->where('is_completed', ArticleCompleteStatus::COMPLETED)
            ->orderByDesc('updated_at');
    }

    public function getNewestChapters($size
    ): \Illuminate\Database\Eloquent\Relations\HasMany {
        return self::chapters()->orderByDesc('number')->take($size);
    }

    public function getNewestCommentsPaginate($perPage = 10
    ): \Illuminate\Contracts\Pagination\LengthAwarePaginator {
        return self::comments()
            ->whereNull('parent_id')                       // chỉ comment gốc
            ->where('is_hidden', false)
            ->with([
                'user',
                'votes',
                'replies' => function ($q) {
                    return $q->where('is_hidden', false)->with(['user', 'votes']);
                },
            ])
            ->orderByDesc('score')
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'comment_page');
    }

    public function increaseViewCount()
    {
        $this->timestamps = false;
        $this->increment('view');
        $this->save();
        $this->timestamps = true;
    }

    public function affiliateLinks()
    {
        return $this->hasMany(AffiliateLink::class);
    }
}
