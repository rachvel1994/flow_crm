<?php

namespace App\Filament\Loggers;

use App\Models\EmailTemplate;
use App\Filament\Resources\EmailTemplateResource;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Noxo\FilamentActivityLog\Loggers\Logger;
use Noxo\FilamentActivityLog\ResourceLogger\Field;
use Noxo\FilamentActivityLog\ResourceLogger\ResourceLogger;

class EmailTemplateLogger extends Logger
{
    public static ?string $model = EmailTemplate::class;

    public static function getLabel(): string|Htmlable|null
    {
        return EmailTemplateResource::getModelLabel();
    }

    public static function resource(ResourceLogger $logger): ResourceLogger
    {
        return $logger
            ->fields([
                Field::make('name')->label('სახელი'),
                Field::make('message')->label('ტექსტი')->formatStateUsing(fn($state) => new HtmlString($state)),
                Field::make('is_active')->label('სტატუსი')->boolean(),
            ]);
    }
}
