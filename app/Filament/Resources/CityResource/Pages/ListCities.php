<?php

namespace App\Filament\Resources\CityResource\Pages;

use App\Filament\Resources\CityResource;
use App\Services\CityCsvImporter;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ListCities extends ListRecords
{
    protected static string $resource = CityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('importCities')
                ->label('Import CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->form([
                    FileUpload::make('file')
                        ->disk('local')
                        ->directory('city-imports')
                        ->visibility('private')
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel'])
                        ->maxSize(5120)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    try {
                        $count = app(CityCsvImporter::class)->import($data['file']);
                    } catch (RuntimeException $exception) {
                        Notification::make()->title('City import failed')->body($exception->getMessage())->danger()->send();

                        return;
                    } finally {
                        Storage::disk('local')->delete($data['file']);
                    }

                    Notification::make()->title("Imported {$count} cities")->success()->send();
                }),
            Actions\CreateAction::make(),
        ];
    }
}
