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
            Schema::create('configuracion_mandante_criterios', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('configuracion_documento_id');
                $table->unsignedBigInteger('criterio_evaluacion_id');
                // Campos extra si son necesarios para esta relación específica
                // Por ejemplo, si un criterio es opcional para una config pero obligatorio para otra
                $table->boolean('es_criterio_obligatorio')->default(true);
                $table->text('instruccion_adicional_criterio')->nullable();
                $table->timestamps();

                $table->foreign('configuracion_documento_id', 'cmc_config_doc_fk')
                      ->references('id')
                      ->on('configuraciones_documentos_mandante')
                      ->onDelete('cascade');

                $table->foreign('criterio_evaluacion_id', 'cmc_criterio_eval_fk')
                      ->references('id')
                      ->on('criterios_evaluacion_maestro')
                      ->onDelete('cascade');

                $table->unique(['configuracion_documento_id', 'criterio_evaluacion_id'], 'unique_config_criterio');
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::dropIfExists('configuracion_mandante_criterios');
        }
    };
   