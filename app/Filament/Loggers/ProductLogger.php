<?php

namespace App\Filament\Loggers;

use App\Models\Product;
use App\Filament\Resources\ProductResource;
use Illuminate\Contracts\Support\Htmlable;
use Noxo\FilamentActivityLog\Loggers\Logger;
use Noxo\FilamentActivityLog\ResourceLogger\Field;
use Noxo\FilamentActivityLog\ResourceLogger\RelationManager;
use Noxo\FilamentActivityLog\ResourceLogger\ResourceLogger;

class ProductLogger extends Logger
{
    public static ?string $model = Product::class;

    public static function getLabel(): string | Htmlable | null
    {
        return ProductResource::getModelLabel();
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
