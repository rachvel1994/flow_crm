<?php

namespace App\Filament\Pages\Tenancy;

use App\Models\Team;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\EditTenantProfile;
use Filament\Pages\Tenancy\RegisterTenant;

class EditTeam extends EditTenantProfile
{
    public static function getLabel(): string
    {
        return 'რედაქტირება';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)->schema([
                    TextInput::make('name')
                        ->label('სახელი')
                        ->unique(ignoreRecord: true)
                        ->required(),
                    TextInput::make('board_name')
                        ->label('კანბანის სახელი')
                        ->required(),
                    ColorPicker::make('config.colors.primary')
                        ->label('ფერი')
                        ->nullable()
                        ->hexColor(),
                ]),
                Grid::make(3)->schema([
                    FileUpload::make('logo')
                        ->label('ლოგო')
                        ->columns(1)
                        ->image()
                        ->imagePreviewHeight(200)
                        ->panelLayout('compact')
                        ->downloadable(false)
                        ->storeFileNamesIn('original_filename')
                        ->directory('logo'),
                    Toggle::make('is_active')
                        ->label('აქტიური სტატუსი')
                        ->default(true),
                ]),
            ]);
    }

    protected function handleRegistration(array $data): Team
    {
        $team = Team::create($data);

        $team->members()->attach(auth()->user());

        return $team;
    }
}
