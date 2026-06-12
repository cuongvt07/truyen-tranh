# Forum & FAQ Migration Guide

This guide explains how to complete the migration from the legacy StaticPage-based system to the new dedicated Forum and FAQ systems.

## Step 1: Run Migrations

Make sure all migrations are run:

```bash
php artisan migrate
```

This will create the following tables:
- `forum_categories`, `forum_posts`, `forum_comments`, `forum_comment_votes`, `forum_settings`
- `faq_categories`, `faq_articles`, `faq_comments`, `faq_comment_votes`, `faq_settings`

## Step 2: Migrate Existing Data (if needed)

If you have existing forum/FAQ data in the `static_pages` table, check if the migration file exists:

```bash
# Check if this migration exists and was run
database/migrations/2026_06_09_120002_migrate_data_from_static_pages_to_forum_faq.php
```

If it doesn't exist or wasn't run, you may need to create a custom migration script to move data from `static_pages` to the new tables.

## Step 3: Update Routes

Replace the old routes in `routes/web.php`:

### Forum Routes

**Replace these lines:**
```php
Route::get('/forum', [App\Http\Controllers\Client\PageController::class, 'forum'])->name('pages.forum');
Route::get('/forum/{category}/{post}', [App\Http\Controllers\Client\PageController::class, 'forumPost'])
    ->name('pages.forum.post');
Route::get('/forum/{category}', [App\Http\Controllers\Client\PageController::class, 'forumCategory'])
    ->name('pages.forum.category');

// Old forum post CRUD
Route::get('/forum/{category}/new-post', [App\Http\Controllers\Client\ForumPostController::class, 'create'])->name('forum.posts.create');
Route::post('/forum/{category}/new-post', [App\Http\Controllers\Client\ForumPostController::class, 'store'])->name('forum.posts.store');
Route::get('/forum/{category}/{post}/edit', [App\Http\Controllers\Client\ForumPostController::class, 'edit'])->name('forum.posts.edit');
Route::patch('/forum/{category}/{post}', [App\Http\Controllers\Client\ForumPostController::class, 'update'])->name('forum.posts.update');
Route::delete('/forum/{category}/{post}', [App\Http\Controllers\Client\ForumPostController::class, 'destroy'])->name('forum.posts.destroy');
```

**With these new routes:**
```php
use App\Http\Controllers\Client\Forum\CategoryController as ForumCategoryController;
use App\Http\Controllers\Client\Forum\PostController as ForumPostController;
use App\Http\Controllers\Client\Forum\CommentController as ForumCommentController;

// Public forum routes
Route::get('/forum', [ForumCategoryController::class, 'index'])->name('forum.index');
Route::get('/forum/{category}', [ForumCategoryController::class, 'show'])->name('forum.category');
Route::get('/forum/{category}/{post}', [ForumPostController::class, 'show'])->name('forum.post');

// Authenticated forum routes
Route::middleware('auth')->group(function () {
    // Post management
    Route::get('/forum/{category}/new-post', [ForumPostController::class, 'create'])->name('forum.posts.create');
    Route::post('/forum/{category}/new-post', [ForumPostController::class, 'store'])->name('forum.posts.store');
    Route::get('/forum/{category}/{post}/edit', [ForumPostController::class, 'edit'])->name('forum.posts.edit');
    Route::patch('/forum/{category}/{post}', [ForumPostController::class, 'update'])->name('forum.posts.update');
    Route::delete('/forum/{category}/{post}', [ForumPostController::class, 'destroy'])->name('forum.posts.destroy');
    
    // Comment management
    Route::post('/forum/posts/{post}/comments', [ForumCommentController::class, 'store'])->name('forum.comments.store');
    Route::delete('/forum/comments/{comment}', [ForumCommentController::class, 'destroy'])->name('forum.comments.destroy');
    Route::post('/forum/comments/{comment}/vote', [ForumCommentController::class, 'vote'])->name('forum.comments.vote');
});
```

### FAQ Routes

**Replace these lines:**
```php
Route::get('/faq', [App\Http\Controllers\Client\PageController::class, 'faq'])->name('pages.faq');
Route::get('/faq/{category}/{article}', [App\Http\Controllers\Client\PageController::class, 'faqArticle'])
    ->name('pages.faq.article');
Route::get('/faq/{topic}', [App\Http\Controllers\Client\PageController::class, 'faqTopic'])
    ->name('pages.faq.topic');
```

**With these new routes:**
```php
use App\Http\Controllers\Client\Faq\CategoryController as FaqCategoryController;
use App\Http\Controllers\Client\Faq\ArticleController as FaqArticleController;
use App\Http\Controllers\Client\Faq\CommentController as FaqCommentController;

// Public FAQ routes
Route::get('/faq', [FaqCategoryController::class, 'index'])->name('faq.index');
Route::get('/faq/{category}', [FaqCategoryController::class, 'show'])->name('faq.category');
Route::get('/faq/{category}/{article}', [FaqArticleController::class, 'show'])->name('faq.article');

// Authenticated FAQ comment routes
Route::middleware('auth')->group(function () {
    Route::post('/faq/articles/{article}/comments', [FaqCommentController::class, 'store'])->name('faq.comments.store');
    Route::delete('/faq/comments/{comment}', [FaqCommentController::class, 'destroy'])->name('faq.comments.destroy');
    Route::post('/faq/comments/{comment}/vote', [FaqCommentController::class, 'vote'])->name('faq.comments.vote');
});
```

## Step 4: Update Views

You'll need to create or update the following view files:

### Forum Views

Create these views in `resources/views/client/forum/`:

1. **index.blade.php** - Forum homepage showing all categories
2. **category.blade.php** - List of posts in a category
3. **post.blade.php** - Individual post view with comments
4. **post-form.blade.php** - Form for creating/editing posts

### FAQ Views

Create these views in `resources/views/client/faq/`:

1. **index.blade.php** - FAQ homepage showing all categories
2. **category.blade.php** - List of articles in a category
3. **article.blade.php** - Individual article view with comments

### Example View Structure

**Forum Post View (forum/post.blade.php):**
```blade
@extends('layouts.client')

@section('content')
<div class="forum-post">
    <h1>{{ $post->localizedTitle() }}</h1>
    
    <div class="post-meta">
        <span>{{ $post->user->name }}</span>
        <span>{{ $post->created_at->diffForHumans() }}</span>
        <span>{{ $post->view_count }} views</span>
    </div>
    
    <div class="post-content">
        {!! $post->localizedContent() !!}
    </div>
    
    @auth
        @if($post->canBeEditedBy(auth()->user()))
            <a href="{{ route('forum.posts.edit', [$category->slug, $post->slug]) }}">Edit</a>
            <form method="POST" action="{{ route('forum.posts.destroy', [$category->slug, $post->slug]) }}">
                @csrf
                @method('DELETE')
                <button type="submit">Delete</button>
            </form>
        @endif
    @endauth
    
    <!-- Comments section -->
    <div class="comments">
        @foreach($comments as $comment)
            @include('partials.forum-comment', ['comment' => $comment])
        @endforeach
        
        {{ $comments->links() }}
    </div>
    
    @auth
        @unless($post->is_locked)
            <form method="POST" action="{{ route('forum.comments.store', $post) }}">
                @csrf
                <textarea name="content" required></textarea>
                <button type="submit">Post Comment</button>
            </form>
        @endunless
    @endauth
</div>
@endsection
```

## Step 5: Update Route Names in Existing Code

Search for old route names and update them:

**Old route names:**
- `pages.forum` → `forum.index`
- `pages.forum.category` → `forum.category`
- `pages.forum.post` → `forum.post`
- `pages.faq` → `faq.index`
- `pages.faq.article` → `faq.article`

**Search and replace in all Blade files:**
```bash
# Find all references to old routes
grep -r "route('pages.forum" resources/views/
grep -r "route('pages.faq" resources/views/
```

## Step 6: Test Everything

1. **Forum Testing:**
   - [ ] Can view forum index
   - [ ] Can view category page
   - [ ] Can create new post (requires approval)
   - [ ] Can view post
   - [ ] Can edit own post
   - [ ] Can delete own post
   - [ ] Can post comment
   - [ ] Can reply to comment
   - [ ] Can vote on comment
   - [ ] Can delete own comment
   - [ ] Admin can approve/reject posts
   - [ ] Admin can delete any comment

2. **FAQ Testing:**
   - [ ] Can view FAQ index
   - [ ] Can view FAQ category
   - [ ] Can view FAQ article
   - [ ] Can post comment (if enabled)
   - [ ] Can vote on comment
   - [ ] Can delete own comment
   - [ ] View count increments

3. **Admin Testing:**
   - [ ] Can manage forum categories
   - [ ] Can manage forum posts
   - [ ] Can moderate forum comments
   - [ ] Can configure forum settings
   - [ ] Can manage FAQ categories
   - [ ] Can manage FAQ articles
   - [ ] Can moderate FAQ comments
   - [ ] Can configure FAQ settings

## Step 7: Clean Up Legacy Code (Optional)

Once everything is working with the new system:

1. **Remove old controller methods:**
   - Remove `forum()`, `forumCategory()`, `forumPost()` from `Client\PageController`
   - Remove `faq()`, `faqTopic()`, `faqArticle()` from `Client\PageController`
   - Delete `Client\ForumPostController` (the old one using StaticPage)

2. **Remove old views:**
   - Archive or delete old forum/FAQ views that used StaticPage

3. **Update documentation:**
   - Update any developer documentation
   - Update API documentation if applicable

## Rollback Plan

If you need to rollback to the old system:

1. Keep the old routes and controllers commented out
2. Don't delete old views immediately
3. Test thoroughly before removing legacy code

To rollback:
```bash
# Rollback the migrations
php artisan migrate:rollback --step=3

# Restore old routes in routes/web.php
# Re-enable old controllers
```

## Common Issues and Solutions

### Issue: 404 Not Found on Forum Pages

**Solution:** Make sure route model binding works correctly. Check that:
- Category slugs exist in `forum_categories` table
- Post slugs exist in `forum_posts` table
- Routes are registered correctly

### Issue: Comments Not Showing

**Solution:** Check:
- Post is not locked (`is_locked = false`)
- Comments are properly loaded with `whereNull('parent_id')`
- View is including the comment partial correctly

### Issue: Vote Button Not Working

**Solution:** 
- Check AJAX endpoint is correct
- Ensure CSRF token is included
- Check user is authenticated

### Issue: Permission Denied

**Solution:**
- Check authorization in UpdatePostRequest
- Verify `canBeEditedBy()` method logic
- Check middleware is applied correctly

## Support

If you encounter issues during migration:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Enable query logging in AppServiceProvider
3. Use `php artisan route:list` to verify routes
4. Check database migrations were successful

---

**Last Updated:** June 9, 2026
