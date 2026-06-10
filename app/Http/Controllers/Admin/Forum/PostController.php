<?php

namespace App\Http\Controllers\Admin\Forum;

use App\Http\Controllers\Controller;
use App\Models\ForumPost;
use App\Models\ForumCategory;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $query = ForumPost::with(['category', 'user'])->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $posts = $query->paginate(30);
        $categories = ForumCategory::orderBy('sort_order')->get();
        $pendingCount = ForumPost::where('status', 'pending')->count();

        return view('admin.forum.posts.index', compact('posts', 'categories', 'pendingCount'));
    }

    public function show(ForumPost $post)
    {
        $post->load(['category', 'user', 'comments.user']);
        return view('admin.forum.posts.show', compact('post'));
    }

    public function edit(ForumPost $post)
    {
        $categories = ForumCategory::orderBy('sort_order')->get();
        return view('admin.forum.posts.edit', compact('post', 'categories'));
    }

    public function update(Request $request, ForumPost $post)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:forum_categories,id',
            'title_en' => 'required|string|max:255',
            'title_vi' => 'nullable|string|max:255',
            'content_en' => 'required|string',
            'content_vi' => 'nullable|string',
            'is_pinned' => 'nullable|boolean',
            'is_locked' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_pinned'] = $request->has('is_pinned');
        $validated['is_locked'] = $request->has('is_locked');
        $validated['is_active'] = $request->has('is_active');

        $post->update($validated);

        return redirect()->route('admin.forum.posts.index')
            ->with('success', 'Forum post updated successfully.');
    }

    public function approve(ForumPost $post)
    {
        $post->update(['status' => 'approved']);
        return redirect()->back()->with('success', 'Post approved successfully.');
    }

    public function reject(ForumPost $post)
    {
        $post->update(['status' => 'rejected']);
        return redirect()->back()->with('success', 'Post rejected successfully.');
    }

    public function destroy(ForumPost $post)
    {
        $post->delete();
        return redirect()->route('admin.forum.posts.index')
            ->with('success', 'Forum post deleted successfully.');
    }
}
