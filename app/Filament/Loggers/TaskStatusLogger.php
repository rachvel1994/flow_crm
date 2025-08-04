<?php

namespace App\Filament\Loggers;

use App\Models\TaskStatus;
use App\Filament\Resources\TaskStatusResource;
use Illuminate\Contracts\Support\Htmlable;
use Noxo\FilamentActivityLog\Loggers\Logger;
use Noxo\FilamentActivityLog\ResourceLogger\Field;
use Noxo\FilamentActivityLog\ResourceLogger\RelationManager;
use Noxo\FilamentActivityLog\ResourceLogger\ResourceLogger;

class TaskStatusLogger extends Logger
{
    public static ?string $model = TaskStatus::class;

    public static function getLabel(): string | Htmlable | null
    {
        return TaskStatusResource::getModelLabel();
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
