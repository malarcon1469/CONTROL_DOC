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
        Schema::create('cargos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_mandante_id')->nullable()->comment('El cargo puede ser global (null) o específico de un mandante');
            $table->string('nombre_cargo');
            $table->text('descripcion_cargo')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('empresa_mandante_id')
                  ->references('id')
                  ->on('empresas_mandantes')
                  ->onDelete('cascade'); // Si se elimina el mandante, se eliminan sus cargos específicos

            // Para asegurar que un nombre de cargo sea único para un mandante específico, o único si es global
            $table->unique(['empresa_mandante_id', 'nombre_cargo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cargos');
    }
};