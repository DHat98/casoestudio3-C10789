<?php

namespace App\Models;

use Database\Factories\EstudianteMatriculadoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

#[Fillable(['grupo_id', 'estudiante_id', 'nota'])]
class EstudianteMatriculado extends Pivot
{
    /** @use HasFactory<EstudianteMatriculadoFactory> */
    use HasFactory;

    protected $table = 'estudiantes_matriculados';

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class);
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'estudiante_id', 'carne');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nota' => 'float',
        ];
    }
}
