<?php

use App\Models\Curso;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('a guest saves a course and receives 201', function (int $creditos) {
    $payload = [
        'sigla' => 'IF6100',
        'nombreCurso' => 'Analisis y Diseno de Sistemas',
        'creditos' => $creditos,
    ];

    $response = $this->postJson(route('cursos.store'), $payload);

    $response->assertCreated()->assertExactJson($payload);
    $this->assertDatabaseHas('cursos', $payload);
    $this->assertDatabaseCount('cursos', 1);
})->with([1, 4, 6]);

test('a course with missing fields receives 422 and is not saved', function () {
    $response = $this->postJson(route('cursos.store'), []);

    $response->assertUnprocessable()->assertJsonValidationErrors([
        'sigla' => 'The sigla field is required.',
        'nombreCurso' => 'The nombre curso field is required.',
        'creditos' => 'The creditos field is required.',
    ]);
    $this->assertDatabaseCount('cursos', 0);
});

test('an invalid course receives 422 and is not saved', function (string $field, mixed $value, string $message) {
    $payload = [
        'sigla' => 'IF6100',
        'nombreCurso' => 'Analisis y Diseno de Sistemas',
        'creditos' => 4,
    ];
    $payload[$field] = $value;

    $response = $this->postJson(route('cursos.store'), $payload);

    $response->assertUnprocessable()->assertJsonValidationErrors([$field => $message]);
    $this->assertDatabaseCount('cursos', 0);
})->with([
    'sigla must be text' => ['sigla', 123, 'The sigla field must be a string.'],
    'sigla is too long' => ['sigla', str_repeat('A', 256), 'The sigla field must not be greater than 255 characters.'],
    'name must be text' => ['nombreCurso', 123, 'The nombre curso field must be a string.'],
    'name is too long' => ['nombreCurso', str_repeat('A', 256), 'The nombre curso field must not be greater than 255 characters.'],
    'credits below minimum' => ['creditos', 0, 'The creditos field must be between 1 and 6.'],
    'credits above maximum' => ['creditos', 7, 'The creditos field must be between 1 and 6.'],
    'credits must be integral' => ['creditos', 1.5, 'The creditos field must be an integer.'],
    'credits must be numeric' => ['creditos', 'invalid', 'The creditos field must be an integer.'],
]);

test('a duplicate sigla receives 422 without replacing the existing course', function () {
    $course = Curso::factory()->create([
        'sigla' => 'IF6100',
        'nombreCurso' => 'Curso original',
        'creditos' => 3,
    ]);

    $response = $this->postJson(route('cursos.store'), [
        'sigla' => $course->sigla,
        'nombreCurso' => 'Curso diferente',
        'creditos' => 6,
    ]);

    $response->assertUnprocessable()->assertJsonValidationErrors([
        'sigla' => 'The sigla has already been taken.',
    ]);
    $this->assertDatabaseCount('cursos', 1);
    $this->assertDatabaseHas('cursos', [
        'sigla' => 'IF6100',
        'nombreCurso' => 'Curso original',
        'creditos' => 3,
    ]);
});
