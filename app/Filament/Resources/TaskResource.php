<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Filament\Resources\TaskResource\RelationManagers;
use App\Forms\Components\PriceInput;
use App\Models\Task;
use App\Models\TaskStatus;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Exception;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TaskResource extends Resource implements  HasShieldPermissions
{
    protected static ?string $model = Task::class;

    protected static ?int $navigationSort = 2;

    protected static ?string $label = 'დავალება';
    protected static ?string $navigationIcon = 'heroicon-o-plus-circle';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(4)
                    ->schema([
                        TextInput::make('title')
                            ->label('დასახელება')
                            ->required()
                            ->maxLength(255),
                        PriceInput::make('price')
                            ->label('ფასი'),
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
                    ->columnSpanFull()
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

                        Toggle::make('is_completed')
                            ->label('შესრულებულია')
                            ->default(0),
                    ])
                    ->minItems(0)
                    ->columnSpanFull()
                    ->addActionLabel('ნაბიჯის დამატება')
                    ->columns(2),

                Select::make('assignees')
                    ->label('დავალებული პირები')
                    ->multiple()
                    ->relationship('assignees', 'name')
                    ->preload()
                    ->searchable(),
            ]);
    }

    /**
     * @throws Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('დასახელება')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('code')
                    ->label('კოდი')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('price')
                    ->label('ფასი')
                    ->money('GEL')
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('დავალება დაამატა')
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('team.name')
                    ->label('ჯგუფი')
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('status.name')
                    ->label('ეტაპი')
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('priority')
                    ->label('პრიორიტეტი')
                    ->formatStateUsing(function (string $state): string {
                        $priorityData = priority_array($state);
                        return $priorityData['label'] ?? $state;
                    })
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('started_at')
                    ->label('დაწყების დრო')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('deadline')
                    ->label('დედლაინი')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('შექმნილია')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('განახლებულია')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('title')
                    ->form([
                        TextInput::make('title')->label('დასახელების ძიება'),
                    ])
                    ->query(fn(Builder $query, array $data): Builder => $query->when(
                        $data['title'],
                        fn(Builder $query, $title): Builder => $query->where('title', 'like', "%{$title}%"),
                    )),

                SelectFilter::make('created_by_id')
                    ->label('შემქმნელი')
                    ->relationship('createdBy', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('status_id')
                    ->label('ეტაპი')
                    ->relationship('status', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('priority')
                    ->label('პრიორიტეტი')
                    ->options(collect(priority_array())
                        ->mapWithKeys(fn($item, $key) => [$key => $item['label']])
                        ->toArray()),

                SelectFilter::make('assignees')
                    ->label('დავალებული პირები')
                    ->relationship('assignees', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),

                TernaryFilter::make('has_price')
                    ->label('ფასით')
                    ->queries(
                        true: fn(Builder $query) => $query->whereNotNull('price'),
                        false: fn(Builder $query) => $query->whereNull('price'),
                        blank: fn(Builder $query) => $query,
                    ),

                Filter::make('price')
                    ->form([
                        PriceInput::make('price_min')->label('მინ. ფასი'),
                        PriceInput::make('price_max')->label('მაქს. ფასი'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['price_min'],
                                fn(Builder $query, $price): Builder => $query->where('price', '<=', $price),
                            )
                            ->when(
                                $data['price_max'],
                                fn(Builder $query, $price): Builder => $query->where('price', '>=', $price),
                            );
                    }),

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')->label('შექმნილია - დან'),
                        DatePicker::make('created_until')->label('შექმნილია - მდე'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ], Tables\Enums\FiltersLayout::AboveContentCollapsible)
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }



    public static function getNavigationLabel(): string
    {
        return 'დავალება';
    }

    public static function getNavigationGroup(): ?string
    {
        return Filament::getTenant()->board_name ?? 'კანბანი';
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
        ];
    }
}
