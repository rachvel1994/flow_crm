<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class Task extends Model implements Sortable
{
    use SortableTrait;

    protected $fillable = [
        'title',
        'code',
        'location',
        'description',
        'created_by_id',
        'team_id',
        'status_id',
		'price',
        'priority',
        'started_at',
        'deadline',
        'images',
        'order_column',
        'attachments',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'deadline' => 'datetime',
        'images' => 'array',
        'attachments' => 'array',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /**
     * The team this task belongs to (tenant).
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(TaskStatus::class);
    }

    public function steps(): HasMany
    {
        return $this->hasMany(TaskStep::class, 'task_id');
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_user');
    }
}
