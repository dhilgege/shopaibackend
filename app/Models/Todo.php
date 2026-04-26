<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    use HasFactory;

    #[Fillable(['title', 'description', 'is_completed', 'due_date', 'priority', 'user_id', 'related_part_number', 'vehicle_model', 'priority_note'])]
    protected $fillable = [
        'title',
        'description',
        'is_completed',
        'due_date',
        'priority',
        'user_id',
        'related_part_number',
        'vehicle_model',
        'priority_note',
    ];

    protected function casts(): array
    {
        return [
            'is_completed' => 'boolean',
            'due_date' => 'datetime',
            'priority' => 'integer',
        ];
    }
}
