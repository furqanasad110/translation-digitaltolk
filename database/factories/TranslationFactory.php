<?php

namespace Database\Factories;

use App\Models\Translation;
use Illuminate\Database\Eloquent\Factories\Factory;

class TranslationFactory extends Factory
{
    protected $model = Translation::class;

    public function definition(): array
    {
        return [
            'key' => $this->faker->unique()->lexify('key_????'),
            'locale' => $this->faker->randomElement(['en', 'fr', 'es', 'de', 'it']),
            'content' => $this->faker->sentence(),
            'context' => $this->faker->word(),
            'tags' => $this->faker->randomElements(['mobile', 'desktop', 'web'], rand(1, 2)),  // array directly
        ];
    }
}
