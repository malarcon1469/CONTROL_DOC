<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresas_contratistas', function (Blueprint $table) {
            $table->id();
            $table->string('rut_empresa_contratista')->unique();
            $table->string('nombre_empresa_contratista');
            $table->string('razon_social_contratista')->nullable();
            $table->string('direccion_contratista')->nullable();
            $table->string('ciudad_contratista')->nullable();
            $table->string('telefono_contratista')->nullable();
            $table->string('email_contratista')->nullable();
            $table->string('nombre_representante_legal')->nullable();
            $table->string('rut_representante_legal')->nullable();
            $table->string('email_representante_legal')->nullable();
            $table->string('telefono_representante_legal')->nullable();
            $table->boolean('activa')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->unsignedBigInteger('user_id')->nullable()->comment('ID del usuario de Laravel que administra esta empresa contratista');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresas_contratistas');
    }
};