<?php

namespace Database\Factories;

use App\Models\Curso;
use App\Models\Grupo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Grupo>
 */
class GrupoFactory extends Factory
{
    protected $model = Grupo::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'curso_sigla' => Curso::factory(),
            'numeroCiclo' => fake()->numberBetween(1, 3),
            'ano' => fake()->numberBetween(2020, 2035),
            'numeroGrupo' => fake()->numberBetween(1, 20),
            'abierto' => fake()->boolean(),
        ];
    }
}
