<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CreditTransactionResource\Pages;
use App\Models\CreditTransaction;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CreditTransactionResource extends Resource
{
    protected static ?string $model = CreditTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('provider.business_name')->searchable()->sortable(),
            TextColumn::make('admin.name')->label('Admin')->toggleable(),
            TextColumn::make('amount')->numeric()->sortable(),
            TextColumn::make('balance_after')->label('Balance')->numeric(),
            TextColumn::make('type')->badge()->sortable(),
            TextColumn::make('description')->wrap(),
            TextColumn::make('reference')->toggleable(),
            TextColumn::make('created_at')->dateTime()->sortable(),
        ])->filters([
            SelectFilter::make('type')->options([
                'purchase' => 'Purchase',
                'lead_unlock' => 'Lead unlock',
                'admin_adjustment' => 'Admin adjustment',
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListCreditTransactions::route('/')];
    }
}
