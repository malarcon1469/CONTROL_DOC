<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmpresaContratista;
use App\Models\CondicionContratistaMaestro;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class EmpresaContratistaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (config('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }
        EmpresaContratista::truncate();
        DB::table('empresa_contratista_condicion')->truncate(); // Limpiar tabla pivote

        // Obtener usuarios para asociar
        $userContratista1 = User::where('email', 'contratista.admin1@example.com')->first();
        $userContratista2 = User::where('email', 'contratista.admin2@example.com')->first();

        // Obtener condiciones para asignar
        $condicionIso = CondicionContratistaMaestro::where('nombre_condicion', 'Certificación ISO 9001')->first();
        $condicionChileProveedores = CondicionContratistaMaestro::where('nombre_condicion', 'Inscrita en ChileProveedores')->first();
        $condicionSinDeudas = CondicionContratistaMaestro::where('nombre_condicion', 'No registra deudas previsionales')->first();

        // --- Empresa Contratista 1 ---
        $contratista1 = EmpresaContratista::create([
            'rut_empresa_contratista' => '78000001-5',
            'nombre_empresa_contratista' => 'Servicios Industriales Alpha',
            'razon_social_contratista' => 'Servicios Industriales Alpha S.A.',
            'direccion_contratista' => 'Calle Falsa 123, Calama',
            'ciudad_contratista' => 'Calama',
            'email_contratista' => 'contacto@alpha-servicios.cl',
            'user_id' => $userContratista1 ? $userContratista1->id : null,
            'activa' => true,
        ]);
        if ($contratista1 && $condicionIso) {
            $contratista1->condicionesContratistaMaestro()->attach($condicionIso->id);
        }
        if ($contratista1 && $condicionSinDeudas) {
            $contratista1->condicionesContratistaMaestro()->attach($condicionSinDeudas->id);
        }

        // --- Empresa Contratista 2 ---
        $contratista2 = EmpresaContratista::create([
            'rut_empresa_contratista' => '79000002-3',
            'nombre_empresa_contratista' => 'Transportes Beta Ltda.',
            'razon_social_contratista' => 'Transportes Beta y Cia Limitada',
            'direccion_contratista' => 'Av. Logistica 789, Santiago',
            'ciudad_contratista' => 'Santiago',
            'email_contratista' => 'info@transportesbeta.cl',
            'user_id' => $userContratista2 ? $userContratista2->id : null,
            'activa' => true,
        ]);
        if ($contratista2 && $condicionChileProveedores) {
            $contratista2->condicionesContratistaMaestro()->attach($condicionChileProveedores->id);
        }
        if ($contratista2 && $condicionSinDeudas) {
            $contratista2->condicionesContratistaMaestro()->attach($condicionSinDeudas->id);
        }

        // --- Empresa Contratista 3 (Sin usuario admin asociado inicialmente) ---
        EmpresaContratista::create([
            'rut_empresa_contratista' => '80000003-1',
            'nombre_empresa_contratista' => 'Consultores Gamma SPA',
            'razon_social_contratista' => 'Consultores Gamma Por Acciones',
            'direccion_contratista' => 'Oficina 101, Providencia',
            'ciudad_contratista' => 'Santiago',
            'email_contratista' => 'proyectos@consultoresgamma.com',
            'activa' => false, // Inactiva para ejemplo
        ]);


        if (config('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
        $this->command->info('Empresas contratistas de ejemplo creadas y condiciones asignadas.');
    }
}