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
            Schema::create('trabajador_condicion', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('trabajador_id');
                $table->unsignedBigInteger('condicion_trabajador_id');
                $table->date('fecha_asignacion_condicion')->nullable();
                $table->date('fecha_vencimiento_condicion')->nullable();
                // Otros campos relevantes para esta asignación podrían ir aquí (ej: número de certificado)
                $table->string('valor_extra_condicion')->nullable()->comment('Ej: Nro de licencia, Nro certificado');
                $table->timestamps();

                $table->foreign('trabajador_id', 'tc_trabajador_fk')
                      ->references('id')
                      ->on('trabajadores')
                      ->onDelete('cascade');

                $table->foreign('condicion_trabajador_id', 'tc_condicion_fk')
                      ->references('id')
                      ->on('condiciones_trabajador_maestro')
                      ->onDelete('cascade');

                // No ponemos unique aquí por si una condición puede ser asignada múltiples veces con diferentes fechas
                // o si el "valor_extra_condicion" pudiera diferenciar instancias de la misma condición.
                // Si una condición solo se puede tener una vez (activa), esto se manejaría en la lógica de negocio.
                // O se podría agregar un unique compuesto si se define que la combinación es única.
                // Por ahora, permitimos múltiples asignaciones, se podría filtrar por la más reciente o válida.
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::dropIfExists('trabajador_condicion');
        }
    };
    