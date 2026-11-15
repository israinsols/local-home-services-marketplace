<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeadResource\Pages;
use App\Models\Lead;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Marketplace';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('category_id')->relationship('category', 'name')->required(),
            Select::make('city_id')->relationship('city', 'name')->required(),
            TextInput::make('name')->required()->maxLength(120),
            TextInput::make('email')->email()->required()->maxLength(254),
            TextInput::make('phone')->tel()->required()->maxLength(32),
            TextInput::make('postal_code')->required()->maxLength(16),
            Textarea::make('details')->required()->maxLength(5000)->columnSpanFull(),
            Select::make('timeline')->options([
                'asap' => 'As soon as possible',
                'within_2_weeks' => 'Within 2 weeks',
                'within_1_month' => 'Within a month',
                'flexible' => 'Flexible',
            ]),
            TextInput::make('budget')->maxLength(60),
            Toggle::make('marketing_consent')->disabled(),
            DateTimePicker::make('consent_at')->disabled(),
            TextInput::make('consent_version')->disabled(),
            Select::make('status')->options([
                'new' => 'New',
                'contacted' => 'Contacted',
                'completed' => 'Completed',
                'spam' => 'Spam',
            ])->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('category.name')->sortable(),
                TextColumn::make('city.name')->sortable(),
                TextColumn::make('name')->searchable(),
                TextColumn::make('email')->searchable()->toggleable(),
                TextColumn::make('phone')->searchable()->toggleable(),
                TextColumn::make('postal_code')->toggleable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([SelectFilter::make('status')->options([
                'new' => 'New',
                'contacted' => 'Contacted',
                'completed' => 'Completed',
                'spam' => 'Spam',
            ])])
            ->actions([EditAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeads::route('/'),
            'edit' => Pages\EditLead::route('/{record}/edit'),
        ];
    }
}
