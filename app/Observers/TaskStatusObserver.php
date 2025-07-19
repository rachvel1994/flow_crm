<?php

namespace App\Observers;

use App\Models\TaskStatus;
use Filament\Facades\Filament;

class TaskStatusObserver
{
    public function creating(TaskStatus $taskStatus): void
    {
        if (auth()->hasUser()) {
            $taskStatus->team()->associate(Filament::getTenant());
        }
    }
}
