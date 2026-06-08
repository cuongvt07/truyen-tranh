<?php

namespace App\Models;

use App\Scopes\ApprovedArticleScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'article_id', 'parent_id', 'content', 'score'];

    protected function getCreatedAtTextAttribute()
    {
        return $this->created_at->diffForHumans();
    }
    protected function getUpdatedAtTextAttribute()
    {
        return $this->updated_at->diffForHumans();
    }

    public function article(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Article::class, 'article_id', 'id');
    }
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    // ----- Reply lồng nhau -----
    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')->orderBy('created_at');
    }

    // ----- Vote / Report -----
    public function votes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CommentVote::class);
    }

    public function reports(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CommentReport::class);
    }

    /** Phiếu vote của user đang đăng nhập (1, -1, hoặc null) */
    public function getMyVoteAttribute(): ?int
    {
        if (!Auth::check()) {
            return null;
        }
        if ($this->relationLoaded('votes')) {
            return $this->votes->firstWhere('user_id', Auth::id())?->value;
        }
        return $this->votes()->where('user_id', Auth::id())->value('value');
    }

    /** Người dùng có quyền xoá comment này không (chủ sở hữu hoặc admin) */
    public function canBeDeletedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }
        return $user->id === $this->user_id || $user->is_admin;
    }

    /** Tính lại điểm score cache từ bảng votes */
    public function recalcScore(): void
    {
        $this->score = (int) $this->votes()->sum('value');
        $this->saveQuietly();
    }
}
