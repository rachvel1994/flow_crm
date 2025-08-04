<?php

namespace App\Filament\Loggers;

use App\Models\Category;
use App\Filament\Resources\CategoryResource;
use Illuminate\Contracts\Support\Htmlable;
use Noxo\FilamentActivityLog\Loggers\Logger;
use Noxo\FilamentActivityLog\ResourceLogger\Field;
use Noxo\FilamentActivityLog\ResourceLogger\RelationManager;
use Noxo\FilamentActivityLog\ResourceLogger\ResourceLogger;

class CategoryLogger extends Logger
{
    public static ?string $model = Category::class;

    public static function getLabel(): string | Htmlable | null
    {
        return CategoryResource::getModelLabel();
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
