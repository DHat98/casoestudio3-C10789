<?php

namespace App\Models;

use Database\Factories\CursoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['sigla', 'nombreCurso', 'creditos'])]
class Curso extends Model
{
    /** @use HasFactory<CursoFactory> */
    use HasFactory;

    protected $table = 'cursos';

    protected $primaryKey = 'sigla';

    public $incrementing = false;

    protected $keyType = 'string';

    public function grupos(): HasMany
    {
        return $this->hasMany(Grupo::class, 'curso_sigla', 'sigla');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'creditos' => 'integer',
        ];
    }
}
