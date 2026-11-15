<?php

namespace Tests\Feature;

use App\Models\ProviderProfile;
use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoAndPrivacyTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_sitemap_index_and_country_sitemap_include_only_supported_public_pages(): void
    {
        $this->get(route('sitemap.index'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('/sitemaps/gb-services-1.xml', false)
            ->assertSee('/sitemaps/us-cities-1.xml', false);

        $this->get(route('sitemap.section', 'gb-services-1'))
            ->assertOk()
            ->assertSee('/plumbing/london</loc>', false)
            ->assertDontSee('/pest-control/london</loc>', false);

        $this->get(route('robots'))
            ->assertOk()
            ->assertSee('Disallow: /find')
            ->assertSee(route('sitemap.index'));
    }

    public function test_legal_pages_are_published_and_cookie_preference_is_remembered(): void
    {
        $this->get('/privacy')->assertOk()->assertSee('How we use information');
        $this->get('/terms')->assertOk()->assertSee('independent businesses');
        $this->get('/cookie-policy')->assertOk()->assertSee('strictly necessary first-party cookies');

        $this->post(route('cookie-consent.store'), ['choice' => 'rejected'])
            ->assertRedirect()
            ->assertCookie('cookie_consent', 'rejected');
    }

    public function test_search_filters_by_service_when_a_city_is_not_selected(): void
    {
        $category = Category::where('slug', 'electrical')->firstOrFail();

        $this->get(route('search', ['category_id' => $category->id]))
            ->assertOk()
            ->assertSee('North Star Electrics')
            ->assertDontSee('Bright Pipe Plumbing');
    }

    public function test_sitemap_sections_never_exceed_fifty_thousand_urls(): void
    {
        $providerId = ProviderProfile::where('slug', 'bright-pipe-plumbing')->value('id');
        $firstId = (int) DB::table('categories')->max('id') + 1;
        $additionalCount = 50001 - 4;
        $timestamp = now()->toDateTimeString();
        $categories = [];
        $providerCategories = [];

        for ($offset = 0; $offset < $additionalCount; $offset++) {
            $id = $firstId + $offset;
            $slug = 'sitemap-test-service-'.str_pad((string) $offset, 5, '0', STR_PAD_LEFT);
            $categories[] = [
                'id' => $id,
                'name' => 'Sitemap Test Service '.$offset,
                'slug' => $slug,
                'description' => 'Used to verify sitemap pagination.',
                'lead_price_credits' => 10,
                'is_active' => true,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
            $providerCategories[] = ['provider_profile_id' => $providerId, 'category_id' => $id];
        }

        foreach (array_chunk($categories, 100) as $chunk) {
            DB::table('categories')->insert($chunk);
        }

        foreach (array_chunk($providerCategories, 100) as $chunk) {
            DB::table('provider_category')->insert($chunk);
        }

        $index = $this->get(route('sitemap.index'))->assertOk();
        $index->assertSee('/sitemaps/categories-2.xml', false);
        $secondPage = $this->get(route('sitemap.section', 'categories-2'))->assertOk();

        $this->assertSame(1, substr_count($secondPage->getContent(), '<url>'));
        $secondPage->assertSee('sitemap-test-service-49996</loc>', false);
    }

    public function test_provider_can_export_and_erase_personal_account_data(): void
    {
        $user = User::factory()->create([
            'role' => 'provider',
            'email_verified_at' => now(),
        ]);
        $provider = ProviderProfile::factory()->create([
            'user_id' => $user->id,
            'status' => 'approved',
            'credit_balance' => 42,
        ]);

        $this->actingAs($user)
            ->get(route('provider.data.export'))
            ->assertDownload('account-data.json');

        $this->delete(route('provider.account.erase'), [
            'password' => 'password',
            'confirmation' => 'DELETE',
        ])->assertRedirect(route('home'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'removed-user-'.$user->id.'@deleted.invalid',
        ]);
        $this->assertDatabaseHas('provider_profiles', [
            'id' => $provider->id,
            'status' => 'suspended',
            'business_name' => 'Removed provider',
            'credit_balance' => 42,
        ]);
    }
}
