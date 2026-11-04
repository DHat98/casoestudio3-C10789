<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CursoController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sigla' => ['required', 'string', 'max:255', 'unique:cursos,sigla'],
            'nombreCurso' => ['required', 'string', 'max:255'],
            'creditos' => ['required', 'integer', 'between:1,6'],
        ]);

        $curso = Curso::create($validated);

        return response()->json([
            'sigla' => $curso->sigla,
            'nombreCurso' => $curso->nombreCurso,
            'creditos' => $curso->creditos,
        ], 201);
    }
}
