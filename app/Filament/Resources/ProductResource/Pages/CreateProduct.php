<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Noxo\FilamentActivityLog\Extensions\LogCreateRecord;

class CreateProduct extends CreateRecord
{
    use LogCreateRecord;

    protected static string $resource = ProductResource::class;

    public function afterCreate(): void
    {
        $this->logRecordCreated($this->record);
    }
}
