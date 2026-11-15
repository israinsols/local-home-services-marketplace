<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CityResource\Pages;
use App\Models\Category;
use App\Models\City;
use App\Models\ContentPage;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CityResource extends Resource
{
    protected static ?string $model = City::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationGroup = 'Marketplace';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('country_id')->relationship('country', 'name')->searchable()->preload()->required(),
            TextInput::make('name')->required()->maxLength(120),
            TextInput::make('slug')->required()->maxLength(140)->unique(ignoreRecord: true)->rules([
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (Category::where('slug', $value)->exists() || ContentPage::where('slug', $value)->exists()) {
                        $fail('This slug is already used by another public page.');
                    }
                },
            ]),
            TextInput::make('region')->maxLength(100),
            TextInput::make('postal_prefix')->maxLength(16),
            TextInput::make('population')->numeric()->integer()->minValue(0),
            Toggle::make('is_active')->default(true)->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('country.name')->sortable(),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('region')->searchable(),
                TextColumn::make('postal_prefix')->label('Postal prefix'),
                TextColumn::make('population')->numeric()->sortable(),
                IconColumn::make('is_active')->boolean(),
            ])
            ->filters([
                SelectFilter::make('country')->relationship('country', 'name'),
                TernaryFilter::make('is_active'),
            ])
            ->actions([EditAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCities::route('/'),
            'create' => Pages\CreateCity::route('/create'),
            'edit' => Pages\EditCity::route('/{record}/edit'),
        ];
    }
}
