<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskStatusResource\Pages;
use App\Models\TaskStatus;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class TaskStatusResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = TaskStatus::class;
    protected static bool $isScopedToTenant = true;

    protected static ?string $label = 'სვეტი';
    protected static ?string $navigationLabel = 'კანბანის სვეტები';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('სვეტის სახელი')
                ->required()
                ->maxLength(255),

            Forms\Components\ColorPicker::make('color')
                ->label('ფერი')
                ->required(),
            Forms\Components\Grid::make(3)->schema([
                Forms\Components\Toggle::make('is_active')
                    ->label('სტატუსი')
                    ->default(true),

                Forms\Components\Toggle::make('send_sms')
                    ->label('SMS გაგზავნა'),

                Forms\Components\Toggle::make('send_email')
                    ->label('Email გაგზავნა'),
                Forms\Components\Select::make('visible_roles')
                    ->label('სვეტის ნახვის უფლება')
                    ->multiple()
                    ->relationship('visibleRoles', 'name')
                    ->preload()
                    ->searchable()
                    ->dehydrated(true),

                Forms\Components\Select::make('only_admin_move_roles')
                    ->label('წინ გადასვლის უფლება')
                    ->multiple()
                    ->relationship('onlyAdminMoveRoles', 'name')
                    ->preload()
                    ->searchable()
                    ->dehydrated(true)
                    ->visible(function ($get, $record) {
                        $teamId = Filament::getTenant()?->id;
                        if (!$teamId) return true;

                        $maxOrder = TaskStatus::where('team_id', $teamId)->max('order_column');

                        return !$record || $record->order_column < $maxOrder;
                    }),


                Forms\Components\Select::make('can_move_back_roles')
                    ->label('უკან დაბრუნების უფლება')
                    ->multiple()
                    ->relationship('canMoveBackRoles', 'name')
                    ->preload()
                    ->searchable()
                    ->dehydrated(true)
                    ->visible(function ($get, $record) {
                        $teamId = Filament::getTenant()?->id;
                        if (!$teamId) return true;

                        $minOrder = TaskStatus::where('team_id', $teamId)->min('order_column');

                        return !$record || $record->order_column > $minOrder;
                    }),

                Forms\Components\Select::make('can_add_task_by_role')
                    ->label('სვეტში დამატების უფლება')
                    ->multiple()
                    ->relationship('canAddTaskByRole', 'name')
                    ->preload()
                    ->searchable()
                    ->dehydrated(true),
            ]),

            Forms\Components\Textarea::make('message')
                ->label('შეტყობინება')
                ->maxLength(1000)
                ->rows(3)
                ->columnSpanFull()
                ->nullable(),


        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('სვეტი')
                    ->searchable(),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('აქტიური'),

                Tables\Columns\IconColumn::make('send_sms')
                    ->label('SMS')
                    ->boolean(),

                Tables\Columns\IconColumn::make('send_email')
                    ->label('Email')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('შეკნა')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('განახლდა')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTaskStatuses::route('/'),
            'create' => Pages\CreateTaskStatus::route('/create'),
            'edit' => Pages\EditTaskStatus::route('/{record}/edit'),
        ];
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

    public static function getNavigationGroup(): ?string
    {
        return Filament::getTenant()->board_name ?? 'კანბანი';
    }
}
