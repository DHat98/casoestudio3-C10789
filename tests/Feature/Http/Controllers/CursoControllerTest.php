<?php

test('a guest saves a course and receives 201', function () {
    $payload = [
        'sigla' => 'IF6100',
        'nombreCurso' => 'Analisis y Diseno de Sistemas',
        'creditos' => 4,
    ];

    $response = $this->postJson(route('cursos.store'), $payload);

    $response->assertCreated()->assertExactJson($payload);
    $this->assertDatabaseHas('cursos', $payload);
    $this->assertDatabaseCount('cursos', 1);
});
