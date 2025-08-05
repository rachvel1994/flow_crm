<?php

namespace App\Observers;

use App\Models\Task;
use App\Models\TaskStatus;
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


    public function updated(Task $task): void
    {
        $dirty = $task->getDirty();
        $original = $task->getOriginal();

        $changes = [];
        $oldValues = [];

        foreach ($dirty as $key => $newValue) {
            $oldValue = $original[$key] ?? null;

            if ($oldValue !== $newValue) {
                if ($key === 'status_id') {
                    $oldStatus = TaskStatus::find($oldValue)?->name;
                    $newStatus = TaskStatus::find($newValue)?->name;

                    $changes['status.name'] = $newStatus;
                    $oldValues['status.name'] = $oldStatus;
                } else {
                    $changes[$key] = $newValue;
                    $oldValues[$key] = $oldValue;
                }
            }
        }

        if (! empty($changes)) {
            activity()
                ->performedOn($task)
                ->causedBy(auth()->user())
                ->withProperties([
                    'attributes' => $changes,
                    'old' => $oldValues,
                ])
                ->event('updated')
                ->log('updated');
        }
    }



}
