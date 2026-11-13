<?php

use App\Models\Curso;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\EstudianteMatriculado;
use App\Models\Grupo;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;

mutates(Grupo::class);

function crearGrupoMatricula(): Grupo
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
        'abierto' => false,
    ]);
}

function crearConsejeroMatricula(): Docente
{
    return Docente::factory()->create([
        'codEmpleado' => 1001,
        'nombreEmpleado' => 'Ana',
        'apellidosEmpleado' => 'Mora Vargas',
    ]);
}

function crearEstudianteMatricula(Docente $consejero): Estudiante
{
    return Estudiante::factory()->for($consejero, 'consejero')->create([
        'carne' => 'C12345',
        'nombre' => 'María',
        'apellidos' => 'Solís Quesada',
    ]);
}

test('CP-07: un grupo sin docente no se abre con veinte estudiantes', function () {
    $grupo = crearGrupoMatricula();
    $consejero = crearConsejeroMatricula();
    $estudiantes = Estudiante::factory()->count(20)->for($consejero, 'consejero')->create();
    $grupo->estudiantes()->attach($estudiantes->modelKeys());

    expect($grupo->puedeAbrirse())->toBeFalse();
    expect(fn () => $grupo->abrir())->toThrow(DomainException::class);

    $this->assertDatabaseHas('grupos', ['id' => $grupo->getKey(), 'abierto' => false]);
    $this->assertDatabaseCount('estudiantes_matriculados', 20);
    $this->assertDatabaseCount('grupo_docente', 0);
});

test('CP-08: el estudiante queda ligado a su docente consejero', function () {
    $docente = crearConsejeroMatricula();
    $estudiante = crearEstudianteMatricula($docente);

    expect($estudiante->fresh()->consejero->codEmpleado)->toBe(1001)
        ->and($docente->fresh()->estudiantes->modelKeys())->toBe(['C12345']);
    $this->assertDatabaseHas('estudiantes', ['carne' => 'C12345', 'consejero_id' => 1001]);
});

test('CP-09: cambiar de consejero reemplaza al anterior', function () {
    $ana = crearConsejeroMatricula();
    $luis = Docente::factory()->create([
        'codEmpleado' => 1002,
        'nombreEmpleado' => 'Luis',
        'apellidosEmpleado' => 'Rojas Solano',
    ]);
    $estudiante = crearEstudianteMatricula($ana);

    $estudiante->consejero()->associate($luis);
    $estudiante->saveOrFail();

    expect($estudiante->fresh()->consejero->codEmpleado)->toBe(1002)
        ->and($ana->fresh()->estudiantes)->toHaveCount(0)
        ->and($luis->fresh()->estudiantes->modelKeys())->toBe(['C12345']);
    $this->assertDatabaseHas('estudiantes', ['carne' => 'C12345', 'consejero_id' => 1002]);
});

test('CP-10: al matricular la nota queda vacía en el pivote', function () {
    $grupo = crearGrupoMatricula();
    $estudiante = crearEstudianteMatricula(crearConsejeroMatricula());

    $grupo->estudiantes()->attach($estudiante->getKey());
    $matricula = $grupo->estudiantes()->firstOrFail()->pivot;

    expect($matricula)->toBeInstanceOf(EstudianteMatriculado::class)
        ->and($matricula->nota)->toBeNull();
    $this->assertDatabaseCount('estudiantes_matriculados', 1);
    $this->assertDatabaseHas('estudiantes_matriculados', [
        'grupo_id' => $grupo->getKey(), 'estudiante_id' => 'C12345', 'nota' => null,
    ]);
});

test('CP-11: registra la nota del estudiante sin modificar otra matrícula', function () {
    $grupo = crearGrupoMatricula();
    $consejero = crearConsejeroMatricula();
    $estudiante = crearEstudianteMatricula($consejero);
    $otro = Estudiante::factory()->for($consejero, 'consejero')->create(['carne' => 'C99999']);
    $grupo->estudiantes()->attach([$estudiante->getKey(), $otro->getKey()]);

    $grupo->registrarNota($estudiante, 8.5);

    $matricula = EstudianteMatriculado::where('grupo_id', $grupo->getKey())
        ->where('estudiante_id', 'C12345')->firstOrFail();
    expect($matricula->nota)->toBe(8.5);
    $this->assertDatabaseCount('estudiantes_matriculados', 2);
    $this->assertDatabaseHas('estudiantes_matriculados', [
        'grupo_id' => $grupo->getKey(), 'estudiante_id' => 'C12345', 'nota' => 8.5,
    ]);
    $this->assertDatabaseHas('estudiantes_matriculados', [
        'grupo_id' => $grupo->getKey(), 'estudiante_id' => 'C99999', 'nota' => null,
    ]);
});

test('CP-12: rechaza notas fuera del rango de cero a diez', function (float $nota) {
    $grupo = crearGrupoMatricula();
    $estudiante = crearEstudianteMatricula(crearConsejeroMatricula());
    $grupo->estudiantes()->attach($estudiante->getKey());

    expect(fn () => $grupo->registrarNota($estudiante, $nota))
        ->toThrow(InvalidArgumentException::class);

    $this->assertDatabaseHas('estudiantes_matriculados', [
        'grupo_id' => $grupo->getKey(), 'estudiante_id' => 'C12345', 'nota' => null,
    ]);
    $this->assertDatabaseCount('estudiantes_matriculados', 1);
})->with([-1, 10.5]);

test('CP-13: no permite matricular dos veces al mismo estudiante en un grupo', function () {
    $grupo = crearGrupoMatricula();
    $estudiante = crearEstudianteMatricula(crearConsejeroMatricula());
    $grupo->estudiantes()->attach($estudiante->getKey());

    expect(fn () => $grupo->estudiantes()->attach($estudiante->getKey()))
        ->toThrow(QueryException::class);

    $this->assertDatabaseCount('estudiantes_matriculados', 1);
    $this->assertDatabaseHas('estudiantes_matriculados', [
        'grupo_id' => $grupo->getKey(), 'estudiante_id' => 'C12345', 'nota' => null,
    ]);
});

test('CP-14: acepta los límites de la escala de notas', function (float $nota) {
    $grupo = crearGrupoMatricula();
    $estudiante = crearEstudianteMatricula(crearConsejeroMatricula());
    $grupo->estudiantes()->attach($estudiante->getKey());

    $grupo->registrarNota($estudiante, $nota);

    expect($grupo->estudiantesMatriculados()->firstOrFail()->nota)->toBe($nota);
})->with([0.0, 10.0]);

test('CP-15: no registra una nota si el estudiante no pertenece al grupo', function () {
    $grupo = crearGrupoMatricula();
    $estudiante = crearEstudianteMatricula(crearConsejeroMatricula());

    expect(fn () => $grupo->registrarNota($estudiante, 8.5))
        ->toThrow(ModelNotFoundException::class);

    $this->assertDatabaseCount('estudiantes_matriculados', 0);
});

test('CP-16: abre el grupo con dos docentes y actualiza la instancia', function () {
    $grupo = crearGrupoMatricula();
    $docentes = Docente::factory()->count(2)->create();
    $estudiantes = Estudiante::factory()->count(15)->for($docentes->first(), 'consejero')->create();
    $grupo->docentes()->attach($docentes->modelKeys());
    $grupo->estudiantes()->attach($estudiantes->modelKeys());

    expect($grupo->puedeAbrirse())->toBeTrue();
    $grupo->abrir();

    expect($grupo->abierto)->toBeTrue();
    $this->assertDatabaseHas('grupos', ['id' => $grupo->getKey(), 'abierto' => true]);
});

test('CP-17: un grupo con tres docentes registrados no puede abrirse', function () {
    $grupo = crearGrupoMatricula();
    $docentes = Docente::factory()->count(3)->create();
    $estudiantes = Estudiante::factory()->count(15)->for($docentes->first(), 'consejero')->create();
    $grupo->docentes()->attach($docentes->modelKeys());
    $grupo->estudiantes()->attach($estudiantes->modelKeys());

    expect($grupo->puedeAbrirse())->toBeFalse();
    expect(fn () => $grupo->abrir())->toThrow(DomainException::class);
    $this->assertDatabaseHas('grupos', ['id' => $grupo->getKey(), 'abierto' => false]);
});

test('CP-18: actualiza las relaciones ya cargadas al registrar una nota', function () {
    $grupo = crearGrupoMatricula();
    $estudiante = crearEstudianteMatricula(crearConsejeroMatricula());
    $grupo->estudiantes()->attach($estudiante->getKey());
    $grupo->load('estudiantes', 'estudiantesMatriculados');

    $grupo->registrarNota($estudiante, 8.5);

    expect($grupo->estudiantes->first()->pivot->nota)->toBe(8.5)
        ->and($grupo->estudiantesMatriculados->first()->nota)->toBe(8.5);
    $this->assertDatabaseHas('estudiantes_matriculados', [
        'grupo_id' => $grupo->getKey(), 'estudiante_id' => 'C12345', 'nota' => 8.5,
    ]);
});

test('CP-19: interpreta los identificadores numéricos del grupo como enteros', function () {
    $grupo = new Grupo([
        'numeroCiclo' => '2',
        'ano' => '2026',
        'numeroGrupo' => '1',
    ]);

    expect($grupo->numeroCiclo)->toBe(2)
        ->and($grupo->ano)->toBe(2026)
        ->and($grupo->numeroGrupo)->toBe(1);
});
