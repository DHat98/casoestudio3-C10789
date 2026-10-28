<?php

namespace Database\Factories;

use App\Models\Docente;
use App\Models\Estudiante;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Estudiante>
 */
class EstudianteFactory extends Factory
{
    protected $model = Estudiante::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'carne' => fake()->unique()->numerify('########'),
            'nombre' => fake()->firstName(),
            'apellidos' => fake()->lastName(),
            'docente_id' => Docente::factory(),
        ];
    }
}
