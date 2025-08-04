<?php

namespace App\Filament\Pages\Tenancy;

use App\Models\Team;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\RegisterTenant;

class RegisterTeam extends RegisterTenant
{
    public static function getLabel(): string
    {
        return 'ჯგუფის დამატება';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('სახელი')
                    ->unique(ignoreRecord: true)
                    ->required(),
                TextInput::make('board_name')
                    ->label('კანბანის სახელი')
                    ->required(),
                FileUpload::make('logo')
                    ->label('ლოგო')
                    ->columns(1)
                    ->image()
                    ->imagePreviewHeight(50)
                    ->panelLayout('compact')
                    ->downloadable(false)
                    ->storeFileNamesIn('original_filename')
                    ->directory('logo'),
                ColorPicker::make('config.colors.primary')
                    ->label('ფერი')
                    ->nullable()
                    ->hexColor(),
                Toggle::make('is_active')
                    ->label('აქტიური სტატუსი')
                    ->default(1),
            ]);
    }

    protected function handleRegistration(array $data): Team
    {
        $team = Team::create($data);

        $team->members()->attach(auth()->user());

        return $team;
    }
}
