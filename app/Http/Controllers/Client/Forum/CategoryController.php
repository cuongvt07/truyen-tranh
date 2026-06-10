<?php

namespace App\Http\Controllers\Client\Forum;

use App\Http\Controllers\Controller;
use App\Models\ForumCategory;
use App\Models\ForumPost;

class CategoryController extends Controller
{
    /**
     * Display Forum index with all categories
     */
    public function index()
    {
        $categories = ForumCategory::active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->withCount(['posts' => function ($query) {
                $query->where('is_active', true)
                    ->where('status', 'approved');
            }])
            ->get();

        // Group categories by section label
        $sections = $categories->groupBy(function ($category) {
            return $category->localizedSectionLabel() ?: '__none__';
        });

        // Get latest post for each category
        $latestPosts = [];
        foreach ($categories as $category) {
            $latestPost = ForumPost::where('category_id', $category->id)
                ->where('is_active', true)
                ->where('status', 'approved')
                ->with('user:id,name,username,avatar')
                ->orderByDesc('created_at')
                ->first();
            
            if ($latestPost) {
                $latestPosts[$category->id] = $latestPost;
            }
        }

        return view('client.forum.index', compact('categories', 'sections', 'latestPosts'));
    }

    /**
     * Display posts in a specific category
     */
    public function show(string $slug)
    {
        $category = ForumCategory::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $query = ForumPost::where('category_id', $category->id)
            ->where('is_active', true)
            ->with('user:id,name,username,avatar');

        // Show pending posts only to their authors and admins
        if (auth()->check() && auth()->user()->is_admin) {
            // Admins see all posts
        } else {
            $query->where(function ($q) {
                $q->where('status', 'approved')
                    ->orWhere(function ($q2) {
                        $q2->where('status', 'pending')
                            ->where('user_id', auth()->id());
                    });
            });
        }

        $posts = $query->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('client.forum.category', compact('category', 'posts'));
    }
}
