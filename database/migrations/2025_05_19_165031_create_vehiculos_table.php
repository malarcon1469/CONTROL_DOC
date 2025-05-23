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
            Schema::create('vehiculos', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('empresa_contratista_id');
                $table->string('patente')->unique();
                $table->string('marca')->nullable();
                $table->string('modelo')->nullable();
                $table->integer('ano')->nullable();
                $table->string('tipo_vehiculo')->nullable(); // Ej: Camioneta, Camión, Auto
                $table->string('numero_motor')->nullable()->unique();
                $table->string('numero_chasis')->nullable()->unique();
                $table->date('fecha_adquisicion')->nullable();
                $table->boolean('activo')->default(true);
                $table->softDeletes();
                $table->timestamps();

                $table->foreign('empresa_contratista_id')
                      ->references('id')
                      ->on('empresas_contratistas')
                      ->onDelete('cascade'); // Si se elimina la contratista, se eliminan sus vehículos
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::dropIfExists('vehiculos');
        }
    };
    