<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('trabajadores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_contratista_id');
            $table->string('rut_trabajador')->unique();
            $table->string('nombres_trabajador');
            $table->string('apellido_paterno_trabajador');
            $table->string('apellido_materno_trabajador');
            $table->date('fecha_nacimiento_trabajador')->nullable();
            $table->string('nacionalidad_trabajador')->nullable();
            $table->string('telefono_trabajador')->nullable();
            $table->string('email_trabajador')->nullable();
            $table->boolean('activo')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('empresa_contratista_id')
                  ->references('id')
                  ->on('empresas_contratistas')
                  ->onDelete('cascade'); // Si se elimina la contratista, se eliminan sus trabajadores
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trabajadores');
    }
};