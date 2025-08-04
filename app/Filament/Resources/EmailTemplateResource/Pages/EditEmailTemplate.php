<?php

namespace App\Filament\Resources\EmailTemplateResource\Pages;

use App\Filament\Resources\EmailTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Noxo\FilamentActivityLog\Extensions\LogEditRecord;

class EditEmailTemplate extends EditRecord
{
    use LogEditRecord;

    protected static string $resource = EmailTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    public function beforeValidate(): void
    {
        $this->logRecordBefore($this->record);
    }

    public function afterSave(): void
    {
        $this->logRecordAfter($this->record);
    }
}
