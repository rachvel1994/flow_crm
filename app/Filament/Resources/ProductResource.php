<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Forms\Components\PriceInput;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\SpatieTagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SpatieTagsColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $label = 'პროდუქტი';
    protected static ?string $navigationLabel = 'პროდუქტი';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(5)->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('სახელი')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('sku')
                        ->label('SKU')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('model')
                        ->label('მოდელი')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Select::make('color_id')
                        ->label('ფერი')
                        ->required()
                        ->preload()
                        ->searchable()
                        ->relationship('color', 'name'),
                    Forms\Components\Select::make('category_id')
                        ->label('კატეგორია')
                        ->required()
                        ->preload()
                        ->searchable()
                        ->relationship('category', 'name')
                ]),
                Forms\Components\Grid::make(3)->schema([
                    PriceInput::make('price')
                        ->label('ფასი'),
                    PriceInput::make('b2b_price')
                        ->label('ბ2ბ ფასი'),
                    PriceInput::make('sale_price')
                        ->label('გასაყიდი ფასი'),
                ]),

                Forms\Components\RichEditor::make('description')
                    ->label('აღწერა')
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('images')
                    ->label('ფოტო')
                    ->columns(1)
                    ->multiple()
                    ->directory('images')
                    ->reorderable()
                    ->imagePreviewHeight(50)
                    ->panelLayout('compact')
                    ->downloadable()
                    ->columnSpan(2)
                    ->storeFileNamesIn('original_filename'),
                SpatieTagsInput::make('tags')
                    ->type('product')
                    ->label('თეგი'),
                Forms\Components\Toggle::make('is_active')
                    ->label('სტატუსი')
                    ->default(true)
                    ->required()
            ]);
    }

    /**
     * @throws \Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('images')
                    ->circular()
                    ->limit(1)
                    ->size(70)
                    ->default(asset('default.jpeg'))
                    ->label('ფოტო'),
                Tables\Columns\TextColumn::make('name')
                    ->label('სახელი')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('model')
                    ->label('მოდელი')
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('price')
                    ->label('ფასი')
                    ->money('GEL')
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('b2b_price')
                    ->label('ბ2ბ ფასი')
                    ->money('GEL')
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('sale_price')
                    ->label('გასაყიდი ფასი')
                    ->money('GEL')
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('color.name')
                    ->label('ფერი')
                    ->toggleable(isToggledHiddenByDefault: false),
                SpatieTagsColumn::make('tags')
                    ->label('თეგი')
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('სტატუსი')
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('დამატებულია')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('განახლებულია')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('sku')
                    ->form([
                        TextInput::make('sku')->label('SKU'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query->when($data['sku'], fn($q, $value) => $q->where('sku', 'like', "%$value%"));
                    }),
                Filter::make('model')
                    ->form([
                        TextInput::make('model')->label('მოდელი'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query->when($data['model'], fn($q, $value) => $q->where('model', 'like', "%$value%"));
                    }),
                Tables\Filters\SelectFilter::make('color_id')
                    ->label('ფერი')
                    ->relationship('color', 'name'),
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('კატეგორია')
                    ->relationship('category', 'name'),
                Tables\Filters\SelectFilter::make('is_active')
                    ->label('სტატუსი')
                    ->options([
                        '1' => 'აქტიური',
                        '0' => 'გათიშული',
                    ]),
                Tables\Filters\SelectFilter::make('tags')
                    ->label('თეგი')
                    ->multiple()
                    ->relationship('tags', 'name'),

                Tables\Filters\Filter::make('price_between')
                    ->form([
                        PriceInput::make('min')->label('მინ. ფასი'),
                        PriceInput::make('max')->label('მაქს. ფასი'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['min'], function ($q) use ($data) {
                                $q->where(function ($q) use ($data) {
                                    $q->where('price', '>=', $data['min'])
                                        ->orWhere('b2b_price', '>=', $data['min'])
                                        ->orWhere('sale_price', '>=', $data['min']);
                                });
                            })
                            ->when($data['max'], function ($q) use ($data) {
                                $q->where(function ($q) use ($data) {
                                    $q->where('price', '<=', $data['max'])
                                        ->orWhere('b2b_price', '<=', $data['max'])
                                        ->orWhere('sale_price', '<=', $data['max']);
                                });
                            });
                    }),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('თარიღიდან'),
                        Forms\Components\DatePicker::make('until')->label('თარიღიმდე'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn($q) => $q->whereDate('created_at', '>=', $data['from']))
                            ->when($data['until'], fn($q) => $q->whereDate('created_at', '<=', $data['until']));
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'პროდუქტი';
    }
}
