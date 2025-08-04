<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Noxo\FilamentActivityLog\Extensions\LogCreateRecord;

class CreateUser extends CreateRecord
{

    use LogCreateRecord;

    protected static string $resource = UserResource::class;

    public function afterCreate(): void
    {
        $this->logRecordCreated($this->record);
    }
}
