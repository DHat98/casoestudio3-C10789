<?php

namespace App\Models;

use Database\Factories\EstudianteMatriculadoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['grupo_id', 'estudiante_carne', 'nota'])]
class EstudianteMatriculado extends Model
{
    /** @use HasFactory<EstudianteMatriculadoFactory> */
    use HasFactory;

    protected $table = 'estudiante_matriculados';

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class);
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'estudiante_carne', 'carne');
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
