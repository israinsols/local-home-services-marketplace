<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\ProviderProfile;
use App\Models\User;
use App\Services\AdminCreditService;
use App\Services\CityCsvImporter;
use App\Services\ProviderApprovalService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class AdminWorkflowsTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_only_admin_users_can_access_filament(): void
    {
        $provider = User::factory()->create(['role' => 'provider']);
        $this->actingAs($provider)->get('/admin')->assertForbidden();

        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->get('/admin/categories')->assertOk();
        $this->get('/admin/cities')->assertOk();
        $this->get('/admin/provider-profiles')->assertOk();
        $this->get('/admin/credit-transactions')->assertOk();
    }

    public function test_provider_approval_requires_a_verified_email(): void
    {
        $user = User::factory()->create(['role' => 'provider', 'email_verified_at' => null]);
        $provider = ProviderProfile::factory()->create(['user_id' => $user->id, 'status' => 'pending']);
        $approval = app(ProviderApprovalService::class);

        try {
            $approval->approve($provider);
            $this->fail('An unverified provider must not be approved.');
        } catch (DomainException $exception) {
            $this->assertStringContainsString('verify their email', $exception->getMessage());
        }

        $this->assertSame('pending', $provider->fresh()->status);
        $user->markEmailAsVerified();
        $this->assertSame('approved', $approval->approve($provider)->status);
        $this->assertNotNull($provider->fresh()->approved_at);
    }

    public function test_admin_credit_adjustment_updates_the_ledger_and_records_the_actor(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $provider = ProviderProfile::factory()->create(['credit_balance' => 20]);

        $transaction = app(AdminCreditService::class)->add($provider, $admin, 15, 'Service recovery credit');

        $this->assertSame(35, $provider->fresh()->credit_balance);
        $this->assertSame($admin->id, $transaction->admin_user_id);
        $this->assertSame(15, $transaction->amount);
        $this->assertSame(35, $transaction->balance_after);
    }

    public function test_city_csv_import_upserts_rows_and_rolls_back_invalid_files(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('city-imports/valid.csv', "country_code,name,region,postal_prefix,population\nGB,Reading,Berkshire,RG1,174000\nUS,London,New York,NY,9000\n");

        $count = app(CityCsvImporter::class)->import('city-imports/valid.csv');
        $city = City::where('slug', 'reading')->firstOrFail();

        $this->assertSame(2, $count);
        $this->assertSame('GB', $city->country->code);
        $this->assertSame('RG1', $city->postal_prefix);
        $this->assertSame(174000, $city->population);
        $this->assertDatabaseHas('cities', ['slug' => 'london-us', 'name' => 'London']);

        Storage::disk('local')->put('city-imports/invalid.csv', "country_code,name\nGB,Invalid\n");

        try {
            app(CityCsvImporter::class)->import('city-imports/invalid.csv');
            $this->fail('A CSV without all required columns must be rejected.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('required column', $exception->getMessage());
        }

        $this->assertDatabaseMissing('cities', ['slug' => 'invalid']);
    }
}
