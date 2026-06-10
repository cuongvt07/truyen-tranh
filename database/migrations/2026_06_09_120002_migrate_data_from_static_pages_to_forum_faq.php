<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Migrate existing forum/faq data from static_pages to new dedicated tables.
     */
    public function up(): void
    {
        // ============ FORUM ============
        // Migrate forum_category → forum_categories
        $forumCategories = DB::table('static_pages')
            ->where('page_type', 'forum_category')
            ->orderBy('sort_order')
            ->get();

        $categoryMap = []; // old_id => new_id

        foreach ($forumCategories as $cat) {
            $newId = DB::table('forum_categories')->insertGetId([
                'slug'              => $cat->slug,
                'title_en'          => $cat->title_en,
                'title_vi'          => $cat->title_vi,
                'description_en'    => $cat->excerpt_en,
                'description_vi'    => $cat->excerpt_vi,
                'section_label_en'  => $cat->section_label_en ?? null,
                'section_label_vi'  => $cat->section_label_vi ?? null,
                'sort_order'        => $cat->sort_order ?? 0,
                'is_active'         => $cat->is_active ?? true,
                'created_at'        => $cat->created_at,
                'updated_at'        => $cat->updated_at,
            ]);
            $categoryMap[$cat->id] = $newId;
        }

        // Migrate forum_post → forum_posts
        $forumPosts = DB::table('static_pages')
            ->where('page_type', 'forum_post')
            ->get();

        $postMap = [];

        foreach ($forumPosts as $post) {
            $categoryId = $categoryMap[$post->parent_id] ?? null;
            if (!$categoryId) continue; // skip orphan posts

            $newId = DB::table('forum_posts')->insertGetId([
                'category_id' => $categoryId,
                'user_id'     => $post->user_id ?? null,
                'slug'        => $post->slug,
                'title_en'    => $post->title_en,
                'title_vi'    => $post->title_vi,
                'content_en'  => $post->content_en ?? '',
                'content_vi'  => $post->content_vi,
                'status'      => $post->status ?? 'approved',
                'is_pinned'   => $post->is_pinned ?? false,
                'is_locked'   => false,
                'view_count'  => $post->view_count ?? 0,
                'is_active'   => $post->is_active ?? true,
                'created_at'  => $post->created_at,
                'updated_at'  => $post->updated_at,
            ]);
            $postMap[$post->id] = $newId;
        }

        // Migrate static_page_comments (for forum posts) → forum_comments
        if (!empty($postMap)) {
            $forumComments = DB::table('static_page_comments')
                ->whereIn('static_page_id', array_keys($postMap))
                ->orderBy('id')
                ->get();

            $commentMap = [];

            foreach ($forumComments as $comment) {
                $postId = $postMap[$comment->static_page_id] ?? null;
                if (!$postId) continue;

                $parentId = null;
                if ($comment->parent_id && isset($commentMap[$comment->parent_id])) {
                    $parentId = $commentMap[$comment->parent_id];
                }

                $newId = DB::table('forum_comments')->insertGetId([
                    'post_id'    => $postId,
                    'user_id'    => $comment->user_id,
                    'parent_id'  => $parentId,
                    'content'    => $comment->content,
                    'score'      => $comment->score ?? 0,
                    'created_at' => $comment->created_at,
                    'updated_at' => $comment->updated_at,
                ]);
                $commentMap[$comment->id] = $newId;
            }
        }

        // ============ FAQ ============
        $faqCategories = DB::table('static_pages')
            ->where('page_type', 'faq_category')
            ->orderBy('sort_order')
            ->get();

        $faqCategoryMap = [];

        foreach ($faqCategories as $cat) {
            $newId = DB::table('faq_categories')->insertGetId([
                'slug'           => $cat->slug,
                'title_en'       => $cat->title_en,
                'title_vi'       => $cat->title_vi,
                'description_en' => $cat->excerpt_en,
                'description_vi' => $cat->excerpt_vi,
                'sort_order'     => $cat->sort_order ?? 0,
                'is_active'      => $cat->is_active ?? true,
                'created_at'     => $cat->created_at,
                'updated_at'     => $cat->updated_at,
            ]);
            $faqCategoryMap[$cat->id] = $newId;
        }

        $faqArticles = DB::table('static_pages')
            ->where('page_type', 'faq_article')
            ->get();

        $faqArticleMap = [];

        foreach ($faqArticles as $article) {
            $categoryId = $faqCategoryMap[$article->parent_id] ?? null;
            if (!$categoryId) continue;

            $newId = DB::table('faq_articles')->insertGetId([
                'category_id'      => $categoryId,
                'slug'             => $article->slug,
                'title_en'         => $article->title_en,
                'title_vi'         => $article->title_vi,
                'content_en'       => $article->content_en ?? '',
                'content_vi'       => $article->content_vi,
                'is_pinned'        => $article->is_pinned ?? false,
                'view_count'       => $article->view_count ?? 0,
                'comments_enabled' => $article->comments_enabled ?? true,
                'is_active'        => $article->is_active ?? true,
                'sort_order'       => $article->sort_order ?? 0,
                'created_at'       => $article->created_at,
                'updated_at'       => $article->updated_at,
            ]);
            $faqArticleMap[$article->id] = $newId;
        }

        // Migrate FAQ comments
        if (!empty($faqArticleMap)) {
            $faqComments = DB::table('static_page_comments')
                ->whereIn('static_page_id', array_keys($faqArticleMap))
                ->orderBy('id')
                ->get();

            $faqCommentMap = [];

            foreach ($faqComments as $comment) {
                $articleId = $faqArticleMap[$comment->static_page_id] ?? null;
                if (!$articleId) continue;

                $parentId = null;
                if ($comment->parent_id && isset($faqCommentMap[$comment->parent_id])) {
                    $parentId = $faqCommentMap[$comment->parent_id];
                }

                $newId = DB::table('faq_comments')->insertGetId([
                    'article_id' => $articleId,
                    'user_id'    => $comment->user_id,
                    'parent_id'  => $parentId,
                    'content'    => $comment->content,
                    'score'      => $comment->score ?? 0,
                    'created_at' => $comment->created_at,
                    'updated_at' => $comment->updated_at,
                ]);
                $faqCommentMap[$comment->id] = $newId;
            }
        }

        // Update comment_count in forum_posts
        DB::statement('
            UPDATE forum_posts fp
            SET comment_count = (
                SELECT COUNT(*) FROM forum_comments fc WHERE fc.post_id = fp.id
            )
        ');

        // Update comment_count in faq_articles
        DB::statement('
            UPDATE faq_articles fa
            SET comment_count = (
                SELECT COUNT(*) FROM faq_comments fc WHERE fc.article_id = fa.id
            )
        ');
    }

    public function down(): void
    {
        // Không rollback data migration để tránh mất dữ liệu
        // Admin có thể tự xoá bằng tay nếu cần
    }
};
