<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'photo', 'description',
        'site', 'donation_text', 'donation_url',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function members()
    {
        return $this->hasMany(TeamMember::class);
    }

    public function approvedMembers()
    {
        return $this->hasMany(TeamMember::class)->where('status', 'approved');
    }

    public function pendingMembers()
    {
        return $this->hasMany(TeamMember::class)->where('status', 'pending');
    }

    public function articles()
    {
        return $this->hasMany(Article::class);
    }

    public function isLeader(int $userId): bool
    {
        return $this->user_id === $userId;
    }

    public function hasMember(int $userId): bool
    {
        return $this->approvedMembers()->where('user_id', $userId)->exists();
    }
}
