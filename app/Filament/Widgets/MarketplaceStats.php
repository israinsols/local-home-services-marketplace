<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use App\Models\ProviderProfile;
use App\Models\StripePayment;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MarketplaceStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $revenue = StripePayment::query()
            ->where('status', 'paid')
            ->selectRaw('currency, SUM(amount_cents) as total')
            ->groupBy('currency')
            ->pluck('total', 'currency');

        return [
            Stat::make('Leads today', Lead::query()->whereDate('created_at', today())->count()),
            Stat::make('Approved providers', ProviderProfile::query()->where('status', 'approved')->count()),
            Stat::make('Revenue (GBP)', '£'.number_format(($revenue['GBP'] ?? 0) / 100, 2)),
            Stat::make('Revenue (USD)', '$'.number_format(($revenue['USD'] ?? 0) / 100, 2)),
        ];
    }
}
