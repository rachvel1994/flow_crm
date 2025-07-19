<?php

namespace App\Filament\Resources;

use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserPhone;
use App\Models\UserSocialLink;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Exception;
use Filament\Forms;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\SpatieTagsColumn;
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
use Parfaitementweb\FilamentCountryField\Forms\Components\Country;
use Parfaitementweb\FilamentCountryField\Tables\Columns\CountryColumn;


class UserResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = User::class;
    protected static bool $isScopedToTenant = false;
    public static ?string $tenantOwnershipRelationshipName = 'teams';

    protected static ?string $label = 'კონტაქტი';
    protected static ?string $navigationLabel = 'კონტაქტი';
    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Tabs::make('Tabs')
                ->tabs([
                    Tabs\Tab::make('მთავარი')->schema([
                        Grid::make(3)->schema([
                            TextInput::make('name')->label('სახელი')
                                ->required(),
                            TextInput::make('surname')->label('გვარი')
                                ->required(),
                            Select::make('role_id')
                                ->label('როლი')
                                ->preload()
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
                            Country::make('country')
                                ->searchable()
                                ->label('ეროვნება')
                                ->required(),
                        ]),
                        Select::make('team_id')
                            ->label('ჯგუფი')
                            ->preload()
                            ->multiple()
                            ->searchable()
                            ->relationship('teams', 'name')
                            ->required(),
                        Grid::make()->schema([
                            Forms\Components\TextInput::make('password')
                                ->label(__('filament-panels::pages/auth/register.form.password.label'))
                                ->password()
                                ->revealable(filament()->arePasswordsRevealable())
                                ->required(fn($record) => !$record)
                                ->rule(Password::default())
                                ->dehydrated(fn($state) => filled($state))
                                ->dehydrateStateUsing(fn($state) => Hash::make($state))
                                ->same('passwordConfirmation')
                                ->validationAttribute(__('filament-panels::pages/auth/register.form.password.validation_attribute')),
                            Forms\Components\TextInput::make('passwordConfirmation')
                                ->label(__('filament-panels::pages/auth/register.form.password_confirmation.label'))
                                ->password()
                                ->revealable(filament()->arePasswordsRevealable())
                                ->required(fn($record) => !$record)
                                ->dehydrated(false),
                        ])
                            ->visible(panel_user('can_access_panel_user')),
                        Grid::make()->schema([
                            FileUpload::make('image')
                                ->label('ლოგო')
                                ->columns(1)
                                ->directory('logo')
                                ->reorderable()
                                ->imagePreviewHeight(50)
                                ->panelLayout('compact')
                                ->downloadable()
                                ->storeFileNamesIn('original_filename'),
                            Forms\Components\SpatieTagsInput::make('tags')
                                ->type('user')
                                ->label('თეგი'),
                        ]),
                    ]),

                    Tabs\Tab::make('მობილურები')->schema([
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

                    ]),

                    Tabs\Tab::make('მისამართები')->schema([
                        Repeater::make('addresses')
                            ->label('მისამართები')
                            ->relationship('addresses')
                            ->schema([
                                TextInput::make('address')->label('მისამართი')->required(),
                            ])
                            ->addActionLabel('მისამართის დამატება')
                            ->defaultItems(0)
                            ->columns(1),

                    ]),

                    Tabs\Tab::make('სოციალური ბმულები')->schema([
                        Repeater::make('socialLinks')
                            ->label('სოციალური ბმულები')
                            ->relationship('socialLinks')
                            ->schema([
                                Select::make('label')
                                    ->label('დასახელება')
                                    ->searchable()
                                    ->options(socials())
                                    ->required(),
                                TextInput::make('url')
                                    ->label('ლინკი')
                                    ->url()
                                    ->required(),
                            ])
                            ->addActionLabel('ბმულის დამატება')
                            ->defaultItems(0)
                            ->columns(2),
                    ]),
                ])->columnSpanFull(),
        ]);
    }


    /**
     * @throws Exception
     */
    public
    static function table(Table $table): Table
    {
        return $table
            ->columns([
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
                TextColumn::make('phones.phone')
                    ->label('ტელეფონის ნომერი')
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('birthdate')
                    ->label('დაბადების თარიღი')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: false),
                CountryColumn::make('country')
                    ->label('ეროვნება')
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('addresses.address')
                    ->label('მისამართი')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('roles.name')
                    ->label('როლი')
                    ->badge()
                    ->color('primary')
                    ->separator(', ')
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('socialLinks.label')
                    ->label('სოც. ბმული')
                    ->toggleable(isToggledHiddenByDefault: false),
                SpatieTagsColumn::make('tags')
                    ->label('თეგები')
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

                SelectFilter::make('addresses')
                    ->label('მისამართი')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->options(function () {
                        return UserAddress::query()->distinct('address')->pluck('address', 'address')->toArray();
                    })
                    ->query(function ($query, $data) {
                        $values = $data['values'] ?? [];

                        return $query->when(!empty($values), fn($q) => $q->whereHas('addresses', fn($q) => $q->whereIn('address', $values)
                        )
                        );
                    }),


                SelectFilter::make('roles')
                    ->label('კონტაქტის ტიპი')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->relationship('roles', 'name'),

                Filter::make('country')
                    ->form([
                        Country::make('country')->label('ეროვნება'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['country'],
                            fn($q, $value) => $q->where('country', $value)
                        );
                    }),
                SelectFilter::make('tags.name')
                    ->label('თეგები')
                    ->preload()
                    ->searchable()
                    ->relationship('tags', 'name')
                    ->multiple(),
            ], FiltersLayout::AboveContentCollapsible)
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
                BulkAction::make('markForSms')->label('SMS გაგზავნა'),
                BulkAction::make('markForEmail')->label('Email გაგზავნა'),
            ]);
    }


    public
    static function getRelations(): array
    {
        return [
            // Define relations if needed (e.g., HasMany phones, etc.)
        ];
    }

    public
    static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public
    static function getPermissionPrefixes(): array
    {
        return [
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'can_access_panel',
            'can_move_task',
        ];
    }

    public
    static function getNavigationGroup(): ?string
    {
        return 'კონტაქტები';
    }
}
