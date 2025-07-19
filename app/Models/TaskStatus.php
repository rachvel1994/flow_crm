<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskStatus extends Model
{
    protected $fillable = [
        'name',
        'team_id',
        'color',
        'is_active',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
