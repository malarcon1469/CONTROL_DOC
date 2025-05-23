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
            Schema::create('configuraciones_documentos_mandante', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('empresa_mandante_id');
                $table->unsignedBigInteger('tipo_documento_id');

                $table->enum('entidad_controlada', ['EMPRESA', 'TRABAJADOR', 'VEHICULO'])
                      ->comment('A qué entidad aplica este requisito documental');

                // Dimensiones de la matriz de exigencias (pueden ser NULL si no aplican)
                $table->unsignedBigInteger('cargo_id')->nullable()->comment('Aplica a un cargo específico o a todos (si NULL)');
                $table->unsignedBigInteger('vinculacion_id')->nullable()->comment('Aplica a una vinculación jerárquica específica o a todas (si NULL)');
                $table->unsignedBigInteger('condicion_contratista_id')->nullable()->comment('Requerido si la empresa contratista cumple esta condición');
                $table->unsignedBigInteger('condicion_trabajador_id')->nullable()->comment('Requerido si el trabajador cumple esta condición');

                $table->boolean('es_obligatorio')->default(true);
                $table->text('observaciones')->nullable();
                $table->softDeletes();
                $table->timestamps();

                $table->foreign('empresa_mandante_id', 'cdm_mandante_fk')
                      ->references('id')
                      ->on('empresas_mandantes')
                      ->onDelete('cascade');

                $table->foreign('tipo_documento_id', 'cdm_tipo_doc_fk')
                      ->references('id')
                      ->on('tipos_documentos')
                      ->onDelete('cascade');

                $table->foreign('cargo_id', 'cdm_cargo_fk')
                      ->references('id')
                      ->on('cargos')
                      ->onDelete('set null'); // Si se elimina el cargo, la regla queda "para todos los cargos"

                $table->foreign('vinculacion_id', 'cdm_vinculacion_fk')
                      ->references('id')
                      ->on('vinculaciones')
                      ->onDelete('set null'); // Si se elimina la vinculación, la regla queda "para todas las vinculaciones"

                $table->foreign('condicion_contratista_id', 'cdm_cond_contratista_fk')
                      ->references('id')
                      ->on('condiciones_contratista_maestro')
                      ->onDelete('set null'); // Si se elimina la condición, la regla deja de depender de ella

                $table->foreign('condicion_trabajador_id', 'cdm_cond_trabajador_fk')
                      ->references('id')
                      ->on('condiciones_trabajador_maestro')
                      ->onDelete('set null'); // Si se elimina la condición, la regla deja de depender de ella

                // Para evitar configuraciones duplicadas idénticas.
                // Podría ser más complejo si "todos" (NULL) tiene una interpretación especial.
                // Por ahora, una combinación de estos campos debería ser única.
                $table->unique([
                    'empresa_mandante_id',
                    'tipo_documento_id',
                    'entidad_controlada',
                    'cargo_id',
                    'vinculacion_id',
                    'condicion_contratista_id',
                    'condicion_trabajador_id'
                ], 'unique_config_documento_mandante');
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::dropIfExists('configuraciones_documentos_mandante');
        }
    };
   