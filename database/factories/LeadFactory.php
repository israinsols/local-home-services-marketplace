<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\City;
use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        return [
            'category_id' => Category::query()->value('id') ?? Category::factory(),
            'city_id' => City::query()->value('id') ?? City::factory(),
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'postal_code' => fake()->postcode(),
            'details' => fake()->paragraph(),
            'timeline' => 'Within 1 month',
            'budget' => '£500–£1,000',
            'marketing_consent' => false,
            'consent_at' => now(),
            'consent_version' => '2026-01',
            'status' => 'new',
        ];
    }
}
