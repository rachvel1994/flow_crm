<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Noxo\FilamentActivityLog\Extensions\LogEditRecord;

class EditCategory extends EditRecord
{
    use LogEditRecord;

    protected static string $resource = CategoryResource::class;

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
