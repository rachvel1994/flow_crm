<?php

namespace App\Filament\Loggers;

use App\Models\Role;
use App\Filament\Resources\RoleResource;
use Illuminate\Contracts\Support\Htmlable;
use Noxo\FilamentActivityLog\Loggers\Logger;
use Noxo\FilamentActivityLog\ResourceLogger\Field;
use Noxo\FilamentActivityLog\ResourceLogger\RelationManager;
use Noxo\FilamentActivityLog\ResourceLogger\ResourceLogger;

class RoleLogger extends Logger
{
    public static ?string $model = Role::class;

    public static function getLabel(): string | Htmlable | null
    {
        return RoleResource::getModelLabel();
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
