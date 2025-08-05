<?php

namespace App\Filament\Loggers;

use App\Filament\Resources\RoleResource;
use Illuminate\Contracts\Support\Htmlable;
use Noxo\FilamentActivityLog\Loggers\Logger;
use Noxo\FilamentActivityLog\ResourceLogger\Field;
use Noxo\FilamentActivityLog\ResourceLogger\RelationManager;
use Noxo\FilamentActivityLog\ResourceLogger\ResourceLogger;
use Spatie\Permission\Models\Role;

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
                Field::make('name')->label('როლის სახელი'),
                Field::make('guard_name')->label('Guard Name'),
                Field::make('team_id')->label('გუნდი'),
            ])
            ->relationManagers([
                RelationManager::make('permissions')->label('უფლებები'),
            ]);
    }
}
