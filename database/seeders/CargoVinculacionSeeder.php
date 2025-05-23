<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cargo;
use App\Models\Vinculacion;
use App\Models\EmpresaMandante;
use App\Models\ConfiguracionDocumentoMandante; // <--- ESTA ES LA LÍNEA CLAVE QUE FALTABA IMPORTAR
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CargoVinculacionSeeder extends Seeder
{
    public function run(): void
    {
        if (config('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }
        
        // Desasociar cargos y vinculaciones de configuraciones existentes ANTES de truncar
        // para evitar problemas de claves foráneas si hay datos.
        ConfiguracionDocumentoMandante::whereNotNull('cargo_id')->update(['cargo_id' => null]);
        ConfiguracionDocumentoMandante::whereNotNull('vinculacion_id')->update(['vinculacion_id' => null]);

        // Truncar las tablas de Cargo y Vinculacion
        Cargo::truncate();
        Vinculacion::truncate();

        // Obtener las empresas mandantes de ejemplo
        $mandanteMinera = EmpresaMandante::where('rut_empresa_mandante', '76000001-K')->first();
        $mandanteConstructora = EmpresaMandante::where('rut_empresa_mandante', '77000002-8')->first();

        // Verificar que las empresas mandantes existan
        if (!$mandanteMinera || !$mandanteConstructora) {
            $this->command->error('CargoVinculacionSeeder: No se encontraron las empresas mandantes de ejemplo (Minera o Constructora). Asegúrate de que EmpresaMandanteSeeder se haya ejecutado correctamente y existan estos mandantes.');
            Log::error('CargoVinculacionSeeder: No se encontraron las empresas mandantes de ejemplo (Minera o Constructora).');
            if (config('database.default') === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;'); // Restaurar claves foráneas si se desactivaron
            }
            return; // Detener la ejecución del seeder si no se encuentran los mandantes
        }

        // --- CARGOS ---
        // Todos los cargos ahora DEBEN tener un empresa_mandante_id

        // Cargos para Mandante Minera
        Cargo::create(['empresa_mandante_id' => $mandanteMinera->id, 'nombre_cargo' => 'Operador Camión Extracción', 'descripcion_cargo' => 'Conductor de camiones de alto tonelaje en faena minera.']);
        Cargo::create(['empresa_mandante_id' => $mandanteMinera->id, 'nombre_cargo' => 'Soldador Calificado 6G', 'descripcion_cargo' => 'Soldador con certificación 6G para estructuras críticas.']);
        Cargo::create(['empresa_mandante_id' => $mandanteMinera->id, 'nombre_cargo' => 'Supervisor Eléctrico Mina', 'descripcion_cargo' => 'Supervisor de trabajos eléctricos en mina.']);
        Cargo::create(['empresa_mandante_id' => $mandanteMinera->id, 'nombre_cargo' => 'Geólogo de Exploración', 'descripcion_cargo' => 'Responsable de la exploración geológica en la mina.']);

        // Cargos para Mandante Constructora
        Cargo::create(['empresa_mandante_id' => $mandanteConstructora->id, 'nombre_cargo' => 'Jefe de Obra', 'descripcion_cargo' => 'Responsable máximo de la ejecución de la obra.']);
        Cargo::create(['empresa_mandante_id' => $mandanteConstructora->id, 'nombre_cargo' => 'Albañil', 'descripcion_cargo' => 'Trabajador de la construcción especializado en albañilería.']);
        Cargo::create(['empresa_mandante_id' => $mandanteConstructora->id, 'nombre_cargo' => 'Prevencionista de Riesgos en Obra', 'descripcion_cargo' => 'Encargado de la seguridad y prevención en la obra.']);
        Cargo::create(['empresa_mandante_id' => $mandanteConstructora->id, 'nombre_cargo' => 'Topógrafo', 'descripcion_cargo' => 'Realiza mediciones y levantamientos topográficos.']);


        // --- VINCULACIONES ---
        // Todas las vinculaciones ahora DEBEN tener un empresa_mandante_id

        // Vinculaciones para Mandante Minera
        $vMineraGerOper = Vinculacion::create(['empresa_mandante_id' => $mandanteMinera->id, 'nombre_vinculacion' => 'Gerencia Operaciones Mina', 'descripcion_vinculacion' => 'Gerencia principal de operaciones en la mina.']);
        if($vMineraGerOper) {
            Vinculacion::create(['empresa_mandante_id' => $mandanteMinera->id, 'nombre_vinculacion' => 'Superintendencia Mantenimiento Mina', 'descripcion_vinculacion' => 'Área de mantenimiento dentro de operaciones.', 'parent_id' => $vMineraGerOper->id]);
            Vinculacion::create(['empresa_mandante_id' => $mandanteMinera->id, 'nombre_vinculacion' => 'Departamento Chancado Primario', 'descripcion_vinculacion' => 'Departamento específico de chancado.', 'parent_id' => $vMineraGerOper->id]);
        }
        Vinculacion::create(['empresa_mandante_id' => $mandanteMinera->id, 'nombre_vinculacion' => 'Gerencia SSO Mina', 'descripcion_vinculacion' => 'Gerencia de Seguridad y Salud Ocupacional.']);


        // Vinculaciones para Mandante Constructora
        $vConstructoraProy = Vinculacion::create(['empresa_mandante_id' => $mandanteConstructora->id, 'nombre_vinculacion' => 'Dirección de Proyectos Construcción', 'descripcion_vinculacion' => 'Dirección general de los proyectos.']);
        if($vConstructoraProy) {
            Vinculacion::create(['empresa_mandante_id' => $mandanteConstructora->id, 'nombre_vinculacion' => 'Proyecto Edificio Central', 'descripcion_vinculacion' => 'Vinculado al proyecto específico del Edificio Central.', 'parent_id' => $vConstructoraProy->id]);
            Vinculacion::create(['empresa_mandante_id' => $mandanteConstructora->id, 'nombre_vinculacion' => 'Obras Viales Ruta 5', 'descripcion_vinculacion' => 'Vinculado a las obras en la Ruta 5.', 'parent_id' => $vConstructoraProy->id]);
        }
        Vinculacion::create(['empresa_mandante_id' => $mandanteConstructora->id, 'nombre_vinculacion' => 'Departamento de Adquisiciones', 'descripcion_vinculacion' => 'Responsable de compras y adquisiciones.']);

        if (config('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
        $this->command->info('Cargos y Vinculaciones de ejemplo creados (todos asignados a mandantes específicos).');
    }
}