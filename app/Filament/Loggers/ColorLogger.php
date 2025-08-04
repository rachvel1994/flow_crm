<?php

namespace App\Filament\Loggers;

use App\Models\Color;
use App\Filament\Resources\ColorResource;
use Illuminate\Contracts\Support\Htmlable;
use Noxo\FilamentActivityLog\Loggers\Logger;
use Noxo\FilamentActivityLog\ResourceLogger\Field;
use Noxo\FilamentActivityLog\ResourceLogger\RelationManager;
use Noxo\FilamentActivityLog\ResourceLogger\ResourceLogger;

class ColorLogger extends Logger
{
    public static ?string $model = Color::class;

    public static function getLabel(): string | Htmlable | null
    {
        return ColorResource::getModelLabel();
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
