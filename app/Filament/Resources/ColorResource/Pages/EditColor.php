<?php

namespace App\Filament\Resources\ColorResource\Pages;

use App\Filament\Resources\ColorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Noxo\FilamentActivityLog\Extensions\LogEditRecord;

class EditColor extends EditRecord
{
    use LogEditRecord;

    protected static string $resource = ColorResource::class;

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
