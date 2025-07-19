<?php

namespace App\Observers;

use App\Models\Task;
use Filament\Facades\Filament;

class TaskObserver
{
    public function creating(Task $task): void
    {
        $tenant = Filament::getTenant();

        if ($tenant) {
            $task->team()->associate($tenant);

            $lastTask = Task::query()->where('team_id', $tenant->id)
                ->where('code', 'like', $tenant->name . '-%')
                ->orderByDesc('id')
                ->first();

            $nextNumber = 1;

            if ($lastTask && preg_match('/(\d+)$/', $lastTask->code, $matches)) {
                $nextNumber = (int)$matches[1] + 1;
            }

            $task->code = $tenant->name . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        }
    }
}
