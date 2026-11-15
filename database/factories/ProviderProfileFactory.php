<?php

namespace Database\Factories;

use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProviderProfileFactory extends Factory
{
    protected $model = ProviderProfile::class;

    public function definition(): array
    {
        $business = fake()->company();

        return [
            'user_id' => User::factory(),
            'business_name' => $business,
            'slug' => Str::slug($business).'-'.fake()->unique()->numberBetween(1000, 9999),
            'description' => fake()->paragraph(),
            'phone' => fake()->phoneNumber(),
            'status' => 'approved',
            'credit_balance' => 100,
            'approved_at' => now(),
        ];
    }
}
