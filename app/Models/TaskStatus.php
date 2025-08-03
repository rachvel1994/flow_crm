<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\EloquentSortable\SortableTrait;

class TaskStatus extends Model
{
    use SortableTrait;

    protected $fillable = [
        'name',
        'team_id',
        'color',
        'is_active',
        'order_column',
        'send_sms',
        'send_email',
        'message',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function visibleRoles(): BelongsToMany
    {
        return $this->belongsToMany(\Spatie\Permission\Models\Role::class, 'role_task_status_visibility');
    }

    public function onlyAdminMoveRoles(): BelongsToMany
    {
        return $this->belongsToMany(\Spatie\Permission\Models\Role::class, 'role_task_status_admin_move');
    }

    public function canMoveBackRoles(): BelongsToMany
    {
        return $this->belongsToMany(\Spatie\Permission\Models\Role::class, 'role_task_status_can_move_back');
    }

    public function canAddTaskByRole(): BelongsToMany
    {
        return $this->belongsToMany(\Spatie\Permission\Models\Role::class, 'role_task_status_can_add_task');
    }
}

