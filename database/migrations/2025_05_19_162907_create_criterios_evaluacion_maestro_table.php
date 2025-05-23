<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('criterios_evaluacion_maestro', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_criterio')->unique();
            $table->text('descripcion_criterio')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('criterios_evaluacion_maestro');
    }
};