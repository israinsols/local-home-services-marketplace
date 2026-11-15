<?php

namespace Tests\Feature;

use App\Jobs\DistributeLead;
use App\Mail\LeadOpportunityMail;
use App\Models\Category;
use App\Models\City;
use App\Models\Lead;
use App\Models\ProviderProfile;
use App\Models\User;
use App\Services\LeadCreditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class LeadMarketplaceTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_quote_form_starts_with_project_details_and_shows_all_steps(): void
    {
        $this->get(route('leads.create'))
            ->assertOk()
            ->assertSee('data-initial-step="1"', false)
            ->assertSee('data-progress-step="1"', false)
            ->assertSee('data-progress-step="2"', false)
            ->assertSee('data-progress-step="3"', false);
    }

    public function test_demo_providers_cover_every_service_and_city_pair_twice(): void
    {
        $providerCounts = DB::table('provider_profiles')
            ->join('provider_category', 'provider_profiles.id', '=', 'provider_category.provider_profile_id')
            ->join('provider_service_areas', 'provider_profiles.id', '=', 'provider_service_areas.provider_profile_id')
            ->where('provider_profiles.is_demo', true)
            ->where('provider_profiles.status', 'approved')
            ->select('provider_category.category_id', 'provider_service_areas.city_id')
            ->selectRaw('COUNT(DISTINCT provider_profiles.id) as total')
            ->groupBy('provider_category.category_id', 'provider_service_areas.city_id')
            ->get();

        $expectedPairCount = Category::query()->where('is_active', true)->count()
            * City::query()->where('is_active', true)->count();

        $this->assertCount($expectedPairCount, $providerCounts);
        $this->assertTrue($providerCounts->every(fn ($pair) => (int) $pair->total === 2));
    }

    public function test_contact_validation_error_returns_to_the_contact_step(): void
    {
        $category = Category::where('slug', 'plumbing')->firstOrFail();
        $city = City::where('slug', 'london')->firstOrFail();

        $this->from(route('leads.create'))->post(route('leads.store'), [
            'category_id' => $category->id,
            'city_id' => $city->id,
            'name' => 'Taylor Homeowner',
            'email' => 'not-an-email',
            'phone' => '+44 20 7946 0100',
            'postal_code' => 'SW1A 1AA',
            'details' => 'A leaking kitchen tap needs repair and I would like an estimate.',
            'consent' => '1',
        ])->assertRedirect(route('leads.create'))
            ->assertSessionHasErrors('email');

        $this->get(route('leads.create'))
            ->assertOk()
            ->assertSee('data-initial-step="2"', false)
            ->assertSee('not-an-email');
    }

    public function test_homeowner_can_submit_a_uk_lead_and_queue_distribution(): void
    {
        Queue::fake();
        $category = Category::where('slug', 'plumbing')->firstOrFail();
        $city = City::where('slug', 'london')->firstOrFail();

        $response = $this->post(route('leads.store'), [
            'category_id' => $category->id,
            'city_id' => $city->id,
            'name' => 'Taylor Homeowner',
            'email' => 'taylor@example.test',
            'phone' => '+44 20 7946 0100',
            'postal_code' => 'SW1A 1AA',
            'details' => 'A leaking kitchen tap needs repair and I would like an estimate.',
            'timeline' => 'within_2_weeks',
            'budget' => '£100–£250',
            'consent' => '1',
        ]);

        $response->assertRedirect(route('leads.thank-you'));
        $this->assertDatabaseHas('leads', [
            'email' => 'taylor@example.test',
            'city_id' => $city->id,
            'marketing_consent' => false,
            'consent_version' => '2026-01',
        ]);
        Queue::assertPushed(DistributeLead::class, fn (DistributeLead $job) => $job->leadId === Lead::where('email', 'taylor@example.test')->value('id'));
    }

    public function test_homeowner_lead_requires_a_country_valid_postal_code_and_consent(): void
    {
        $category = Category::where('slug', 'plumbing')->firstOrFail();
        $city = City::where('slug', 'new-york')->firstOrFail();

        $data = [
            'category_id' => $category->id,
            'city_id' => $city->id,
            'name' => 'Taylor Homeowner',
            'email' => 'taylor@example.test',
            'phone' => '+1 212 555 0100',
            'postal_code' => 'SW1A 1AA',
            'details' => 'A leaking kitchen tap needs repair and I would like an estimate.',
            'consent' => '1',
        ];
        $this->from(route('leads.create'))->post(route('leads.store'), $data)->assertSessionHasErrors('postal_code');

        $data['postal_code'] = '10001';
        unset($data['consent']);
        $this->from(route('leads.create'))->post(route('leads.store'), $data)->assertSessionHasErrors('consent');

        $this->assertDatabaseMissing('leads', ['email' => 'taylor@example.test']);
    }

    public function test_matching_notifies_at_most_five_verified_approved_local_providers_without_contact_details(): void
    {
        Mail::fake();
        $lead = Lead::where('email', 'plumbing.demo.GB@example.test')->firstOrFail();
        $lead->matches()->delete();
        $category = $lead->category;
        $city = $lead->city;

        for ($index = 0; $index < 7; $index++) {
            $user = User::factory()->create([
                'email' => "provider{$index}@example.test",
                'role' => 'provider',
                'email_verified_at' => now(),
            ]);
            $provider = ProviderProfile::factory()->create(['user_id' => $user->id, 'status' => 'approved']);
            $provider->categories()->attach($category);
            $provider->serviceAreas()->attach($city);
        }

        $unverifiedUser = User::factory()->create(['email' => 'unverified@example.test', 'role' => 'provider']);
        $unverified = ProviderProfile::factory()->create(['user_id' => $unverifiedUser->id, 'status' => 'approved']);
        $unverified->categories()->attach($category);
        $unverified->serviceAreas()->attach($city);

        DistributeLead::dispatchSync($lead->id);

        $this->assertSame(5, $lead->matches()->count());
        Mail::assertQueued(LeadOpportunityMail::class, 5);
        Mail::assertQueued(LeadOpportunityMail::class, function (LeadOpportunityMail $mail) use ($lead) {
            $content = $mail->render();

            $this->assertStringNotContainsString($lead->email, $content);
            $this->assertStringNotContainsString($lead->phone, $content);

            return true;
        });
    }

    public function test_unlock_deducts_credits_once_and_records_a_ledger_entry(): void
    {
        $lead = Lead::where('email', 'plumbing.demo.GB@example.test')->firstOrFail();
        $provider = ProviderProfile::where('slug', 'bright-pipe-plumbing')->firstOrFail();
        $service = app(LeadCreditService::class);

        $service->unlock($provider->id, $lead->id);
        $service->unlock($provider->id, $lead->id);

        $this->assertSame(90, $provider->fresh()->credit_balance);
        $this->assertDatabaseCount('lead_unlocks', 1);
        $this->assertDatabaseHas('credit_transactions', [
            'provider_profile_id' => $provider->id,
            'lead_id' => $lead->id,
            'amount' => -10,
            'balance_after' => 90,
        ]);
        $this->assertSame(1, $provider->transactions()->count());
    }

    public function test_unlock_does_not_change_the_balance_when_credits_are_insufficient(): void
    {
        $lead = Lead::where('email', 'plumbing.demo.GB@example.test')->firstOrFail();
        $provider = ProviderProfile::where('slug', 'bright-pipe-plumbing')->firstOrFail();
        $provider->update(['credit_balance' => 0]);

        try {
            app(LeadCreditService::class)->unlock($provider->id, $lead->id);
            $this->fail('Insufficient credits should prevent unlocking a lead.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('credits', $exception->errors());
        }

        $this->assertSame(0, $provider->fresh()->credit_balance);
        $this->assertDatabaseCount('lead_unlocks', 0);
        $this->assertDatabaseCount('credit_transactions', 0);
    }
}
