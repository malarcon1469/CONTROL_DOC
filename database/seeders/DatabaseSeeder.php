<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // Necesario para DB::statement

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Deshabilitar temporalmente las claves foráneas para evitar problemas de orden al truncar.
        // No todos los sistemas de BD lo soportan o requieren (ej. SQLite con :memory:).
        // Para MySQL, esto es útil.
        if (config('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        // Limpiar tablas antes de sembrar es opcional pero recomendado para mantener la consistencia
        // si se ejecuta el seeder múltiples veces. Algunos seeders individuales ya tienen su truncate.

        $this->call([
            RoleAndPermissionSeeder::class,
            UserSeeder::class, // Aquí se llamará a tu UserSeeder personalizado
            CatalogSeeder::class,
            EmpresaMandanteSeeder::class,
            CargoVinculacionSeeder::class,
            EmpresaContratistaSeeder::class,
            TrabajadorSeeder::class,
            ConfiguracionDocumentoMandanteSeeder::class,
            ConfiguracionMandanteCriterioSeeder::class,
        ]);

        if (config('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        $this->command->info('Base de datos poblada exitosamente con todos los seeders.'); // Mensaje final
    }
}