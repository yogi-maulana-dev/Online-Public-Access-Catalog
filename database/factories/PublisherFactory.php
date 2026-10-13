<?php

namespace Database\Factories;

use App\Models\Publisher;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PublisherFactory extends Factory
{
    protected $model = Publisher::class;

    public function definition(): array
    {
        $name = 'Penerbit '.fake()->unique()->lastName().' Press';

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 9999),
            'description' => fake()->paragraph(),
            'website' => fake()->url(),
            'email' => fake()->companyEmail(),
            'city' => fake()->city(),
            'country' => 'Indonesia',
            'founded_year' => fake()->numberBetween(1980, 2015),
        ];
    }
}
