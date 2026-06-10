<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaticPage;
use App\Models\StaticPageComment;
use Illuminate\Http\Request;

class ForumController extends Controller
{
    // --------------------------------------------------- Forum Post moderation

    // GET admin/forum/posts
    public function posts(Request $request)
    {
        $query = StaticPage::with(['parent:id,title_en,title_vi,slug', 'author:id,name,username'])
            ->where('page_type', 'forum_post');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($category = $request->get('category')) {
            $query->whereHas('parent', fn ($q) => $q->where('slug', $category));
        }

        if ($q = trim((string) $request->get('q'))) {
            $query->where(function ($sub) use ($q) {
                $sub->where('title_en', 'like', "%{$q}%")
                    ->orWhere('title_vi', 'like', "%{$q}%");
            });
        }

        $posts = $query->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')")
            ->orderByDesc('created_at')
            ->paginate(30)
            ->withQueryString();

        $categories = StaticPage::where('page_type', 'forum_category')
            ->orderBy('title_en')
            ->get(['id', 'slug', 'title_en', 'title_vi']);

        return view('admin.forum.posts', compact('posts', 'categories'));
    }

    // PATCH admin/forum/posts/{post}/approve
    public function approvePost(StaticPage $post)
    {
        abort_unless($post->page_type === 'forum_post', 404);

        $post->update(['status' => StaticPage::STATUS_APPROVED, 'is_active' => true]);

        return back()->with('success', 'Đã duyệt bài viết.');
    }

    // PATCH admin/forum/posts/{post}/reject
    public function rejectPost(StaticPage $post)
    {
        abort_unless($post->page_type === 'forum_post', 404);

        $post->update(['status' => StaticPage::STATUS_REJECTED, 'is_active' => false]);

        return back()->with('success', 'Đã từ chối bài viết.');
    }

    // DELETE admin/forum/posts/{post}
    public function destroyPost(StaticPage $post)
    {
        abort_unless($post->page_type === 'forum_post', 404);

        $post->delete();

        return back()->with('success', 'Đã xoá bài viết.');
    }

    // -------------------------------------------- Static page comment moderate

    // GET admin/forum/comments
    public function comments(Request $request)
    {
        $query = StaticPageComment::with(['user:id,name,username', 'page:id,title_en,title_vi,slug,page_type,parent_id']);

        if ($q = trim((string) $request->get('q'))) {
            $query->where('content', 'like', "%{$q}%");
        }

        if ($pageType = $request->get('page_type')) {
            $query->whereHas('page', fn ($sub) => $sub->where('page_type', $pageType));
        }

        $comments = $query->orderByDesc('created_at')->paginate(30)->withQueryString();

        return view('admin.forum.comments', compact('comments'));
    }

    // DELETE admin/forum/comments/{comment}
    public function destroyComment(StaticPageComment $comment)
    {
        $comment->delete();

        return back()->with('success', 'Đã xoá bình luận.');
    }

    // POST admin/forum/comments/bulk-destroy
    public function bulkDestroyComments(Request $request)
    {
        $ids = array_filter(array_map('intval', (array) $request->input('ids', [])));

        if (!empty($ids)) {
            StaticPageComment::whereIn('id', $ids)->delete();
        }

        return back()->with('success', 'Đã xoá ' . count($ids) . ' bình luận.');
    }
}
