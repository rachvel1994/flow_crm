<?php

namespace App\Filament\Loggers;

use App\Models\TaskStatus;
use App\Filament\Resources\TaskStatusResource;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Noxo\FilamentActivityLog\Loggers\Logger;
use Noxo\FilamentActivityLog\ResourceLogger\Field;
use Noxo\FilamentActivityLog\ResourceLogger\RelationManager;
use Noxo\FilamentActivityLog\ResourceLogger\ResourceLogger;

class TaskStatusLogger extends Logger
{
    public static ?string $model = TaskStatus::class;

    public static function getLabel(): string|Htmlable|null
    {
        return TaskStatusResource::getModelLabel();
    }

    public static function resource(ResourceLogger $logger): ResourceLogger
    {
        return $logger
            ->fields([
                Field::make('name')->label('სვეტის სახელი'),
                Field::make('color')->label('ფერი'),
                Field::make('is_active')->label('სტატუსი')->boolean(),
                Field::make('send_sms')->label('SMS გაგზავნა')->boolean(),
                Field::make('send_email')->label('Email გაგზავნა')->boolean(),
                Field::make('message')->label('შეტყობინება')->formatStateUsing(fn($state) => new HtmlString($state)),
            ])
            ->relationManagers([
                RelationManager::make('visibleRoles')->label('სვეტის ნახვის უფლება'),
                RelationManager::make('onlyAdminMoveRoles')->label('წინ გადასვლის უფლება'),
                RelationManager::make('canMoveBackRoles')->label('უკან დაბრუნების უფლება'),
                RelationManager::make('canAddTaskByRole')->label('სვეტში დამატების უფლება'),
                RelationManager::make('smsReceivers')->label('სმს მიმღები'),
            ]);
    }
}
