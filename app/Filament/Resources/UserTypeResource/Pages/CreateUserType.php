<?php

namespace App\Filament\Resources\UserTypeResource\Pages;

use App\Filament\Resources\UserTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Noxo\FilamentActivityLog\Extensions\LogCreateRecord;

class CreateUserType extends CreateRecord
{
    use LogCreateRecord;

    protected static string $resource = UserTypeResource::class;

    public function afterCreate(): void
    {
        $this->logRecordCreated($this->record);
    }
}
