<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentConversationMessage extends Model
{
    use HasFactory;

    #[Fillable(['conversation_id', 'role', 'content', 'metadata'])]
    protected $fillable = ['conversation_id', 'role', 'content', 'metadata'];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AgentConversation::class);
    }

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }
}
