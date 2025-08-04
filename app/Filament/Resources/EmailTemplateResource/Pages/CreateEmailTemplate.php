<?php

namespace App\Filament\Resources\EmailTemplateResource\Pages;

use App\Filament\Resources\EmailTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Noxo\FilamentActivityLog\Extensions\LogCreateRecord;

class CreateEmailTemplate extends CreateRecord
{
    use LogCreateRecord;

    protected static string $resource = EmailTemplateResource::class;

    public function afterCreate(): void
    {
        $this->logRecordCreated($this->record);
    }
}
