<?php

namespace App\Filament\Resources\TaskStatusResource\Pages;

use App\Filament\Resources\TaskStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Noxo\FilamentActivityLog\Extensions\LogCreateRecord;

class CreateTaskStatus extends CreateRecord
{
    use LogCreateRecord;

    protected static string $resource = TaskStatusResource::class;

    protected function afterCreate(): void
    {
        $this->record->visibleRoles()->sync($this->form->getState()['visible_roles'] ?? []);
        $this->record->onlyAdminMoveRoles()->sync($this->form->getState()['only_admin_move_roles'] ?? []);
        $this->record->canMoveBackRoles()->sync($this->form->getState()['can_move_back_roles'] ?? []);
        $this->logRecordCreated($this->record);
    }

    protected function afterSave(): void
    {
        $this->afterCreate();
    }
}
