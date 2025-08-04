<?php

namespace App\Filament\Resources\UserTypeResource\Pages;

use App\Filament\Resources\UserTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Noxo\FilamentActivityLog\Extensions\LogEditRecord;

class EditUserType extends EditRecord
{
    use LogEditRecord;

    protected static string $resource = UserTypeResource::class;

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
