<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estudiantes', function (Blueprint $table) {
            $table->renameColumn('docente_id', 'consejero_id');
        });

        Schema::table('estudiante_matriculados', function (Blueprint $table) {
            $table->renameColumn('estudiante_carne', 'estudiante_id');
        });

        Schema::rename('estudiante_matriculados', 'estudiantes_matriculados');
        Schema::rename('docente_grupo', 'grupo_docente');
    }

    public function down(): void
    {
        Schema::rename('grupo_docente', 'docente_grupo');
        Schema::rename('estudiantes_matriculados', 'estudiante_matriculados');

        Schema::table('estudiante_matriculados', function (Blueprint $table) {
            $table->renameColumn('estudiante_id', 'estudiante_carne');
        });

        Schema::table('estudiantes', function (Blueprint $table) {
            $table->renameColumn('consejero_id', 'docente_id');
        });
    }
};
