<?php

namespace App\Filament\Resources\TaskStatusResource\Pages;

use App\Filament\Resources\TaskStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Noxo\FilamentActivityLog\Extensions\LogEditRecord;

class EditTaskStatus extends EditRecord
{
    use LogEditRecord;

    protected static string $resource = TaskStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterCreate(): void
    {
        $this->record->visibleRoles()->sync($this->form->getState()['visible_roles'] ?? []);
        $this->record->onlyAdminMoveRoles()->sync($this->form->getState()['only_admin_move_roles'] ?? []);
        $this->record->canMoveBackRoles()->sync($this->form->getState()['can_move_back_roles'] ?? []);
    }

    protected function afterSave(): void
    {
        $this->afterCreate();
        $this->logRecordAfter($this->record);
    }

    public function beforeValidate(): void
    {
        $this->logRecordBefore($this->record);
    }
}
