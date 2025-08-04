<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Noxo\FilamentActivityLog\Extensions\LogCreateRecord;

class CreateCategory extends CreateRecord
{
    use LogCreateRecord;

    protected static string $resource = CategoryResource::class;

    public function afterCreate(): void
    {
        $this->logRecordCreated($this->record);
    }
}
