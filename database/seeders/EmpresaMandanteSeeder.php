<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmpresaMandante;
use Illuminate\Support\Facades\DB;

class EmpresaMandanteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (config('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }
        EmpresaMandante::truncate();

        EmpresaMandante::create([
            'rut_empresa_mandante' => '76000001-K',
            'nombre_empresa_mandante' => 'Gran Minera del Norte S.A.',
            'razon_social_mandante' => 'Gran Minera del Norte Sociedad Anónima',
            'direccion_mandante' => 'Av. Principal 123, Antofagasta',
            'ciudad_mandante' => 'Antofagasta',
            'telefono_mandante' => '+56552123456',
            'email_mandante' => 'contacto@granmineranorte.cl',
            'nombre_contacto_mandante' => 'Juan Pérez (Gerente SSO)',
            'email_contacto_mandante' => 'jperez.sso@granmineranorte.cl',
            'telefono_contacto_mandante' => '+56987654321',
            'activa' => true,
        ]);

        EmpresaMandante::create([
            'rut_empresa_mandante' => '77000002-8',
            'nombre_empresa_mandante' => 'Constructora Austral Ltda.',
            'razon_social_mandante' => 'Constructora Austral y Compañía Limitada',
            'direccion_mandante' => 'Calle Sur 456, Santiago',
            'ciudad_mandante' => 'Santiago',
            'telefono_mandante' => '+56229876543',
            'email_mandante' => 'info@constructoraaustral.cl',
            'nombre_contacto_mandante' => 'Ana López (Jefa de Proyectos)',
            'email_contacto_mandante' => 'alopez.proyectos@constructoraaustral.cl',
            'telefono_contacto_mandante' => '+56912345678',
            'activa' => true,
        ]);

        if (config('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
        $this->command->info('Empresas mandantes de ejemplo creadas.');
    }
}