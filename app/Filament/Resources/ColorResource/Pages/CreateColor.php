<?php

namespace App\Filament\Resources\ColorResource\Pages;

use App\Filament\Resources\ColorResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Noxo\FilamentActivityLog\Extensions\LogCreateRecord;

class CreateColor extends CreateRecord
{
    use LogCreateRecord;

    protected static string $resource = ColorResource::class;

    public function afterCreate(): void
    {
        $this->logRecordCreated($this->record);
    }
}
