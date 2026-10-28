<?php

use App\Models\Curso;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\Grupo;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('a group belongs to one course and a course has many groups', function () {
    $course = Curso::factory()->create();
    $group = Grupo::factory()->for($course, 'curso')->create();

    expect($group->curso->is($course))->toBeTrue()
        ->and($course->grupos)->toHaveCount(1);
});

test('a group can be taught by multiple teachers', function () {
    $group = Grupo::factory()->create();
    $teachers = Docente::factory()->count(2)->create();

    $group->docentes()->attach($teachers->modelKeys());

    expect($group->docentes)->toHaveCount(2)
        ->and($teachers->first()->grupos->modelKeys())->toContain($group->getKey());
});

test('a student enrollment associates a group and student and stores the grade', function () {
    $group = Grupo::factory()->create();
    $student = Estudiante::factory()->create();

    $enrollment = $group->estudiantesMatriculados()->create([
        'estudiante_carne' => $student->getKey(),
        'nota' => 91.5,
    ]);

    expect($enrollment->grupo->is($group))->toBeTrue()
        ->and($enrollment->estudiante->is($student))->toBeTrue()
        ->and($group->estudiantes()->first()->pivot->nota)->toBe(91.5);
});

test('a student has one advisor and a teacher advises multiple students', function () {
    $teacher = Docente::factory()->create();
    $student = Estudiante::factory()->for($teacher, 'docenteConsejero')->create();

    expect($student->docenteConsejero->is($teacher))->toBeTrue()
        ->and($teacher->estudiantesConsejados->contains($student))->toBeTrue();
});
