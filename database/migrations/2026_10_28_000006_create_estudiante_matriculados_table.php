<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estudiante_matriculados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grupo_id')->constrained('grupos')->cascadeOnDelete();
            $table->string('estudiante_carne');
            $table->float('nota')->nullable();
            $table->timestamps();

            $table->foreign('estudiante_carne')->references('carne')->on('estudiantes')->cascadeOnDelete();
            $table->unique(['grupo_id', 'estudiante_carne']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estudiante_matriculados');
    }
};
