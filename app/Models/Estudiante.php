<?php

namespace App\Models;

use Database\Factories\EstudianteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['carne', 'nombre', 'apellidos', 'docente_id'])]
class Estudiante extends Model
{
    /** @use HasFactory<EstudianteFactory> */
    use HasFactory;

    protected $table = 'estudiantes';

    protected $primaryKey = 'carne';

    public $incrementing = false;

    protected $keyType = 'string';

    public function docenteConsejero(): BelongsTo
    {
        return $this->belongsTo(Docente::class, 'docente_id', 'codEmpleado');
    }

    public function matriculas(): HasMany
    {
        return $this->hasMany(EstudianteMatriculado::class, 'estudiante_carne', 'carne');
    }

    public function grupos(): BelongsToMany
    {
        return $this->belongsToMany(
            Grupo::class,
            'estudiante_matriculados',
            'estudiante_carne',
            'grupo_id',
            'carne',
            'id',
        )->withPivot('nota');
    }
}
