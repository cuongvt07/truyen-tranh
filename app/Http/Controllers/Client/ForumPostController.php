<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\StaticPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ForumPostController extends Controller
{
    // GET /forum/{category}/new-post
    public function create(string $category)
    {
        $categoryPage = $this->getCategory($category);

        return view('client.pages.forum-post-form', [
            'category' => $categoryPage,
            'post'     => null,
        ]);
    }

    // POST /forum/{category}/new-post
    public function store(Request $request, string $category)
    {
        $categoryPage = $this->getCategory($category);

        $validated = $request->validate([
            'title'   => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:100000'],
        ]);

        $slug = $this->uniqueSlug(Str::slug($validated['title']));

        StaticPage::create([
            'parent_id'        => $categoryPage->id,
            'user_id'          => Auth::id(),
            'page_type'        => 'forum_post',
            'slug'             => $slug,
            'title_en'         => $validated['title'],
            'content_en'       => $validated['content'],
            'comments_enabled' => true,
            'is_active'        => true,
            'status'           => StaticPage::STATUS_PENDING,
            'sort_order'       => 0,
        ]);

        return redirect()
            ->route('pages.forum.category', $category)
            ->with('success', app()->getLocale() === 'vi'
                ? 'Bài viết đã gửi, đang chờ admin duyệt.'
                : 'Your post has been submitted and is awaiting approval.');
    }

    // GET /forum/{category}/{post}/edit
    public function edit(string $category, string $post)
    {
        $categoryPage = $this->getCategory($category);
        $postPage     = $this->getPost($post, $categoryPage, ownedBy: Auth::id());

        return view('client.pages.forum-post-form', [
            'category' => $categoryPage,
            'post'     => $postPage,
        ]);
    }

    // PATCH /forum/{category}/{post}
    public function update(Request $request, string $category, string $post)
    {
        $categoryPage = $this->getCategory($category);
        $postPage     = $this->getPost($post, $categoryPage, ownedBy: Auth::id());

        $validated = $request->validate([
            'title'   => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:100000'],
        ]);

        $postPage->update([
            'title_en'   => $validated['title'],
            'content_en' => $validated['content'],
            // Sau khi edit lại cần duyệt lại
            'status'     => StaticPage::STATUS_PENDING,
        ]);

        return redirect()
            ->route('pages.forum.category', $category)
            ->with('success', app()->getLocale() === 'vi'
                ? 'Bài viết đã cập nhật, đang chờ duyệt lại.'
                : 'Post updated and pending re-approval.');
    }

    // DELETE /forum/{category}/{post}
    public function destroy(string $category, string $post)
    {
        $categoryPage = $this->getCategory($category);
        $postPage     = $this->getPost($post, $categoryPage, ownedBy: Auth::id());

        $postPage->delete();

        return redirect()
            ->route('pages.forum.category', $category)
            ->with('success', app()->getLocale() === 'vi' ? 'Đã xoá bài viết.' : 'Post deleted.');
    }

    // ----------------------------------------------------------------- helpers

    private function getCategory(string $slug): StaticPage
    {
        $page = StaticPage::active()
            ->where('page_type', 'forum_category')
            ->where('slug', $slug)
            ->firstOrFail();

        return $page;
    }

    private function getPost(string $slug, StaticPage $category, ?int $ownedBy = null): StaticPage
    {
        $query = StaticPage::where('page_type', 'forum_post')
            ->where('slug', $slug)
            ->where('parent_id', $category->id);

        if ($ownedBy !== null) {
            $query->where('user_id', $ownedBy);
        }

        $page = $query->firstOrFail();

        return $page;
    }

    private function uniqueSlug(string $base): string
    {
        $slug = $base ?: Str::random(8);
        $i    = 0;

        while (StaticPage::where('slug', $slug)->exists()) {
            $i++;
            $slug = $base . '-' . $i;
        }

        return $slug;
    }
}
