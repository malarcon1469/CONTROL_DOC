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
            Schema::create('documentos_adjuntos', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tipo_documento_id'); // Qué tipo de documento es (ej: CI, Licencia)

                // A qué entidad pertenece este documento adjunto
                $table->morphs('documentable'); // Esto crea documentable_id y documentable_type (Ej: Trabajador, EmpresaContratista, Vehiculo)

                // Quién subió el documento (usuario de la contratista)
                $table->unsignedBigInteger('subido_por_user_id')->nullable();

                // Información del archivo
                $table->string('nombre_original_archivo');
                $table->string('path_archivo'); // Ruta en el storage
                $table->string('mime_type')->nullable();
                $table->unsignedBigInteger('tamano_archivo')->nullable(); // en bytes

                // Estado del documento
                $table->enum('estado_validacion', ['PENDIENTE', 'APROBADO', 'RECHAZADO', 'VENCIDO', 'ANULADO'])
                      ->default('PENDIENTE');
                $table->text('observaciones_validacion')->nullable();
                $table->date('fecha_emision_documento')->nullable();
                $table->date('fecha_vencimiento_documento')->nullable();
                $table->unsignedBigInteger('validado_por_user_id')->nullable()->comment('Analista ASEM que validó');
                $table->timestamp('fecha_validacion')->nullable();

                // Relación opcional a la configuración específica que este documento intenta cumplir
                // Esto podría ser útil para rastrear, aunque la lógica de "qué se requiere" podría estar separada.
                $table->unsignedBigInteger('configuracion_documento_mandante_id')->nullable();

                $table->softDeletes();
                $table->timestamps();

                $table->foreign('tipo_documento_id')
                      ->references('id')
                      ->on('tipos_documentos')
                      ->onDelete('restrict'); // No eliminar un tipo de doc si hay adjuntos

                $table->foreign('subido_por_user_id')
                      ->references('id')
                      ->on('users')
                      ->onDelete('set null');

                $table->foreign('validado_por_user_id')
                      ->references('id')
                      ->on('users')
                      ->onDelete('set null');

                $table->foreign('configuracion_documento_mandante_id', 'da_config_doc_mandante_fk')
                      ->references('id')
                      ->on('configuraciones_documentos_mandante')
                      ->onDelete('set null');
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::dropIfExists('documentos_adjuntos');
        }
    };
  