<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Trabajador;
use App\Models\EmpresaContratista;
use App\Models\CondicionTrabajadorMaestro;
use Illuminate\Support\Facades\DB;

class TrabajadorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (config('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }
        Trabajador::truncate();
        DB::table('trabajador_condicion')->truncate(); // Limpiar tabla pivote

        // Obtener empresas contratistas
        $contratistaAlpha = EmpresaContratista::where('rut_empresa_contratista', '78000001-5')->first();
        $contratistaBeta = EmpresaContratista::where('rut_empresa_contratista', '79000002-3')->first();

        // Obtener condiciones de trabajador
        $condLicA4 = CondicionTrabajadorMaestro::where('nombre_condicion', 'Licencia de Conducir Clase A4')->first();
        $condLicB = CondicionTrabajadorMaestro::where('nombre_condicion', 'Licencia de Conducir Clase B')->first();
        $condAltura = CondicionTrabajadorMaestro::where('nombre_condicion', 'Curso Trabajos en Altura')->first();

        // --- Trabajadores para Contratista Alpha ---
        if ($contratistaAlpha) {
            $trabajador1 = Trabajador::create([
                'empresa_contratista_id' => $contratistaAlpha->id,
                'rut_trabajador' => '15000001-1',
                'nombres_trabajador' => 'Carlos',
                'apellido_paterno_trabajador' => 'Soto',
                'apellido_materno_trabajador' => 'Rojas',
                'fecha_nacimiento_trabajador' => '1985-03-15',
                'activo' => true,
            ]);
            if ($trabajador1 && $condLicA4) {
                $trabajador1->condicionesTrabajadorMaestro()->attach($condLicA4->id, [
                    'fecha_asignacion_condicion' => now()->subMonths(6),
                    'fecha_vencimiento_condicion' => now()->addYears(3),
                    'valor_extra_condicion' => 'Nro Lic: LKA-452'
                ]);
            }
            if ($trabajador1 && $condAltura) {
                $trabajador1->condicionesTrabajadorMaestro()->attach($condAltura->id, [
                    'fecha_asignacion_condicion' => now()->subMonths(2),
                    'fecha_vencimiento_condicion' => now()->addYears(1),
                    'valor_extra_condicion' => 'Certificado Folio: C987'
                ]);
            }

            Trabajador::create([
                'empresa_contratista_id' => $contratistaAlpha->id,
                'rut_trabajador' => '16000002-2',
                'nombres_trabajador' => 'Luisa',
                'apellido_paterno_trabajador' => 'Méndez',
                'apellido_materno_trabajador' => 'Tapia',
                'fecha_nacimiento_trabajador' => '1990-07-20',
                'activo' => true,
            ]);
        }

        // --- Trabajadores para Contratista Beta ---
        if ($contratistaBeta) {
            $trabajador3 = Trabajador::create([
                'empresa_contratista_id' => $contratistaBeta->id,
                'rut_trabajador' => '17000003-3',
                'nombres_trabajador' => 'Pedro',
                'apellido_paterno_trabajador' => 'González',
                'apellido_materno_trabajador' => 'Pérez',
                'fecha_nacimiento_trabajador' => '1988-11-05',
                'activo' => true,
            ]);
            if ($trabajador3 && $condLicB) {
                 $trabajador3->condicionesTrabajadorMaestro()->attach($condLicB->id, [
                    'fecha_asignacion_condicion' => now()->subYear(),
                    'fecha_vencimiento_condicion' => now()->addYears(4),
                    'valor_extra_condicion' => 'Nro Lic: JHG-901'
                ]);
            }

            Trabajador::create([
                'empresa_contratista_id' => $contratistaBeta->id,
                'rut_trabajador' => '18000004-4',
                'nombres_trabajador' => 'Ana',
                'apellido_paterno_trabajador' => 'Silva',
                'apellido_materno_trabajador' => 'Castro',
                'fecha_nacimiento_trabajador' => '1992-01-30',
                'activo' => false, // Inactivo para ejemplo
            ]);
        }

        if (config('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
        $this->command->info('Trabajadores de ejemplo creados y condiciones asignadas.');
    }
}