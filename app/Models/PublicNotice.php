<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublicNotice extends Model
{
    protected $fillable = [
        'title',
        'body',
        'notice_type',
        'is_important',
        'published_at',
        'expires_at',
        'attachment',
        'is_published',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_important' => 'boolean',
            'is_published' => 'boolean',
            'published_at' => 'date',
            'expires_at' => 'date',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePublished($query)
    {
        return $query
            ->where('is_published', true)
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhereDate('published_at', '<=', now('Asia/Dhaka'));
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhereDate('expires_at', '>=', now('Asia/Dhaka'));
            });
    }
}
