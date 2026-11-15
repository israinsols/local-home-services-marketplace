<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\City;
use App\Models\ContentPage;
use App\Models\Country;
use App\Models\Lead;
use App\Models\ProviderLeadMatch;
use App\Models\ProviderProfile;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MarketplaceSeeder extends Seeder
{
    public function run(): void
    {
        SiteSetting::updateOrCreate(['key' => 'site_name'], ['value' => config('marketplace.name')]);
        SiteSetting::updateOrCreate(['key' => 'contact_email'], ['value' => env('SITE_CONTACT_EMAIL', 'support@example.com')]);
        $contactEmail = SiteSetting::valueFor('contact_email', 'support@example.com');
        $legalPages = [
            'about' => [
                'title' => 'About '.config('marketplace.name'),
                'body' => config('marketplace.name')." helps homeowners describe home-service projects and connect with relevant local businesses. Homeowners can request quotes at no charge. Providers are independent businesses; a listing is not a guarantee of work quality, licensing, insurance, or availability. Please check credentials and agree terms directly with a provider.",
            ],
            'contact' => [
                'title' => 'Contact',
                'body' => "For marketplace support, privacy requests, or questions about your account, email [{$contactEmail}](mailto:{$contactEmail}). Homeowners should not send sensitive payment information by email.",
            ],
            'privacy' => [
                'title' => 'Privacy policy',
                'body' => "## Information we collect\nWhen a homeowner submits a request, we collect their name, email, phone number, postal code, project details, selected service and area, timeline, budget, and a record of the privacy notice and consent version shown at submission. Provider accounts include business contact details, service coverage, account credentials, and account activity. We also process limited technical information needed to secure and operate the site.\n\n## How we use information\nWe use homeowner request data to route the request to up to five approved, email-verified providers matching the selected service and area, administer the marketplace, prevent abuse, and meet legal obligations. Providers receive the request preview first; contact details are available to matched providers only after they unlock the lead. We do not include homeowner contact details in lead-notification emails. Marketing consent is separate and optional.\n\n## Sharing and retention\nWe share request information with the selected providers as described above and with service vendors acting on our instructions (such as hosting, email, and payment providers). We retain records only as needed for service delivery, security, dispute handling, and legal or accounting requirements. Financial transaction records may be retained after an account is removed when required by law.\n\n## Your choices and rights\nDepending on your location, you may have rights to access, correct, export, restrict, object to, or request deletion of personal data. Providers can export account data and remove their login and public listing from the account profile page. Homeowners can contact [{$contactEmail}](mailto:{$contactEmail}) to make a privacy request; we may need to verify identity before acting. Removing a request may not erase copies already lawfully received by a provider or records we must retain.\n\n## International transfers and contact\nThe marketplace serves the UK and the United States. Information may be processed in either country or by vendors in other locations, subject to appropriate safeguards where required. Contact [{$contactEmail}](mailto:{$contactEmail}) with privacy questions. This notice should be reviewed for the operator's actual legal basis, retention schedule, and transfer arrangements before launch.",
            ],
            'terms' => [
                'title' => 'Terms of service',
                'body' => "Homeowners may submit a project request without charge. Submitting a request does not create an obligation to hire a provider. Providers are independent businesses, not our employees or agents. We do not warrant the quality, licensing, insurance, price, or availability of any provider; check qualifications and agree the scope and payment terms directly before work begins.\n\nProviders must supply accurate business information, maintain appropriate permissions and insurance, use homeowner data only to respond to the specific request, and keep account credentials secure. Lead credits are used to unlock contact details and are subject to the applicable pack price and checkout terms. Credits are not cash and are not transferable except where required by law. We may suspend accounts or listings to protect users, investigate abuse, or comply with law.\n\nThese terms do not limit rights that cannot legally be excluded. Contact ".($contactEmail ? "[{$contactEmail}](mailto:{$contactEmail})" : 'the site operator')." with questions. The operator should obtain local legal review of these terms before launch.",
            ],
            'cookie-policy' => [
                'title' => 'Cookie policy',
                'body' => "We use strictly necessary first-party cookies for sessions, security, and account authentication. These are needed for the service to work. At present, no optional analytics or advertising cookies are active. Your cookie choice is stored in a first-party preference cookie so we can remember it. If optional technologies are introduced, they should remain off unless you choose to allow them. You can remove cookies in your browser settings; doing so may sign you out or affect site functionality.",
            ],
        ];

        foreach ($legalPages as $slug => $page) {
            $contentPage = ContentPage::firstOrCreate(
                ['slug' => $slug],
                [
                    ...$page,
                    'meta_title' => $page['title'].' | '.config('marketplace.name'),
                    'meta_description' => str($page['body'])->stripTags()->limit(155)->toString(),
                    'status' => 'published',
                ],
            );

            if ($slug === 'about') {
                $contentPage->fill([
                    'title' => str_replace('OmniLead', config('marketplace.name'), $contentPage->title),
                    'body' => str_replace('OmniLead', config('marketplace.name'), $contentPage->body),
                    'meta_title' => str_replace('OmniLead', config('marketplace.name'), $contentPage->meta_title),
                    'meta_description' => str_replace('OmniLead', config('marketplace.name'), $contentPage->meta_description),
                ])->save();
            }
        }

        $uk = Country::updateOrCreate(['code' => 'GB'], [
            'name' => 'United Kingdom', 'currency' => 'GBP', 'currency_symbol' => '£',
            'postal_label' => 'Postcode', 'phone_label' => 'Phone', 'locale' => 'en-GB', 'is_active' => true,
        ]);
        $us = Country::updateOrCreate(['code' => 'US'], [
            'name' => 'United States', 'currency' => 'USD', 'currency_symbol' => '$',
            'postal_label' => 'ZIP code', 'phone_label' => 'Phone', 'locale' => 'en-US', 'is_active' => true,
        ]);

        $services = [
            'Plumbing' => 'From dripping taps to full bathroom installations, find a reliable local plumber.',
            'Electrical' => 'Find qualified electricians for safe repairs, upgrades and installations.',
            'Roofing' => 'Compare trusted roofers for repairs, inspections and new roofs.',
            'HVAC' => 'Get matched with local heating, ventilation and air-conditioning specialists.',
            'Landscaping' => 'Connect with landscapers for garden design, maintenance and outdoor projects.',
            'Painting' => 'Find decorators for interior and exterior painting projects.',
            'Cleaning' => 'Book experienced home cleaners for regular or one-off cleans.',
            'Pest control' => 'Get help from local pest-control professionals.',
        ];
        $categories = [];

        foreach ($services as $name => $description) {
            $categories[$name] = Category::updateOrCreate(['slug' => Str::slug($name)], [
                'name' => $name, 'description' => $description, 'lead_price_credits' => 10, 'is_active' => true,
            ]);
        }

        $cityData = [
            [$uk, 'London', 'Greater London', 'SW1A'],
            [$uk, 'Birmingham', 'West Midlands', 'B1'],
            [$uk, 'Manchester', 'Greater Manchester', 'M1'],
            [$uk, 'Leeds', 'West Yorkshire', 'LS1'],
            [$uk, 'Bristol', 'South West England', 'BS1'],
            [$uk, 'Sheffield', 'South Yorkshire', 'S1'],
            [$uk, 'Nottingham', 'East Midlands', 'NG1'],
            [$uk, 'Leicester', 'East Midlands', 'LE1'],
            [$uk, 'Liverpool', 'Merseyside', 'L1'],
            [$uk, 'Glasgow', 'Scotland', 'G1'],
            [$us, 'New York', 'New York', '10001'],
            [$us, 'Los Angeles', 'California', '90012'],
            [$us, 'Chicago', 'Illinois', '60601'],
            [$us, 'Houston', 'Texas', '77002'],
            [$us, 'Phoenix', 'Arizona', '85004'],
        ];
        $cities = [];

        foreach ($cityData as [$country, $name, $region, $prefix]) {
            $slug = Str::slug($name);

            if (City::query()->where('slug', $slug)->where('country_id', '!=', $country->id)->exists()) {
                $slug .= '-'.strtolower($country->code);
            }

            $cities[$country->code.'-'.Str::slug($name)] = City::updateOrCreate([
                'country_id' => $country->id, 'slug' => $slug,
            ], [
                'name' => $name, 'region' => $region, 'postal_prefix' => $prefix, 'is_active' => true,
            ]);
        }

        if (! app()->environment(['local', 'testing'])) {
            $adminEmail = env('ADMIN_EMAIL');
            $adminPassword = env('ADMIN_PASSWORD');

            if ($adminEmail && $adminPassword) {
                User::updateOrCreate(['email' => $adminEmail], [
                    'name' => 'Marketplace Admin',
                    'password' => $adminPassword,
                    'role' => 'admin',
                    'email_verified_at' => now(),
                ]);
            }

            return;
        }

        foreach ([
            ['Bright Pipe Plumbing', 'London', 'GB', 'Plumbing', 'hello@brightpipe.example', '020 7946 0101'],
            ['North Star Electrics', 'Manchester', 'GB', 'Electrical', 'team@northstar.example', '0161 496 0101'],
            ['West End Roofing', 'Birmingham', 'GB', 'Roofing', 'hello@westendroofing.example', '0121 496 0101'],
            ['Liberty Home Services', 'New York', 'US', 'Plumbing', 'hello@libertyhomes.example', '+1 212 555 0101'],
            ['Pacific Climate Pros', 'Los Angeles', 'US', 'HVAC', 'service@pacificclimate.example', '+1 213 555 0101'],
        ] as [$business, $cityName, $countryCode, $service, $email, $phone]) {
            $user = User::firstOrCreate(['email' => $email], [
                'name' => $business, 'password' => 'password', 'role' => 'provider', 'email_verified_at' => now(),
            ]);
            $provider = ProviderProfile::updateOrCreate(['user_id' => $user->id], [
                'business_name' => $business,
                'slug' => Str::slug($business),
                'description' => "Sample {$service} listing for {$cityName}. This is demo content, not a real business; contact details are placeholders.",
                'phone' => $phone,
                'status' => 'approved',
                'credit_balance' => 100,
                'approved_at' => now(),
                'is_demo' => true,
            ]);
            $provider->categories()->syncWithoutDetaching([$categories[$service]->id]);
            $provider->serviceAreas()->syncWithoutDetaching([
                $cities[$countryCode.'-'.Str::slug($cityName)]->id,
            ]);
        }

        $demoPasswordHash = Hash::make(Str::random(64));

        foreach ($categories as $service => $category) {
            foreach ($cities as $city) {
                $providerCount = ProviderProfile::query()
                    ->where('status', 'approved')
                    ->whereHas('categories', fn ($query) => $query->whereKey($category->id))
                    ->whereHas('serviceAreas', fn ($query) => $query->whereKey($city->id))
                    ->count();

                for ($number = $providerCount + 1; $number <= 2; $number++) {
                    $slug = "demo-{$category->slug}-{$city->slug}-{$number}";
                    $businessName = "Demo {$service} Professional {$number} - {$city->name}";
                    $user = User::firstOrCreate(['email' => "{$slug}@example.test"], [
                        'name' => $businessName,
                        'password' => $demoPasswordHash,
                        'role' => 'provider',
                    ]);
                    $provider = ProviderProfile::updateOrCreate(['user_id' => $user->id], [
                        'business_name' => $businessName,
                        'slug' => $slug,
                        'description' => "Sample {$service} listing for {$city->name}. This is demo content, not a real business; contact details are placeholders.",
                        'phone' => null,
                        'status' => 'approved',
                        'credit_balance' => 0,
                        'approved_at' => now(),
                        'is_demo' => true,
                    ]);

                    $provider->categories()->syncWithoutDetaching([$category->id]);
                    $provider->serviceAreas()->syncWithoutDetaching([$city->id]);
                }
            }
        }

        foreach ([
            ['Plumbing', 'London', 'GB', 'SW1A 1AA'],
            ['Electrical', 'Manchester', 'GB', 'M1 1AE'],
            ['Roofing', 'Birmingham', 'GB', 'B1 1AA'],
            ['Plumbing', 'New York', 'US', '10001'],
            ['HVAC', 'Los Angeles', 'US', '90012'],
        ] as [$service, $cityName, $countryCode, $postalCode]) {
            $lead = Lead::firstOrCreate([
                'email' => strtolower($service).'.demo.'.$countryCode.'@example.test',
                'city_id' => $cities[$countryCode.'-'.Str::slug($cityName)]->id,
            ], [
                'category_id' => $categories[$service]->id,
                'name' => 'Demo Homeowner',
                'phone' => '+1 555 010 0000',
                'postal_code' => $postalCode,
                'details' => 'Looking for a trusted local '.strtolower($service).' professional to help with a home project.',
                'timeline' => 'Within 1 month',
                'budget' => 'Flexible',
                'marketing_consent' => false,
                'consent_at' => now(),
                'consent_version' => '2026-01',
                'status' => 'new',
            ]);

            $providers = ProviderProfile::query()
                ->where('status', 'approved')
                ->whereHas('categories', fn ($query) => $query->whereKey($categories[$service]->id))
                ->whereHas('serviceAreas', fn ($query) => $query->whereKey($cities[$countryCode.'-'.Str::slug($cityName)]->id))
                ->limit(5)
                ->get();

            foreach ($providers as $provider) {
                ProviderLeadMatch::firstOrCreate([
                    'lead_id' => $lead->id,
                    'provider_profile_id' => $provider->id,
                ], ['status' => 'matched']);
            }
        }
    }
}
