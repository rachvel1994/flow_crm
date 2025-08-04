<?php

namespace App\Filament\Loggers;

use App\Models\UserType;
use App\Filament\Resources\UserTypeResource;
use Illuminate\Contracts\Support\Htmlable;
use Noxo\FilamentActivityLog\Loggers\Logger;
use Noxo\FilamentActivityLog\ResourceLogger\Field;
use Noxo\FilamentActivityLog\ResourceLogger\RelationManager;
use Noxo\FilamentActivityLog\ResourceLogger\ResourceLogger;

class UserTypeLogger extends Logger
{
    public static ?string $model = UserType::class;

    public static function getLabel(): string | Htmlable | null
    {
        return UserTypeResource::getModelLabel();
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
