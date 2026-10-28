<?php

namespace App\Models;

use Database\Factories\DocenteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nombreEmpleado', 'apellidosEmpleado'])]
class Docente extends Model
{
    /** @use HasFactory<DocenteFactory> */
    use HasFactory;

    protected $table = 'docentes';

    protected $primaryKey = 'codEmpleado';

    public function grupos(): BelongsToMany
    {
        return $this->belongsToMany(
            Grupo::class,
            'docente_grupo',
            'docente_id',
            'grupo_id',
            'codEmpleado',
            'id',
        );
    }

    public function estudiantesConsejados(): HasMany
    {
        return $this->hasMany(Estudiante::class, 'docente_id', 'codEmpleado');
    }
}
