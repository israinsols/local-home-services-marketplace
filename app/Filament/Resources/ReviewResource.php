<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewResource\Pages;
use App\Models\Review;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationGroup = 'Marketplace';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('provider_profile_id')->relationship('provider', 'business_name')->required(),
            TextInput::make('author_name')->required()->maxLength(120),
            TextInput::make('rating')->numeric()->integer()->minValue(1)->maxValue(5)->required(),
            Textarea::make('body')->required()->maxLength(3000)->columnSpanFull(),
            Select::make('status')->options(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'])->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('provider.business_name')->searchable()->sortable(),
            TextColumn::make('author_name')->searchable(),
            TextColumn::make('rating')->numeric()->sortable(),
            TextColumn::make('body')->limit(80)->wrap(),
            TextColumn::make('status')->badge()->sortable(),
            TextColumn::make('created_at')->dateTime()->sortable(),
        ])->filters([
            SelectFilter::make('status')->options(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected']),
        ])->actions([
            Action::make('approve')->color('success')->requiresConfirmation()->visible(fn (Review $record) => $record->status !== 'approved')->action(function (Review $record): void {
                $record->update(['status' => 'approved']);
                Notification::make()->title('Review approved')->success()->send();
            }),
            Action::make('reject')->color('danger')->requiresConfirmation()->visible(fn (Review $record) => $record->status !== 'rejected')->action(function (Review $record): void {
                $record->update(['status' => 'rejected']);
                Notification::make()->title('Review rejected')->success()->send();
            }),
            EditAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReviews::route('/'),
            'edit' => Pages\EditReview::route('/{record}/edit'),
        ];
    }
}
