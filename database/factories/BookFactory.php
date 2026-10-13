<?php

namespace Database\Factories;

use App\Enums\BookStatus;
use App\Models\Book;
use App\Models\Category;
use App\Models\Publisher;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookFactory extends Factory
{
    protected $model = Book::class;

    public function definition(): array
    {
        $title = ucfirst(fake()->words(fake()->numberBetween(3, 6), true));
        $year = fake()->numberBetween(2016, (int) date('Y'));

        return [
            'publisher_id' => Publisher::inRandomOrder()->value('id') ?? Publisher::factory(),
            'category_id' => Category::inRandomOrder()->value('id') ?? Category::factory(),
            'title' => $title,
            'subtitle' => fake()->optional()->sentence(4),
            'isbn' => fake()->numerify('978-###-####-##-#'),
            'doi' => '10.'.fake()->numberBetween(1000, 9999).'/upress.'.fake()->numerify('####'),
            'abstract' => fake()->paragraph(4),
            'description' => fake()->paragraphs(3, true),
            'year' => $year,
            'publication_date' => fake()->dateTimeBetween("$year-01-01", "$year-12-31"),
            'edition' => fake()->randomElement(['Edisi 1', 'Edisi 2', 'Edisi Revisi']),
            'language' => 'Indonesia',
            'pages' => fake()->numberBetween(120, 480),
            'format' => fake()->randomElement(['PDF', 'E-Book', 'Paperback']),
            'license' => 'CC BY 4.0',
            'is_open_access' => true,
            'status' => BookStatus::Published->value,
            'is_featured' => fake()->boolean(20),
            'view_count' => fake()->numberBetween(0, 5000),
            'download_count' => fake()->numberBetween(0, 1500),
            'published_at' => fake()->dateTimeBetween("$year-01-01", 'now'),
        ];
    }
}
