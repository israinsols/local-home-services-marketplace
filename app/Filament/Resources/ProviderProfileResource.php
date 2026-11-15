<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProviderProfileResource\Pages;
use App\Models\ProviderProfile;
use App\Services\AdminCreditService;
use App\Services\ProviderApprovalService;
use DomainException;
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
use InvalidArgumentException;

class ProviderProfileResource extends Resource
{
    protected static ?string $model = ProviderProfile::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationGroup = 'Marketplace';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('business_name')->required()->maxLength(160),
            TextInput::make('slug')->disabled(),
            Textarea::make('description')->maxLength(3000)->columnSpanFull(),
            TextInput::make('phone')->tel()->maxLength(32),
            TextInput::make('website')->url()->maxLength(255),
            TextInput::make('status')->disabled(),
            TextInput::make('credit_balance')->numeric()->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('business_name')->searchable()->sortable(),
                TextColumn::make('user.email')->label('Verified email')->searchable(),
                TextColumn::make('status')->badge()->color(fn (string $state) => match ($state) {
                    'approved' => 'success',
                    'pending' => 'warning',
                    'suspended', 'rejected' => 'danger',
                    default => 'gray',
                }),
                TextColumn::make('credit_balance')->numeric()->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([SelectFilter::make('status')->options([
                'pending' => 'Pending',
                'approved' => 'Approved',
                'rejected' => 'Rejected',
                'suspended' => 'Suspended',
            ])])
            ->actions([
                Action::make('approve')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (ProviderProfile $record) => $record->status !== 'approved')
                    ->action(function (ProviderProfile $record): void {
                        try {
                            app(ProviderApprovalService::class)->approve($record);
                        } catch (DomainException $exception) {
                            Notification::make()->title('Approval unavailable')->body($exception->getMessage())->danger()->send();

                            return;
                        }

                        Notification::make()->title('Provider approved')->success()->send();
                    }),
                Action::make('reject')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (ProviderProfile $record) => $record->status === 'pending')
                    ->action(function (ProviderProfile $record): void {
                        app(ProviderApprovalService::class)->reject($record);
                        Notification::make()->title('Provider rejected')->success()->send();
                    }),
                Action::make('suspend')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (ProviderProfile $record) => $record->status === 'approved')
                    ->action(function (ProviderProfile $record): void {
                        app(ProviderApprovalService::class)->suspend($record);
                        Notification::make()->title('Provider suspended')->success()->send();
                    }),
                Action::make('addCredits')
                    ->label('Add credits')
                    ->form([
                        TextInput::make('credits')->integer()->minValue(1)->maxValue(1000000)->required(),
                        Textarea::make('reason')->minLength(3)->maxLength(255)->required(),
                    ])
                    ->action(function (ProviderProfile $record, array $data): void {
                        try {
                            app(AdminCreditService::class)->add($record, auth()->user(), (int) $data['credits'], $data['reason']);
                        } catch (InvalidArgumentException $exception) {
                            Notification::make()->title('Credit adjustment failed')->body($exception->getMessage())->danger()->send();

                            return;
                        }

                        Notification::make()->title('Credits added')->success()->send();
                    }),
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProviderProfiles::route('/'),
            'edit' => Pages\EditProviderProfile::route('/{record}/edit'),
        ];
    }
}
