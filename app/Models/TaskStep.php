<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskStep extends Model
{
    protected $fillable = [
        'task_id',
        'title',
        'is_completed',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

}
