<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\City;
use App\Models\Lead;
use App\Models\User;
use Database\Seeders\MarketplaceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProductionDemoProviderSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_opted_in_production_seeding_creates_two_labeled_profiles_per_pair_without_demo_leads(): void
    {
        $initialLeadCount = Lead::query()->count();
        $previousEnv = getenv('SEED_DEMO_PROVIDERS');
        $previousEnvValue = $_ENV['SEED_DEMO_PROVIDERS'] ?? null;
        $hadEnvValue = array_key_exists('SEED_DEMO_PROVIDERS', $_ENV);
        $previousServerValue = $_SERVER['SEED_DEMO_PROVIDERS'] ?? null;
        $hadServerValue = array_key_exists('SEED_DEMO_PROVIDERS', $_SERVER);

        putenv('SEED_DEMO_PROVIDERS=true');
        $_ENV['SEED_DEMO_PROVIDERS'] = 'true';
        $_SERVER['SEED_DEMO_PROVIDERS'] = 'true';
        app()->instance('env', 'production');

        try {
            (new MarketplaceSeeder)->run();
        } finally {
            if ($previousEnv === false) {
                putenv('SEED_DEMO_PROVIDERS');
            } else {
                putenv('SEED_DEMO_PROVIDERS='.$previousEnv);
            }

            if ($hadEnvValue) {
                $_ENV['SEED_DEMO_PROVIDERS'] = $previousEnvValue;
            } else {
                unset($_ENV['SEED_DEMO_PROVIDERS']);
            }

            if ($hadServerValue) {
                $_SERVER['SEED_DEMO_PROVIDERS'] = $previousServerValue;
            } else {
                unset($_SERVER['SEED_DEMO_PROVIDERS']);
            }

            app()->instance('env', 'testing');
        }

        $providerCounts = DB::table('provider_profiles')
            ->join('provider_category', 'provider_profiles.id', '=', 'provider_category.provider_profile_id')
            ->join('provider_service_areas', 'provider_profiles.id', '=', 'provider_service_areas.provider_profile_id')
            ->where('provider_profiles.is_demo', true)
            ->where('provider_profiles.slug', 'like', 'demo-%')
            ->where('provider_profiles.status', 'approved')
            ->select('provider_category.category_id', 'provider_service_areas.city_id')
            ->selectRaw('COUNT(DISTINCT provider_profiles.id) as total')
            ->groupBy('provider_category.category_id', 'provider_service_areas.city_id')
            ->get();

        $expectedPairCount = Category::query()->where('is_active', true)->count()
            * City::query()->where('is_active', true)->count();

        $this->assertCount($expectedPairCount, $providerCounts);
        $this->assertTrue($providerCounts->every(fn ($pair) => (int) $pair->total === 2));
        $this->assertSame($initialLeadCount, Lead::query()->count());
        $this->assertSame(
            0,
            User::query()
                ->where('role', 'provider')
                ->where('email', 'like', 'demo-%@example.test')
                ->whereNotNull('email_verified_at')
                ->count(),
        );
    }
}
