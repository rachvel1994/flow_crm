<?php

namespace App\Filament\Loggers;

use App\Filament\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Contracts\Support\Htmlable;
use Noxo\FilamentActivityLog\Loggers\Logger;
use Noxo\FilamentActivityLog\ResourceLogger\Field;
use Noxo\FilamentActivityLog\ResourceLogger\RelationManager;
use Noxo\FilamentActivityLog\ResourceLogger\ResourceLogger;

class TaskLogger extends Logger
{
    public static ?string $model = Task::class;

    public static function getLabel(): string|Htmlable|null
    {
        return TaskResource::getModelLabel();
    }

    public static function resource(ResourceLogger $logger): ResourceLogger
    {
        return $logger
            ->fields([
                Field::make('title')->label('დასახელება'),
                Field::make('price')->label('ფასი'),
                Field::make('priority')->label('პრიორიტეტი'),
                Field::make('started_at')->label('დაწყების დრო'),
                Field::make('deadline')->label('დედლაინი'),
                Field::make('status.name')->label('ეტაპი'),
                Field::make('code')->label('კოდი'),
                Field::make('description')->label('დამატებითი ინფორმაცია'),
            ])
            ->relationManagers([
                RelationManager::make('steps')->label('ნაბიჯები'),
                RelationManager::make('assignees')->label('დავალებული პირები'),
            ]);
    }
}
