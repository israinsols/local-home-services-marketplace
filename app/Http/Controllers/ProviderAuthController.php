<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\City;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProviderAuthController extends Controller
{
    public function create(): View
    {
        return view('provider.auth.register', [
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(),
            'cities' => City::query()->with('country')->where('is_active', true)->orderBy('name')->get(),
            'title' => 'Join as a service provider | '.config('marketplace.name'),
            'description' => 'Create an account to grow your local home services business.',
            'indexable' => false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:254', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(12)->letters()->mixedCase()->numbers()],
            'business_name' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:3000'],
            'phone' => ['required', 'string', 'regex:/^\+?[\d\s().-]{7,24}$/'],
            'website' => ['nullable', 'url:http,https', 'max:255'],
            'category_ids' => ['required', 'array', 'min:1', 'max:10'],
            'category_ids.*' => ['integer', 'distinct', Rule::exists('categories', 'id')->where('is_active', true)],
            'city_ids' => ['required', 'array', 'min:1', 'max:50'],
            'city_ids.*' => ['integer', 'distinct', Rule::exists('cities', 'id')->where('is_active', true)],
            'terms' => ['accepted'],
        ]);

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => strtolower($data['email']),
                'password' => $data['password'],
                'role' => 'provider',
            ]);
            $provider = ProviderProfile::create([
                'user_id' => $user->id,
                'business_name' => $data['business_name'],
                'slug' => Str::slug($data['business_name']).'-'.Str::lower(Str::random(6)),
                'description' => $data['description'] ?? null,
                'phone' => $data['phone'],
                'website' => $data['website'] ?? null,
                'status' => 'pending',
            ]);
            $provider->categories()->sync($data['category_ids']);
            $provider->serviceAreas()->sync($data['city_ids']);

            return $user;
        });

        $user->sendEmailVerificationNotification();

        return redirect()->route('verification.notice')
            ->with('status', 'Please verify your email address before signing in.');
    }

    public function login(): View
    {
        return view('provider.auth.login', [
            'title' => 'Provider sign in | '.config('marketplace.name'),
            'description' => 'Sign in to your '.config('marketplace.name').' provider dashboard.',
            'indexable' => false,
        ]);
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email:rfc'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'These credentials do not match our records.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        if ($request->user()->role !== 'provider') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors(['email' => 'These credentials do not belong to a provider account.'])->onlyInput('email');
        }

        return redirect()->intended(route('provider.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
