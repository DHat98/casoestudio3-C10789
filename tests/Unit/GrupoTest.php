<?php

use App\Models\Grupo;

test('a group contains its course and identifying information', function () {
    $group = new Grupo([
        'curso_sigla' => 'IF6100',
        'numeroCiclo' => 2,
        'ano' => 2026,
        'numeroGrupo' => 3,
        'abierto' => true,
    ]);

    expect($group->curso_sigla)->toBe('IF6100')
        ->and($group->numeroCiclo)->toBe(2)
        ->and($group->ano)->toBe(2026)
        ->and($group->numeroGrupo)->toBe(3)
        ->and($group->abierto)->toBeTrue();
});
