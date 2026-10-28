<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grupos', function (Blueprint $table) {
            $table->id();
            $table->string('curso_sigla');
            $table->integer('numeroCiclo');
            $table->integer('ano');
            $table->integer('numeroGrupo');
            $table->boolean('abierto');
            $table->timestamps();

            $table->foreign('curso_sigla')->references('sigla')->on('cursos')->restrictOnDelete();
            $table->unique(['curso_sigla', 'numeroCiclo', 'ano', 'numeroGrupo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grupos');
    }
};
