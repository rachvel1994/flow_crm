<?php

namespace App\Filament\Resources;

use App\Models\User;
use App\Models\UserLocation;
use App\Models\UserPhone;
use App\Models\UserSocialLink;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Exception;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\UserResource\Pages;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Filament\Tables\Columns\SpatieTagsColumn;


class UserResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = User::class;
    protected static bool $isScopedToTenant = false;
    public static ?string $tenantOwnershipRelationshipName = 'teams';

    protected static ?string $label = 'კონტაქტი';
    protected static ?string $navigationLabel = 'კონტაქტი';
    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static ?int $navigationSort = 3;

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'name',
            'surname',
            'email',
            'tags.name',
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        if ($user->hasRole('super_admin')) {
            return parent::getEloquentQuery();
        }

        return parent::getEloquentQuery()->whereHas('user_type', function ($query) use ($user) {
            $query->whereIn('id', $user->visibleContactTypes->pluck('id'));
        });
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Grid::make(4)->schema([
                TextInput::make('name')->label('სახელი')
                    ->required(),
                TextInput::make('surname')->label('გვარი')
                    ->required(),
                Select::make('type_id')
                    ->label('კონტაქტის ტიპი')
                    ->preload()
                    ->searchable()
                    ->relationship('user_type', 'name')
                    ->required(),
                Select::make('role_id')
                    ->label('როლი')
                    ->preload()
                    ->searchable()
                    ->multiple()
                    ->relationship('roles', 'name')
                    ->required(),

                DatePicker::make('birthdate')
                    ->label('დაბადების თარიღი')
                    ->required(),
                TextInput::make('email')
                    ->label('ელ-ფოსტა')
                    ->unique(ignoreRecord: true)
                    ->email()
                    ->required(),
                Select::make('language')
                    ->searchable()
                    ->options(languages())
                    ->label('ენა')
                    ->required(),
                Select::make('team_id')
                    ->label('ჯგუფი')
                    ->preload()
                    ->multiple()
                    ->searchable()
                    ->relationship('teams', 'name')
                    ->required(),
            ]),

            Grid::make()->schema([
                TextInput::make('mobile')->label('მობილური')
                    ->required(),
                TextInput::make('address')->label('მისამართი')
                    ->required(),
            ]),

            Grid::make()->schema([
                Forms\Components\TextInput::make('password')
                    ->label(__('filament-panels::pages/auth/register.form.password.label'))
                    ->password()
                    ->revealable(filament()->arePasswordsRevealable())
                    ->rule(Password::default())
                    ->dehydrated(fn($state) => filled($state))
                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->same('passwordConfirmation')
                    ->validationAttribute(__('filament-panels::pages/auth/register.form.password.validation_attribute')),
                Forms\Components\TextInput::make('passwordConfirmation')
                    ->label(__('filament-panels::pages/auth/register.form.password_confirmation.label'))
                    ->password()
                    ->revealable(filament()->arePasswordsRevealable())
                    ->dehydrated(false),
            ]),
//                ->visible(fn() => auth()->user()?->can('can_access_panel_user') ?? false),
            Grid::make()->schema([
                FileUpload::make('image')
                    ->label('სურათი')
                    ->columns(1)
                    ->directory('images')
                    ->reorderable()
                    ->imagePreviewHeight(50)
                    ->panelLayout('compact')
                    ->downloadable()
                    ->storeFileNamesIn('original_filename'),
                Forms\Components\SpatieTagsInput::make('tags')
                    ->type('user')
                    ->label('თეგი'),
            ]),

            Repeater::make('phones')
                ->label('ტელეფონის ნომრები')
                ->relationship('phones')
                ->schema([
                    TextInput::make('phone')
                        ->label('ნომერი')
                        ->required(),
                ])
                ->addActionLabel('ნომრის დამატება')
                ->defaultItems(0)
                ->columns(1),

            Repeater::make('socialLinks')
                ->label('სოციალური ბმულები')
                ->relationship('socialLinks')
                ->schema([
                    Select::make('label')
                        ->label('დასახელება')
                        ->searchable()
                        ->options(fn() => socials())
                        ->required(),
                    TextInput::make('url')
                        ->label('ლინკი')
                        ->required(),
                ])
                ->addActionLabel('ბმულის დამატება')
                ->defaultItems(0)
                ->columns(2),

            Repeater::make('location')
                ->label('ლოკაცია')
                ->relationship('locations')
                ->schema([
                    TextInput::make('location')->label('ლოკაცია')->required(),
                ])
                ->addActionLabel('ლოკაციის დამატება')
                ->defaultItems(0)
                ->columns(1),
        ]);
    }


    /**
     * @throws Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->circular()
                    ->limit(1)
                    ->size(70)
                    ->default(asset('default.jpeg'))
                    ->label('სურათი'),
                TextColumn::make('name')
                    ->label('სახელი')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('surname')
                    ->label('გვარი')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('email')
                    ->label('ელ-ფოსტა')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('mobile')
                    ->label('მობილური')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('phones.phone')
                    ->label('ტელეფონის ნომერი')
                    ->searchable()
                    ->formatStateUsing(fn($state, $record) => $record->phones->first()?->phone ?? '-')
                    ->tooltip(fn($record) => $record->phones->pluck('phone')->implode(', ') ?: null
                    )
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('birthdate')
                    ->label('დაბადების თარიღი')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('language')
                    ->label('ენა')
                    ->formatStateUsing(fn(string $state): string => languages($state) ?? $state)
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('locations.location')
                    ->label('ლოკაცია')
                    ->formatStateUsing(fn($state, $record) => $record->locations->first()?->location ?? '-')
                    ->tooltip(fn($record) => $record->locations->pluck('location')->implode(', ') ?: null
                    )
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('creator.name')
                    ->label('დაამატა')
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('roles.name')
                    ->label('როლი')
                    ->searchable()
                    ->badge()
                    ->color('primary')
                    ->separator(', ')
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('socialLinks')
                    ->label('სოც. ბმული')
                    ->html()
                    ->formatStateUsing(function ($state, $record) {
                        return $record->socialLinks
                            ->map(function ($link) {
                                $url = $link->url;
                                // Add logic for WhatsApp and Viber links
                                if (strtolower($link->label) === 'whatsapp') {
                                    // Assuming the 'url' field contains the phone number
                                    $url = 'https://wa.me/' . preg_replace('/[^0-9]/', '', $link->url);
                                } elseif (strtolower($link->label) === 'viber') {
                                    // Assuming the 'url' field contains the phone number
                                    $url = 'viber://chat?number=' . preg_replace('/[^0-9]/', '', $link->url);
                                }

                                return '<a href="' . e($url) . '" target="_blank" class="inline-block px-2 py-1 text-sm rounded-full bg-primary-100 text-primary-800 dark:bg-primary-800 dark:text-primary-100 mr-1 mb-1">'
                                    . e($link->label)
                                    . '</a>';
                            })
                            ->implode(' ');
                    })
                    ->toggleable(isToggledHiddenByDefault: false),
                SpatieTagsColumn::make('tags')
                    ->label('თეგები')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('tags', function ($q) use ($search) {
                            $locale = app()->getLocale();
                            $q->where("name->{$locale}", 'like', "%{$search}%");
                        });
                    }),
                TextColumn::make('address')
                    ->label('მისამართი')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('created_at')
                    ->label('დამატებულია')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('updated_at')
                    ->label('განახლებულია')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('name')
                    ->form([
                        TextInput::make('name')->label('სახელი'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query->when($data['name'], fn($q, $value) => $q->where('name', 'like', "%$value%"));
                    }),

                Filter::make('surname')
                    ->form([
                        TextInput::make('surname')->label('გვარი'),
                    ])
                    ->query(fn($query, $data) => $query->when($data['surname'], fn($q, $value) => $q->where('surname', 'like', "%$value%"))),

                Filter::make('email')
                    ->form([
                        TextInput::make('email')->label('ელ-ფოსტა'),
                    ])
                    ->query(fn($query, $data) => $query->when($data['email'], fn($q, $value) => $q->where('email', 'like', "%$value%"))),

                Filter::make('birthdate')
                    ->form([
                        DatePicker::make('birthdate')->label('დაბადების თარიღი'),
                    ])
                    ->query(fn($query, $data) => $query->when($data['birthdate'], fn($q, $value) => $q->whereDate('birthdate', $value))),

                SelectFilter::make('created_by')
                    ->label('დაამატა მომხმარებლი')
                    ->options(User::all()->pluck('name', 'id'))
                    ->query(fn($query, $data) => $query->when($data['value'], fn($q, $value) => $q->where('created_by', $value))),

                SelectFilter::make('social_links')
                    ->label('სოც. ბმული')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->options(function () {
                        return UserSocialLink::query()->distinct('label')->pluck('label', 'label')->toArray();
                    })
                    ->query(function ($query, $data) {
                        $values = $data['values'] ?? [];

                        return $query->when(!empty($values), fn($q) => $q->whereHas('socialLinks', fn($q) => $q->whereIn('label', $values)));
                    }),

                SelectFilter::make('phones')
                    ->label('ტელეფონი')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->options(function () {
                        return UserPhone::query()->distinct('phone')->pluck('phone', 'phone')->toArray();
                    })
                    ->query(function ($query, $data) {
                        $values = $data['values'] ?? [];

                        return $query->when(!empty($values), fn($q) => $q->whereHas('phones', fn($q) => $q->whereIn('phone', $values)
                        )
                        );
                    }),

                SelectFilter::make('locations')
                    ->label('ლოკაცია')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->options(function () {
                        return UserLocation::query()->distinct('location')->pluck('location', 'location')->toArray();
                    })
                    ->query(function ($query, $data) {
                        $values = $data['values'] ?? [];

                        return $query->when(!empty($values), fn($q) => $q->whereHas('locations', fn($q) => $q->whereIn('location', $values)
                        )
                        );
                    }),

                SelectFilter::make('type_id')
                    ->label('კონტაქტის ტიპი')
                    ->multiple()
                    ->preload()
                    ->relationship('user_type', 'name')
                    ->searchable(),

                SelectFilter::make('roles')
                    ->label('როლები')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->relationship('roles', 'name'),

                SelectFilter::make('language')
                    ->label('ენა')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->options(languages()),
                SelectFilter::make('tags')
                    ->label('თეგები')
                    ->preload()
                    ->searchable()
                    ->relationship('tags', 'name')
                    ->multiple(),
            ], FiltersLayout::AboveContentCollapsible)
            ->actions([
                EditAction::make(),
                DeleteAction::make()
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
                BulkAction::make('markForSms')->label('SMS გაგზავნა'),
                BulkAction::make('markForEmail')->label('Email გაგზავნა'),
            ]);
    }


    public static function getRelations(): array
    {
        return [
            // No explicit relations needed here for `Spatie\Tags` via `HasTags` trait.
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }


    public static function getNavigationGroup(): ?string
    {
        return 'კონტაქტები';
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
