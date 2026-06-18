<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamMember extends Model
{
    protected $fillable = [
        'team_id', 'user_id', 'role', 'status',
        'requested_by', 'approved_by', 'approved_at', 'note',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    const ROLES = [
        'leader' => 'Trưởng nhóm',
        'admin'  => 'Admin',
        'editor' => 'Biên tập',
        'member' => 'Thành viên',
    ];

    public function isPending(): bool   { return $this->status === 'pending'; }
    public function isApproved(): bool  { return $this->status === 'approved'; }
    public function isRejected(): bool  { return $this->status === 'rejected'; }
    public function roleLabel(): string { return self::ROLES[$this->role] ?? $this->role; }
}
