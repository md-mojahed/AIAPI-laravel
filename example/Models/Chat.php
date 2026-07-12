<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chat extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'summary',
        'model',
        'messages_count',
        'total_tokens',
    ];

    protected $casts = [
        'messages_count' => 'integer',
        'total_tokens'   => 'integer',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class)->oldest();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
