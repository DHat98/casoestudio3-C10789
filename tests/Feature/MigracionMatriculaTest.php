<?php

use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\Grupo;
use Illuminate\Support\Facades\DB;

test('CP-20: la migración conserva matrículas, consejeros y docentes existentes', function () {
    $docente = Docente::factory()->create();
    $estudiante = Estudiante::factory()->for($docente, 'consejero')->create();
    $grupo = Grupo::factory()->create();
    $grupo->docentes()->attach($docente->getKey());
    $grupo->estudiantes()->attach($estudiante->getKey(), ['nota' => 8.5]);
    $migracion = require database_path('migrations/2026_11_13_153439_adaptar_tablas_matricula_al_contrato.php');

    $migracion->down();

    $this->assertDatabaseHas('estudiante_matriculados', [
        'grupo_id' => $grupo->getKey(), 'estudiante_carne' => $estudiante->getKey(), 'nota' => 8.5,
    ]);
    $this->assertDatabaseHas('estudiantes', ['carne' => $estudiante->getKey(), 'docente_id' => $docente->getKey()]);
    $this->assertDatabaseHas('docente_grupo', ['grupo_id' => $grupo->getKey(), 'docente_id' => $docente->getKey()]);

    $migracion->up();

    $this->assertDatabaseHas('estudiantes_matriculados', [
        'grupo_id' => $grupo->getKey(), 'estudiante_id' => $estudiante->getKey(), 'nota' => 8.5,
    ]);
    $this->assertDatabaseHas('estudiantes', ['carne' => $estudiante->getKey(), 'consejero_id' => $docente->getKey()]);
    $this->assertDatabaseHas('grupo_docente', ['grupo_id' => $grupo->getKey(), 'docente_id' => $docente->getKey()]);
    expect(DB::table('estudiantes_matriculados')->count())->toBe(1);
});
