# Forum & FAQ API Reference

Quick reference guide for developers working with the Forum and FAQ systems.

## Table of Contents
- [Forum Models](#forum-models)
- [FAQ Models](#faq-models)
- [Common Patterns](#common-patterns)
- [Helper Methods](#helper-methods)
- [Settings](#settings)

---

## Forum Models

### ForumCategory

**Available Scopes:**
```php
ForumCategory::active()->get();  // Only active categories
```

**Relationships:**
```php
$category->posts;  // HasMany ForumPost
```

**Helper Methods:**
```php
$category->localizedTitle();            // Returns title in current locale
$category->localizedDescription();      // Returns description in current locale
$category->localizedSectionLabel();     // Returns section label in current locale
```

**Example Usage:**
```php
// Get all active categories with post counts
$categories = ForumCategory::active()
    ->withCount('posts')
    ->orderBy('sort_order')
    ->get();

// Get category by slug
$category = ForumCategory::where('slug', 'general-discussion')
    ->where('is_active', true)
    ->firstOrFail();
```

---

### ForumPost

**Available Scopes:**
```php
ForumPost::active()->get();      // Only active posts
ForumPost::approved()->get();    // Only approved posts
ForumPost::pending()->get();     // Only pending posts
ForumPost::rejected()->get();    // Only rejected posts
ForumPost::pinned()->get();      // Only pinned posts
```

**Relationships:**
```php
$post->category;    // BelongsTo ForumCategory
$post->user;        // BelongsTo User
$post->comments;    // HasMany ForumComment
```

**Helper Methods:**
```php
$post->localizedTitle();                   // Returns title in current locale
$post->localizedContent();                 // Returns content in current locale
$post->isPending();                        // bool
$post->isApproved();                       // bool
$post->isRejected();                       // bool
$post->canBeEditedBy($user);              // bool - Check if user can edit
$post->incrementViewCount();               // Increment view counter
```

**Example Usage:**
```php
// Get approved posts in a category
$posts = ForumPost::where('category_id', $categoryId)
    ->active()
    ->approved()
    ->with('user:id,name,username,avatar')
    ->orderByDesc('is_pinned')
    ->orderByDesc('created_at')
    ->paginate(20);

// Create a new post
$post = ForumPost::create([
    'category_id' => $category->id,
    'user_id' => auth()->id(),
    'slug' => $slug,
    'title_en' => $title,
    'content_en' => $content,
    'status' => 'pending',
    'is_active' => true,
]);
```

---

### ForumComment

**Relationships:**
```php
$comment->post;      // BelongsTo ForumPost
$comment->user;      // BelongsTo User
$comment->parent;    // BelongsTo ForumComment (nullable)
$comment->replies;   // HasMany ForumComment
$comment->votes;     // HasMany ForumCommentVote
```

**Helper Methods:**
```php
$comment->isReply();              // bool - Check if this is a reply
$comment->canBeDeletedBy($user);  // bool - Check if user can delete
```

**Example Usage:**
```php
// Get top-level comments with replies
$comments = $post->comments()
    ->whereNull('parent_id')
    ->with(['user', 'replies.user'])
    ->orderByDesc('score')
    ->orderByDesc('created_at')
    ->paginate(20);

// Create a comment
$comment = ForumComment::create([
    'post_id' => $post->id,
    'user_id' => auth()->id(),
    'parent_id' => $parentId, // null for top-level
    'content' => $content,
]);

// Create a reply
$reply = ForumComment::create([
    'post_id' => $post->id,
    'user_id' => auth()->id(),
    'parent_id' => $parentComment->id,
    'content' => $content,
]);
```

---

### ForumCommentVote

**Relationships:**
```php
$vote->comment;  // BelongsTo ForumComment
$vote->user;     // BelongsTo User
```

**Example Usage:**
```php
// Vote on a comment
$vote = ForumCommentVote::create([
    'comment_id' => $comment->id,
    'user_id' => auth()->id(),
    'value' => 1, // 1 for upvote, -1 for downvote
]);

// Check if user already voted
$existingVote = ForumCommentVote::where('comment_id', $comment->id)
    ->where('user_id', $userId)
    ->first();

// Update score
$comment->increment('score', 1);
```

---

### ForumSetting

**Static Methods:**
```php
ForumSetting::get('key', 'default');    // Get setting value
ForumSetting::set('key', 'value');      // Set setting value
ForumSetting::has('key');               // Check if key exists
ForumSetting::forget('key');            // Delete setting
ForumSetting::all();                    // Get all as key-value array
```

**Example Usage:**
```php
// Get settings
$autoApprove = ForumSetting::get('auto_approve_posts', false);
$postsPerPage = ForumSetting::get('posts_per_page', 20);

// Update settings
ForumSetting::set('auto_approve_posts', true);
ForumSetting::set('posts_per_page', 30);

// Get all settings
$settings = ForumSetting::all();
```

---

## FAQ Models

### FaqCategory

**Available Scopes:**
```php
FaqCategory::active()->get();  // Only active categories
```

**Relationships:**
```php
$category->articles;  // HasMany FaqArticle
```

**Helper Methods:**
```php
$category->localizedTitle();        // Returns title in current locale
$category->localizedDescription();  // Returns description in current locale
```

**Example Usage:**
```php
// Get all active categories with article counts
$categories = FaqCategory::active()
    ->withCount('articles')
    ->orderBy('sort_order')
    ->get();
```

---

### FaqArticle

**Available Scopes:**
```php
FaqArticle::active()->get();   // Only active articles
FaqArticle::pinned()->get();   // Only pinned articles
```

**Relationships:**
```php
$article->category;   // BelongsTo FaqCategory
$article->comments;   // HasMany FaqComment
```

**Helper Methods:**
```php
$article->localizedTitle();         // Returns title in current locale
$article->localizedContent();       // Returns content in current locale
$article->incrementViewCount();     // Increment view counter
```

**Example Usage:**
```php
// Get articles in a category
$articles = FaqArticle::where('category_id', $categoryId)
    ->active()
    ->orderByDesc('is_pinned')
    ->orderBy('sort_order')
    ->orderByDesc('created_at')
    ->paginate(20);

// Create an article
$article = FaqArticle::create([
    'category_id' => $category->id,
    'slug' => $slug,
    'title_en' => $title,
    'content_en' => $content,
    'is_pinned' => false,
    'comments_enabled' => true,
    'is_active' => true,
    'sort_order' => 0,
]);
```

---

### FaqComment

**Relationships:**
```php
$comment->article;   // BelongsTo FaqArticle
$comment->user;      // BelongsTo User
$comment->parent;    // BelongsTo FaqComment (nullable)
$comment->replies;   // HasMany FaqComment
$comment->votes;     // HasMany FaqCommentVote
```

**Helper Methods:**
```php
$comment->isReply();              // bool
$comment->canBeDeletedBy($user);  // bool
```

**Example Usage:**
```php
// Get comments for an article
$comments = $article->comments()
    ->whereNull('parent_id')
    ->with(['user', 'replies.user'])
    ->orderByDesc('score')
    ->orderByDesc('created_at')
    ->paginate(20);
```

---

### FaqCommentVote

Similar to `ForumCommentVote`. See Forum section above.

---

### FaqSetting

Similar to `ForumSetting`. See Forum section above.

---

## Common Patterns

### 1. Check User Permissions

```php
// Check if user can edit a post
if ($post->canBeEditedBy(auth()->user())) {
    // Allow edit
}

// Check if user can delete a comment
if ($comment->canBeDeletedBy(auth()->user())) {
    // Allow delete
}
```

### 2. Handle Bilingual Content

```php
// In views
{{ $post->localizedTitle() }}
{!! $post->localizedContent() !!}

// In controllers
$locale = app()->getLocale();
$title = $locale === 'vi' ? $post->title_vi : $post->title_en;
```

### 3. Increment Counters

```php
// When creating a comment
$post->increment('comment_count');
if ($parentId) {
    $parentComment->increment('reply_count');
}

// When deleting a comment
$post->decrement('comment_count', 1 + $comment->replies()->count());
if ($comment->parent_id) {
    $parentComment->decrement('reply_count');
}

// When viewing
$post->incrementViewCount();
// or
$article->incrementViewCount();
```

### 4. Vote on Comments

```php
$userId = auth()->id();
$value = 1; // or -1

$existingVote = ForumCommentVote::where('comment_id', $comment->id)
    ->where('user_id', $userId)
    ->first();

if ($existingVote) {
    if ($existingVote->value === $value) {
        // Remove vote
        $existingVote->delete();
        $comment->decrement('score', $value);
    } else {
        // Change vote
        $existingVote->update(['value' => $value]);
        $comment->increment('score', $value * 2); // Remove old + add new
    }
} else {
    // New vote
    ForumCommentVote::create([
        'comment_id' => $comment->id,
        'user_id' => $userId,
        'value' => $value,
    ]);
    $comment->increment('score', $value);
}
```

### 5. Approval Workflow

```php
// Submit post for approval
$post = ForumPost::create([
    'status' => 'pending',
    // ... other fields
]);

// Admin approves
$post->update(['status' => 'approved']);

// Admin rejects
$post->update(['status' => 'rejected']);

// After user edits, reset to pending
$post->update([
    'title_en' => $newTitle,
    'content_en' => $newContent,
    'status' => 'pending', // Requires re-approval
]);
```

### 6. Query Optimization

```php
// Eager load relationships
$posts = ForumPost::with(['user:id,name,username,avatar', 'category:id,slug,title_en,title_vi'])
    ->approved()
    ->paginate(20);

// Use select to load only needed columns
$categories = ForumCategory::select('id', 'slug', 'title_en', 'title_vi', 'icon', 'sort_order')
    ->active()
    ->get();

// Count relationships
$categories = ForumCategory::withCount(['posts' => function ($query) {
    $query->where('is_active', true)->where('status', 'approved');
}])->get();
```

---

## Helper Methods

### Model Helper Methods Summary

**ForumPost / FaqArticle:**
- `localizedTitle(?string $locale = null): string`
- `localizedContent(?string $locale = null): string`
- `incrementViewCount(): void`

**ForumPost specific:**
- `isPending(): bool`
- `isApproved(): bool`
- `isRejected(): bool`
- `canBeEditedBy(?User $user): bool`

**ForumCategory / FaqCategory:**
- `localizedTitle(?string $locale = null): string`
- `localizedDescription(?string $locale = null): string`

**ForumCategory specific:**
- `localizedSectionLabel(?string $locale = null): string`

**ForumComment / FaqComment:**
- `isReply(): bool`
- `canBeDeletedBy(?User $user): bool`

---

## Settings

### Forum Settings Keys

- `auto_approve_posts` (boolean) - Auto-approve new posts
- `allow_guest_view` (boolean) - Allow guests to view forum
- `posts_per_page` (integer) - Posts per page (5-100)
- `comments_per_page` (integer) - Comments per page (5-100)

### FAQ Settings Keys

- `allow_guest_view` (boolean) - Allow guests to view FAQ
- `enable_comments` (boolean) - Enable comments globally
- `articles_per_page` (integer) - Articles per page (5-100)
- `comments_per_page` (integer) - Comments per page (5-100)

---

## Code Examples

### Complete Post Creation Flow

```php
use App\Models\ForumCategory;
use App\Models\ForumPost;
use Illuminate\Support\Str;

public function store(Request $request, string $categorySlug)
{
    // Get category
    $category = ForumCategory::where('slug', $categorySlug)
        ->where('is_active', true)
        ->firstOrFail();
    
    // Validate
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'content' => 'required|string|max:100000',
    ]);
    
    // Generate unique slug
    $baseSlug = Str::slug($validated['title']);
    $slug = $baseSlug;
    $i = 0;
    while (ForumPost::where('category_id', $category->id)->where('slug', $slug)->exists()) {
        $i++;
        $slug = $baseSlug . '-' . $i;
    }
    
    // Create post
    $post = ForumPost::create([
        'category_id' => $category->id,
        'user_id' => auth()->id(),
        'slug' => $slug,
        'title_en' => $validated['title'],
        'content_en' => $validated['content'],
        'status' => 'pending',
        'is_active' => true,
    ]);
    
    return redirect()
        ->route('forum.category', $category->slug)
        ->with('success', 'Post submitted for approval');
}
```

### Complete Comment with Vote Flow

```php
use App\Models\ForumComment;
use App\Models\ForumCommentVote;

// Create comment
public function storeComment(Request $request, ForumPost $post)
{
    $validated = $request->validate([
        'content' => 'required|string|max:10000',
        'parent_id' => 'nullable|exists:forum_comments,id',
    ]);
    
    $comment = ForumComment::create([
        'post_id' => $post->id,
        'user_id' => auth()->id(),
        'parent_id' => $validated['parent_id'] ?? null,
        'content' => $validated['content'],
    ]);
    
    // Update counters
    $post->increment('comment_count');
    if ($validated['parent_id']) {
        ForumComment::find($validated['parent_id'])->increment('reply_count');
    }
    
    return back()->with('success', 'Comment posted');
}

// Vote on comment
public function voteComment(Request $request, ForumComment $comment)
{
    $value = (int) $request->input('value'); // 1 or -1
    $userId = auth()->id();
    
    $existingVote = ForumCommentVote::where('comment_id', $comment->id)
        ->where('user_id', $userId)
        ->first();
    
    if ($existingVote) {
        if ($existingVote->value === $value) {
            $existingVote->delete();
            $comment->decrement('score', $value);
        } else {
            $existingVote->update(['value' => $value]);
            $comment->increment('score', $value * 2);
        }
    } else {
        ForumCommentVote::create([
            'comment_id' => $comment->id,
            'user_id' => $userId,
            'value' => $value,
        ]);
        $comment->increment('score', $value);
    }
    
    return response()->json([
        'success' => true,
        'score' => $comment->fresh()->score,
    ]);
}
```

---

**Last Updated:** June 9, 2026
