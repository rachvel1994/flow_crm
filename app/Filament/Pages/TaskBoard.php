<?php

namespace App\Filament\Pages;

use App\Models\Task;
use App\Models\TaskStatus;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Mokhosh\FilamentKanban\Pages\KanbanBoard;

class TaskBoard extends KanbanBoard
{
    use HasPageShield;

    protected static string $model = Task::class;
    protected static string $recordStatusAttribute = 'status_id';
    protected bool $editModalSlideOver = true;
    protected static ?int $navigationSort = 1;

    protected string $editModalTitle = 'დავალება';
    protected string $editModalWidth = '4xl';

    public bool $disableEditModal = false;

    public function getMaxContentWidth(): MaxWidth|string|null
    {
        return 'full';
    }

    protected function statuses(): Collection
    {
        $user = Auth::user();

        $userRoleIds = $user?->roles->pluck('id')->toArray() ?? [];

        return TaskStatus::where('is_active', 1)
            ->with('visibleRoles:id') // eager-load only needed field
            ->get(['id', 'name', 'color'])
            ->map(function ($status) use ($userRoleIds) {
                $isVisible = $status->visibleRoles()
                    ->whereIn('role_id', $userRoleIds)
                    ->exists();

                return [
                    'id' => $status->id,
                    'title' => $status->name,
                    'color' => $status->color,
                    'visible' => $isVisible ? 'flex' : 'none',
                ];
            })
            ->values();
    }

    protected function records(): Collection
    {
        return Task::ordered()->get();
    }

    public function onStatusChanged(int|string $recordId, string $newStatusId, array $fromOrderedIds, array $toOrderedIds): void
    {
        $task = Task::with(['steps', 'status'])->findOrFail($recordId);

        $currentStatus = TaskStatus::with(['canMoveBackRoles', 'onlyAdminMoveRoles', 'visibleRoles'])->findOrFail($task->status_id);
        $newStatus = TaskStatus::with(['canMoveBackRoles', 'onlyAdminMoveRoles', 'visibleRoles'])->findOrFail($newStatusId);

        if (!$this->canMoveTask($task, $currentStatus, $newStatus)) {
            return;
        }

        $task->update(['status_id' => $newStatusId]);
        Task::setNewOrder($toOrderedIds);
    }

    public function onSortChanged(int|string $recordId, string $statusId, array $orderedIds): void
    {
        Task::setNewOrder($orderedIds);
    }

    private function canMoveTask(Task $task, TaskStatus $currentStatus, TaskStatus $newStatus): bool
    {
        $user = auth()->user();
        $userRoleIds = $user->roles->pluck('id')->toArray();

        if (!$task->steps->every(fn($step) => (int) $step->is_completed === 1)) {
            Notification::make()
                ->title('გთხოვთ, ყველა ნაბიჯი შესრულდეს ეტაპის შესაცვლელად.')
                ->danger()
                ->send();
            return false;
        }
        $isMovingForward = $newStatus->order_column > $currentStatus->order_column;
        $isMovingBackward = $newStatus->order_column < $currentStatus->order_column;
        $currentStatus->refresh();

        if ($isMovingBackward) {
            $allowedFromCurrent = $currentStatus->canMoveBackRoles()
                ->whereIn('role_id', $userRoleIds)
                ->exists();

            if (!($allowedFromCurrent)) {
                Notification::make()
                    ->danger()
                    ->title('უკან დაბრუნება შეზღუდულია.')
                    ->send();
                return false;
            }
        } elseif ($isMovingForward) {
            $allowedFromCurrent = $currentStatus->onlyAdminMoveRoles()
                ->whereIn('role_id', $userRoleIds)
                ->exists();

            if (!($allowedFromCurrent)) {
                Notification::make()
                    ->danger()
                    ->title('წინ გადაადგილება შეზღუდულია.')
                    ->send();
                return false;
            }
        }

        return true;
    }



    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->model(Task::class)
                ->label('ახალი დავალება')
                ->form([
                    Grid::make(3)
                        ->schema([
                            TextInput::make('title')
                                ->label('დასახელება')
                                ->required()
                                ->maxLength(255),
                            Hidden::make('code'),
                            Hidden::make('created_by_id')
                                ->default(fn() => auth()->id()),
                            Select::make('status_id')
                                ->label('ეტაპი')
                                ->relationship('status', 'name')
                                ->default(function () {
                                    return TaskStatus::where('team_id', Filament::getTenant()->id)->orderBy('id')->value('id');
                                })
                                ->required(),
                            Select::make('priority')
                                ->label('პრიორიტეტი')
                                ->options(collect(priority_array())
                                    ->mapWithKeys(fn($item, $key) => [$key => $item['label']])
                                    ->toArray())
                                ->default('medium')
                                ->required(),
                        ]),

                    RichEditor::make('description')
                        ->label('დამატებითი ინფორმაცია'),

                    Grid::make(2)
                        ->schema([
                            DateTimePicker::make('started_at')
                                ->label('დაწყების დრო')
                                ->seconds(false),

                            DateTimePicker::make('deadline')
                                ->label('დედლაინი')
                                ->seconds(false),
                        ]),
                    Grid::make()
                        ->schema([
                            FileUpload::make('attachments')
                                ->label('მიმაგრებული ფაილები')
                                ->multiple()
                                ->directory('tasks/attachments')
                                ->previewable()
                                ->downloadable(),
                            FileUpload::make('images')
                                ->label('სურათები')
                                ->image()
                                ->multiple()
                                ->directory('tasks/images')
                                ->previewable(),
                        ]),
                    Repeater::make('steps')
                        ->label('ნაბიჯები')
                        ->relationship('steps')
                        ->schema([
                            TextInput::make('title')
                                ->label('ნაბიჯის დასახელება')
                                ->required(),

                            Select::make('is_completed')
                                ->label('შესრულებულია')
                                ->options([
                                    1 => 'დიახ',
                                    0 => 'არა',
                                ])
                                ->required(),
                        ])
                        ->minItems(0)
                        ->addActionLabel('ნაბიჯის დამატება')
                        ->columns(2),

                    Select::make('assignees')
                        ->label('დავალებული პირები')
                        ->multiple()
                        ->relationship('assignees', 'name')
                        ->preload()
                        ->searchable(),
                ]),
        ];

    }

    protected function getEditModalFormSchema(string|null|int $recordId): array
    {
        return [
            Repeater::make('steps')
                ->label('ნაბიჯები')
                ->relationship('steps')
                ->schema([
                    TextInput::make('title')
                        ->label('ნაბიჯის დასახელება')
                        ->required(),
                    Select::make('is_completed')
                        ->label('შესრულებულია')
                        ->options([
                            1 => 'დიახ',
                            0 => 'არა',
                        ])
                        ->required(),
                ])
                ->addActionLabel('ნაბიჯის დამატება')
                ->minItems(0)
                ->columns(2),

            Grid::make(3)
                ->schema([
                    TextInput::make('title')
                        ->label('დასახელება')
                        ->required()
                        ->maxLength(255),
                    Hidden::make('code'),
                    Hidden::make('created_by_id')
                        ->default(fn() => auth()->id()),
                    Select::make('status_id')
                        ->label('ეტაპი')
                        ->relationship('status', 'name')
                        ->required(),
                    Select::make('priority')
                        ->label('პრიორიტეტი')
                        ->options(collect(priority_array())
                            ->mapWithKeys(fn($item, $key) => [$key => $item['label']])
                            ->toArray())
                        ->default('medium')
                        ->required(),
                ]),

            RichEditor::make('description')
                ->label('დამატებითი ინფორმაცია'),

            Grid::make(2)
                ->schema([
                    DateTimePicker::make('started_at')
                        ->label('დაწყების დრო')
                        ->seconds(false),
                    DateTimePicker::make('deadline')
                        ->label('დედლაინი')
                        ->seconds(false),
                ]),

            Grid::make()
                ->schema([
                    FileUpload::make('attachments')
                        ->label('მიმაგრებული ფაილები')
                        ->multiple()
                        ->directory('tasks/attachments')
                        ->previewable()
                        ->downloadable(),
                    FileUpload::make('images')
                        ->label('სურათები')
                        ->image()
                        ->multiple()
                        ->directory('tasks/images')
                        ->previewable(),
                ]),

            Select::make('assignees')
                ->label('დავალებული პირები')
                ->multiple()
                ->relationship('assignees', 'name')
                ->preload()
                ->searchable(),
        ];
    }


    public
    function getTitle(): string|Htmlable
    {
        return Filament::getTenant()->board_name ?? 'კანბანი';
    }

    public
    static function getNavigationLabel(): string
    {
        return Filament::getTenant()->board_name ?? 'კანბანი';
    }

    public
    static function getNavigationGroup(): ?string
    {
        return Filament::getTenant()->board_name ?? 'კანბანი';
    }
}
