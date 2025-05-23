<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('condiciones_trabajador_maestro', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_condicion')->unique();
            $table->text('descripcion_condicion')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('condiciones_trabajador_maestro');
    }
};