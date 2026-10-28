<?php

namespace App\Models;

use Database\Factories\GrupoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['curso_sigla', 'numeroCiclo', 'ano', 'numeroGrupo', 'abierto'])]
class Grupo extends Model
{
    /** @use HasFactory<GrupoFactory> */
    use HasFactory;

    protected $table = 'grupos';

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'curso_sigla', 'sigla');
    }

    public function docentes(): BelongsToMany
    {
        return $this->belongsToMany(
            Docente::class,
            'docente_grupo',
            'grupo_id',
            'docente_id',
            'id',
            'codEmpleado',
        );
    }

    public function estudiantesMatriculados(): HasMany
    {
        return $this->hasMany(EstudianteMatriculado::class, 'grupo_id');
    }

    public function estudiantes(): BelongsToMany
    {
        return $this->belongsToMany(
            Estudiante::class,
            'estudiante_matriculados',
            'grupo_id',
            'estudiante_carne',
            'id',
            'carne',
        )->withPivot('nota');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'numeroCiclo' => 'integer',
            'ano' => 'integer',
            'numeroGrupo' => 'integer',
            'abierto' => 'boolean',
        ];
    }
}
