<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\City;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ProviderAccountTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_provider_registration_requires_strong_password_and_email_verification(): void
    {
        Notification::fake();
        $category = Category::where('slug', 'plumbing')->firstOrFail();
        $city = City::where('slug', 'london')->firstOrFail();
        $registration = [
            'name' => 'Morgan Provider',
            'email' => 'morgan@example.test',
            'business_name' => 'Morgan Home Services',
            'phone' => '+44 20 7946 0102',
            'password' => 'weak',
            'password_confirmation' => 'weak',
            'category_ids' => [$category->id],
            'city_ids' => [$city->id],
            'terms' => '1',
        ];

        $this->from(route('provider.register'))->post(route('provider.register.store'), $registration)
            ->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', ['email' => $registration['email']]);

        $registration['password'] = 'StrongPassword2026';
        $registration['password_confirmation'] = $registration['password'];
        $this->post(route('provider.register.store'), $registration)
            ->assertRedirect(route('verification.notice'));

        $user = User::where('email', $registration['email'])->firstOrFail();
        $provider = ProviderProfile::where('user_id', $user->id)->firstOrFail();
        $this->assertSame('provider', $user->role);
        $this->assertNull($user->email_verified_at);
        $this->assertSame('pending', $provider->status);
        Notification::assertSentTo($user, VerifyEmail::class);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(30),
            ['id' => $user->id, 'hash' => sha1($user->email)],
        );

        $this->actingAs($user)->get($verificationUrl)->assertRedirect(route('provider.dashboard'));
        $this->assertNotNull($user->fresh()->email_verified_at);
    }
}
