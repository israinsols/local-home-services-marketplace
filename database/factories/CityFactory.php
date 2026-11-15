<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CityFactory extends Factory
{
    protected $model = City::class;

    public function definition(): array
    {
        $name = fake()->unique()->city();

        return [
            'country_id' => Country::query()->value('id') ?? Country::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'region' => fake()->state(),
            'is_active' => true,
        ];
    }
}
