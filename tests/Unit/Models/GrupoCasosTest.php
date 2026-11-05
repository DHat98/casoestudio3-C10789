<?php

use App\Models\Curso;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\Grupo;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, LazilyRefreshDatabase::class);

function crearGrupo(bool $abierto = false): Grupo
{
    $curso = Curso::factory()->create([
        'sigla' => 'IF6100',
        'nombreCurso' => 'Análisis y Diseño de Sistemas',
        'creditos' => 4,
    ]);

    return Grupo::factory()->for($curso, 'curso')->create([
        'numeroCiclo' => 2,
        'ano' => 2026,
        'numeroGrupo' => 1,
        'abierto' => $abierto,
    ]);
}

function crearDocente(): Docente
{
    return Docente::factory()->create([
        'codEmpleado' => 1001,
        'nombreEmpleado' => 'Ana',
        'apellidosEmpleado' => 'Mora Vargas',
    ]);
}

test('CP-01: recupera el grupo con sus atributos y el curso asociado', function () {
    $grupo = crearGrupo(abierto: true);

    $recuperado = Grupo::findOrFail($grupo->getKey());

    expect($recuperado->numeroCiclo)->toBe(2)
        ->and($recuperado->ano)->toBe(2026)
        ->and($recuperado->numeroGrupo)->toBe(1)
        ->and($recuperado->abierto)->toBeTrue()
        ->and($recuperado->curso->sigla)->toBe('IF6100')
        ->and($recuperado->curso->nombreCurso)->toBe('Análisis y Diseño de Sistemas')
        ->and($recuperado->curso->creditos)->toBe(4);
    $this->assertDatabaseCount('grupos', 1);
});

test('CP-02: un curso tiene tres grupos y distingue los dos abiertos', function () {
    $primero = crearGrupo(abierto: true);
    $curso = $primero->curso;
    Grupo::factory()->for($curso, 'curso')->create([
        'numeroCiclo' => 2, 'ano' => 2026, 'numeroGrupo' => 2, 'abierto' => true,
    ]);
    Grupo::factory()->for($curso, 'curso')->create([
        'numeroCiclo' => 2, 'ano' => 2026, 'numeroGrupo' => 3, 'abierto' => false,
    ]);

    $grupos = $curso->fresh()->grupos;
    $abiertos = $grupos->where('abierto', true);

    expect($grupos)->toHaveCount(3)
        ->and($grupos->pluck('numeroGrupo')->sort()->values()->all())->toBe([1, 2, 3])
        ->and($abiertos)->toHaveCount(2)
        ->and($abiertos->pluck('numeroGrupo')->sort()->values()->all())->toBe([1, 2])
        ->and($grupos->pluck('curso_sigla')->unique()->all())->toBe(['IF6100']);
    $this->assertDatabaseCount('grupos', 3);
});

test('CP-03: asigna dos docentes al grupo y mantiene la relación inversa', function () {
    $grupo = crearGrupo();
    $ana = crearDocente();
    Docente::factory()->create([
        'codEmpleado' => 1002, 'nombreEmpleado' => 'Luis', 'apellidosEmpleado' => 'Rojas Solano',
    ]);
    $grupo->load('docentes');

    $grupo->asignarDocente(1001);
    $grupo->asignarDocente(1002);

    expect($grupo->docentes)->toHaveCount(2)
        ->and($grupo->docentes->modelKeys())->toEqualCanonicalizing([1001, 1002])
        ->and($ana->grupos->modelKeys())->toBe([$grupo->getKey()]);
    $this->assertDatabaseCount('docente_grupo', 2);
});

test('CP-04: rechaza un tercer docente sin modificar las asignaciones existentes', function () {
    $grupo = crearGrupo();
    crearDocente();
    Docente::factory()->create([
        'codEmpleado' => 1002, 'nombreEmpleado' => 'Luis', 'apellidosEmpleado' => 'Rojas Solano',
    ]);
    Docente::factory()->create([
        'codEmpleado' => 1003, 'nombreEmpleado' => 'Carla', 'apellidosEmpleado' => 'Jiménez Araya',
    ]);
    $grupo->docentes()->attach([1001, 1002]);

    expect(fn () => $grupo->asignarDocente(1003))
        ->toThrow(DomainException::class, 'Un grupo no puede tener más de dos docentes.');

    expect($grupo->fresh()->docentes->modelKeys())->toEqualCanonicalizing([1001, 1002]);
    $this->assertDatabaseCount('docente_grupo', 2);
    $this->assertDatabaseMissing('docente_grupo', [
        'grupo_id' => $grupo->getKey(), 'docente_id' => 1003,
    ]);
});

test('CP-05: un grupo con catorce estudiantes no puede abrirse', function () {
    $grupo = crearGrupo();
    $docente = crearDocente();
    $grupo->docentes()->attach($docente->getKey());
    $estudiantes = Estudiante::factory()->count(14)->for($docente, 'docenteConsejero')->create();
    $grupo->estudiantes()->attach($estudiantes->modelKeys());

    expect($grupo->puedeAbrirse())->toBeFalse();
    expect(fn () => $grupo->abrir())
        ->toThrow(DomainException::class, 'El grupo requiere al menos quince estudiantes y uno o dos docentes para abrirse.');

    expect($grupo->fresh()->abierto)->toBeFalse();
    $this->assertDatabaseHas('grupos', ['id' => $grupo->getKey(), 'abierto' => false]);
    $this->assertDatabaseCount('estudiante_matriculados', 14);
});

test('CP-06: abre el grupo con quince estudiantes y un docente', function () {
    $grupo = crearGrupo();
    $docente = crearDocente();
    $grupo->docentes()->attach($docente->getKey());
    $estudiantes = Estudiante::factory()->count(15)->for($docente, 'docenteConsejero')->create();
    $grupo->estudiantes()->attach($estudiantes->modelKeys());

    expect($grupo->puedeAbrirse())->toBeTrue();
    $grupo->abrir();

    expect($grupo->fresh()->abierto)->toBeTrue();
    $this->assertDatabaseHas('grupos', ['id' => $grupo->getKey(), 'abierto' => true]);
    $this->assertDatabaseCount('estudiante_matriculados', 15);
    $this->assertDatabaseCount('docente_grupo', 1);
});
