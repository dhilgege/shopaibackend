<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AgentConversation extends Model
{
    use HasFactory;

    #[Fillable(['user_id', 'title', 'metadata'])]
    protected $fillable = ['user_id', 'title', 'metadata'];

    public function messages(): HasMany
    {
        return $this->hasMany(AgentConversationMessage::class);
    }

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
