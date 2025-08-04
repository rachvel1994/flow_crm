<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Noxo\FilamentActivityLog\Extensions\LogEditRecord;

class EditProduct extends EditRecord
{
    use LogEditRecord;

    protected static string $resource = ProductResource::class;

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
