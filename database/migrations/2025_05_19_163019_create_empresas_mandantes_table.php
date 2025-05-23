<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresas_mandantes', function (Blueprint $table) {
            $table->id();
            $table->string('rut_empresa_mandante')->unique();
            $table->string('nombre_empresa_mandante');
            $table->string('razon_social_mandante')->nullable();
            $table->string('direccion_mandante')->nullable();
            $table->string('ciudad_mandante')->nullable();
            $table->string('telefono_mandante')->nullable();
            $table->string('email_mandante')->nullable();
            $table->string('nombre_contacto_mandante')->nullable();
            $table->string('email_contacto_mandante')->nullable();
            $table->string('telefono_contacto_mandante')->nullable();
            $table->boolean('activa')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresas_mandantes');
    }
};