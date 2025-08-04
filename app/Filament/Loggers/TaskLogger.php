<?php

namespace App\Filament\Loggers;

use App\Models\Task;
use App\Filament\Resources\TaskResource;
use Illuminate\Contracts\Support\Htmlable;
use Noxo\FilamentActivityLog\Loggers\Logger;
use Noxo\FilamentActivityLog\ResourceLogger\Field;
use Noxo\FilamentActivityLog\ResourceLogger\RelationManager;
use Noxo\FilamentActivityLog\ResourceLogger\ResourceLogger;

class TaskLogger extends Logger
{
    public static ?string $model = Task::class;

    public static function getLabel(): string | Htmlable | null
    {
        return TaskResource::getModelLabel();
    }

    public static function resource(ResourceLogger $logger): ResourceLogger
    {
        return $logger
            ->fields([
                //
            ])
            ->relationManagers([
                //
            ]);
    }
}
