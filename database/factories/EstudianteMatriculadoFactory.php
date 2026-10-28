<?php

namespace Database\Factories;

use App\Models\Estudiante;
use App\Models\EstudianteMatriculado;
use App\Models\Grupo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EstudianteMatriculado>
 */
class EstudianteMatriculadoFactory extends Factory
{
    protected $model = EstudianteMatriculado::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'grupo_id' => Grupo::factory(),
            'estudiante_carne' => Estudiante::factory(),
            'nota' => fake()->randomFloat(2, 0, 100),
        ];
    }
}
