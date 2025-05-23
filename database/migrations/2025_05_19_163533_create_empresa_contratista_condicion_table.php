    ```php
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
            Schema::create('empresa_contratista_condicion', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('empresa_contratista_id');
                $table->unsignedBigInteger('condicion_contratista_id');
                // Podríamos añadir campos extra a la tabla pivote si fuera necesario, como fecha_asignacion_condicion, etc.
                // Por ahora, la mantenemos simple.
                $table->timestamps(); // Para saber cuándo se asignó la condición

                $table->foreign('empresa_contratista_id', 'ecc_empresa_fk') // Nombre corto para el índice
                      ->references('id')
                      ->on('empresas_contratistas')
                      ->onDelete('cascade');

                $table->foreign('condicion_contratista_id', 'ecc_condicion_fk') // Nombre corto para el índice
                      ->references('id')
                      ->on('condiciones_contratista_maestro')
                      ->onDelete('cascade');

                $table->unique(['empresa_contratista_id', 'condicion_contratista_id'], 'unique_empresa_condicion'); // Evita duplicados
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::dropIfExists('empresa_contratista_condicion');
        }
    };
   