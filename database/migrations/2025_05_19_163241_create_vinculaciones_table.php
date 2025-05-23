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
        Schema::create('vinculaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_mandante_id')->nullable()->comment('La vinculación puede ser global (null) o específica de un mandante');
            $table->string('nombre_vinculacion');
            $table->text('descripcion_vinculacion')->nullable();
            // Para estructuras jerárquicas, una vinculación puede pertenecer a otra
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('empresa_mandante_id')
                  ->references('id')
                  ->on('empresas_mandantes')
                  ->onDelete('cascade');

            $table->foreign('parent_id')
                  ->references('id')
                  ->on('vinculaciones')
                  ->onDelete('set null'); // Si se elimina la vinculación padre, esta se queda sin padre

            $table->unique(['empresa_mandante_id', 'nombre_vinculacion']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vinculaciones');
    }
};