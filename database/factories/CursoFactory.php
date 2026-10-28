<?php

namespace Database\Factories;

use App\Models\Curso;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Curso>
 */
class CursoFactory extends Factory
{
    protected $model = Curso::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sigla' => fake()->unique()->bothify('???###'),
            'nombreCurso' => fake()->words(3, true),
            'creditos' => fake()->numberBetween(1, 6),
        ];
    }
}
