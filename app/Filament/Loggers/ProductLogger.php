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

    public static function getLabel(): string|Htmlable|null
    {
        return ProductResource::getModelLabel();
    }

    public static function resource(ResourceLogger $logger): ResourceLogger
    {
        return $logger
            ->fields([
                Field::make('name')->label('სახელი'),
                Field::make('sku')->label('SKU'),
                Field::make('model')->label('მოდელი'),
                Field::make('price')->label('ფასი')->money('GEL'),
                Field::make('b2b_price')->label('ბ2ბ ფასი')->money('GEL'),
                Field::make('sale_price')->label('გასაყიდი ფასი')->money('GEL'),
                Field::make('is_active')->label('სტატუსი')->boolean(),
                Field::make('category.name')->label('კატეგორია'),
                Field::make('color.name')->label('ფერი'),
                Field::make('tags')->label('თეგები'),
                Field::make('description')->label('აღწერა'),
            ])
            ->relationManagers([
                // Add relations if needed later
                RelationManager::make('team')->label('გუნდი'),
            ]);
    }
}
