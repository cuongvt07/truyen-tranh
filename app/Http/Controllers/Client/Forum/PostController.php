<?php

namespace App\Http\Controllers\Client\Forum;

use App\Http\Controllers\Controller;
use App\Http\Requests\Forum\StorePostRequest;
use App\Http\Requests\Forum\UpdatePostRequest;
use App\Models\ForumCategory;
use App\Models\ForumPost;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['show']);
    }

    /**
     * Display a specific forum post
     */
    public function show(string $categorySlug, string $postSlug)
    {
        $category = ForumCategory::where('slug', $categorySlug)
            ->where('is_active', true)
            ->firstOrFail();

        $post = ForumPost::where('category_id', $category->id)
            ->where('slug', $postSlug)
            ->where('is_active', true)
            ->with(['user', 'category'])
            ->firstOrFail();

        // Check if user can view this post
        if (!$post->isApproved()) {
            $user = auth()->user();
            abort_unless(
                $user && ($user->id === $post->user_id || $user->is_admin),
                404
            );
        }

        $post->incrementViewCount();

        // Load comments with pagination
        $comments = $post->comments()
            ->whereNull('parent_id')
            ->where('is_hidden', false)
            ->with(['user', 'replies' => fn ($q) => $q->where('is_hidden', false)->with('user')])
            ->orderByDesc('score')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('client.forum.post', compact('category', 'post', 'comments'));
    }

    /**
     * Show form to create a new post
     */
    public function create(string $categorySlug)
    {
        $category = ForumCategory::where('slug', $categorySlug)
            ->where('is_active', true)
            ->firstOrFail();

        return view('client.forum.post-form', [
            'category' => $category,
            'post' => null,
        ]);
    }

    /**
     * Store a new forum post
     */
    public function store(StorePostRequest $request, string $categorySlug)
    {
        $category = ForumCategory::where('slug', $categorySlug)
            ->where('is_active', true)
            ->firstOrFail();

        $validated = $request->validated();
        
        // Generate unique slug
        $baseSlug = Str::slug($validated['title']);
        $slug = $this->generateUniqueSlug($baseSlug, $category->id);

        $post = ForumPost::create([
            'category_id' => $category->id,
            'user_id' => auth()->id(),
            'slug' => $slug,
            'title_en' => $validated['title'],
            'title_vi' => $validated['title_vi'] ?? null,
            'content_en' => $validated['content'],
            'content_vi' => $validated['content_vi'] ?? null,
            'status' => 'pending',
            'is_active' => true,
        ]);

        return redirect()
            ->route('forum.category', $category->slug)
            ->with('success', app()->getLocale() === 'vi'
                ? 'Bài viết đã gửi, đang chờ admin duyệt.'
                : 'Your post has been submitted and is awaiting approval.');
    }

    /**
     * Show form to edit a post
     */
    public function edit(string $categorySlug, string $postSlug)
    {
        $category = ForumCategory::where('slug', $categorySlug)
            ->where('is_active', true)
            ->firstOrFail();

        $post = ForumPost::where('category_id', $category->id)
            ->where('slug', $postSlug)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return view('client.forum.post-form', compact('category', 'post'));
    }

    /**
     * Update an existing post
     */
    public function update(UpdatePostRequest $request, string $categorySlug, string $postSlug)
    {
        $category = ForumCategory::where('slug', $categorySlug)
            ->where('is_active', true)
            ->firstOrFail();

        $post = ForumPost::where('category_id', $category->id)
            ->where('slug', $postSlug)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $validated = $request->validated();

        $post->update([
            'title_en' => $validated['title'],
            'title_vi' => $validated['title_vi'] ?? null,
            'content_en' => $validated['content'],
            'content_vi' => $validated['content_vi'] ?? null,
            'status' => 'pending', // Requires re-approval after edit
        ]);

        return redirect()
            ->route('forum.category', $category->slug)
            ->with('success', app()->getLocale() === 'vi'
                ? 'Bài viết đã cập nhật, đang chờ duyệt lại.'
                : 'Post updated and pending re-approval.');
    }

    /**
     * Delete a post
     */
    public function destroy(string $categorySlug, string $postSlug)
    {
        $category = ForumCategory::where('slug', $categorySlug)
            ->where('is_active', true)
            ->firstOrFail();

        $post = ForumPost::where('category_id', $category->id)
            ->where('slug', $postSlug)
            ->firstOrFail();

        // Only author or admin can delete
        abort_unless(
            auth()->user() && $post->canBeEditedBy(auth()->user()),
            403
        );

        $post->delete();

        return redirect()
            ->route('forum.category', $category->slug)
            ->with('success', app()->getLocale() === 'vi' 
                ? 'Đã xoá bài viết.' 
                : 'Post deleted.');
    }

    /**
     * Generate a unique slug for a post within a category
     */
    private function generateUniqueSlug(string $baseSlug, int $categoryId): string
    {
        $slug = $baseSlug ?: Str::random(8);
        $i = 0;

        while (ForumPost::where('category_id', $categoryId)->where('slug', $slug)->exists()) {
            $i++;
            $slug = $baseSlug . '-' . $i;
        }

        return $slug;
    }
}
