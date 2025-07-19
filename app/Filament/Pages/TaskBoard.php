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
        return TaskStatus::where('is_active', 1)
            ->get(['id', 'name', 'color'])
            ->map(fn($status) => [
                'id' => $status->id,
                'title' => $status->name,
                'color' => $status->color,
            ])
            ->values();
    }

    protected function records(): Collection
    {
        return Task::ordered()->get();
    }

    public function onStatusChanged(int|string $recordId, string $status, array $fromOrderedIds, array $toOrderedIds): void
    {
        $task = Task::with('steps')->findOrFail($recordId);

        $steps = $task->steps;

        $allStepsCompleted = $steps->every(fn($step) => (int)$step->is_completed === 1);

        if (!$allStepsCompleted) {
            Notification::make()
                ->title('გთხოვთ, ყველა ნაბიჯი შესრულდეს ეტაპის შესაცვლელად.')
                ->danger()
                ->send();
            return;
        }

        if (!panel_user('can_move_task_user')) {
            Notification::make()
                ->danger()
                ->title('სვეტი ჩაკეტილია, თქვენ არ გაქვთ სვეტის გადატანის უფლება')
                ->send();
            return;
        }

        $task->update(['status_id' => $status]);
        Task::setNewOrder($toOrderedIds);
    }

    public function onSortChanged(int|string $recordId, string $status, array $orderedIds): void
    {
        $currentStatus = Task::query()->firstWhere('status_id', $status);
        $newStatus = Task::query()->firstWhere('status_id', $status);

        if ($newStatus->order_column < $currentStatus->order_column) {
            Notification::make()
                ->danger()
                ->title('უკან დაბრუნება შეუძლებელია')
                ->send();
            return;
        }

        Task::setNewOrder($orderedIds);

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
