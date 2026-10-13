<?php

namespace Database\Factories;

use App\Models\Author;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AuthorFactory extends Factory
{
    protected $model = Author::class;

    public function definition(): array
    {
        $name = fake()->name();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 99999),
            'email' => fake()->safeEmail(),
            'title' => fake()->randomElement(['Dr.', 'Prof. Dr.', 'Dr., M.Kom.', 'M.Pd.', 'M.Sc.', 'S.T., M.T.']),
            'affiliation' => 'Universitas '.fake()->city(),
            'faculty' => fake()->randomElement(['Fakultas Teknik', 'Fakultas Ilmu Komputer', 'Fakultas Ekonomi', 'Fakultas Pendidikan']),
            'department' => fake()->randomElement(['Teknik Informatika', 'Sistem Informasi', 'Manajemen', 'Pendidikan']),
            'orcid' => '0000-000'.fake()->numberBetween(1, 9).'-'.fake()->numerify('####-####'),
            'bio' => fake()->paragraph(3),
        ];
    }
}
