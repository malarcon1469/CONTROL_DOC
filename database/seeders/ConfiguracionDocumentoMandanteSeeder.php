<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ConfiguracionDocumentoMandante;
use App\Models\EmpresaMandante;
use App\Models\TipoDocumento;
use App\Models\Cargo;
use App\Models\Vinculacion;
use App\Models\CondicionContratistaMaestro;
use App\Models\CondicionTrabajadorMaestro;
use Illuminate\Support\Facades\DB;

class ConfiguracionDocumentoMandanteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (config('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }
        // No truncamos configuracion_mandante_criterios aquí, se hace en su propio seeder
        ConfiguracionDocumentoMandante::truncate();

        // Obtener entidades para las configuraciones
        $mandanteMinera = EmpresaMandante::where('rut_empresa_mandante', '76000001-K')->first();
        $mandanteConstructora = EmpresaMandante::where('rut_empresa_mandante', '77000002-8')->first();

        $docCedula = TipoDocumento::where('nombre', 'Cédula de Identidad')->first();
        $docLicencia = TipoDocumento::where('nombre', 'Licencia de Conducir')->first();
        $docContrato = TipoDocumento::where('nombre', 'Contrato de Trabajo')->first();
        $docExamenAltura = TipoDocumento::where('nombre', 'Examen de Altura Física')->first();
        $docPermisoCirculacion = TipoDocumento::where('nombre', 'Permiso de Circulación')->first();
        $docEscrituraEmpresa = TipoDocumento::where('nombre', 'Escritura de Constitución Empresa')->first();


        $cargoOpCamion = Cargo::where('nombre_cargo', 'Operador Camión Extracción')->first(); // Específico de Minera
        $cargoJefeObra = Cargo::where('nombre_cargo', 'Jefe de Obra')->first(); // Específico de Constructora
        $cargoGlobalAdmin = Cargo::where('nombre_cargo', 'Administrativo General')->whereNull('empresa_mandante_id')->first();


        $vincGerOperMinera = Vinculacion::where('nombre_vinculacion', 'Gerencia Operaciones Mina')->first(); // Específica Minera
        $vincProyectoEdCentral = Vinculacion::where('nombre_vinculacion', 'Proyecto Edificio Central')->first(); // Específica Constructora


        $condContratistaISO = CondicionContratistaMaestro::where('nombre_condicion', 'Certificación ISO 9001')->first();
        $condTrabajadorAltura = CondicionTrabajadorMaestro::where('nombre_condicion', 'Curso Trabajos en Altura')->first();

        // --- Configuraciones para Mandante Minera ---
        if ($mandanteMinera && $docCedula) {
            ConfiguracionDocumentoMandante::create([
                'empresa_mandante_id' => $mandanteMinera->id,
                'tipo_documento_id' => $docCedula->id,
                'entidad_controlada' => 'TRABAJADOR', // Para todos los trabajadores
                'es_obligatorio' => true,
            ]);
        }
        if ($mandanteMinera && $docContrato) {
             ConfiguracionDocumentoMandante::create([
                'empresa_mandante_id' => $mandanteMinera->id,
                'tipo_documento_id' => $docContrato->id,
                'entidad_controlada' => 'TRABAJADOR', // Para todos los trabajadores
                'es_obligatorio' => true,
            ]);
        }
        if ($mandanteMinera && $docLicencia && $cargoOpCamion) {
            ConfiguracionDocumentoMandante::create([
                'empresa_mandante_id' => $mandanteMinera->id,
                'tipo_documento_id' => $docLicencia->id,
                'entidad_controlada' => 'TRABAJADOR',
                'cargo_id' => $cargoOpCamion->id, // Solo para Operadores de Camión
                'es_obligatorio' => true,
            ]);
        }
        if ($mandanteMinera && $docExamenAltura && $vincGerOperMinera) {
            ConfiguracionDocumentoMandante::create([
                'empresa_mandante_id' => $mandanteMinera->id,
                'tipo_documento_id' => $docExamenAltura->id,
                'entidad_controlada' => 'TRABAJADOR',
                'vinculacion_id' => $vincGerOperMinera->id, // Para todos los trabajadores en Gerencia Operaciones
                'es_obligatorio' => true,
                'observaciones' => 'Examen debe tener menos de 1 año de antigüedad.'
            ]);
        }
        if ($mandanteMinera && $docPermisoCirculacion) {
            ConfiguracionDocumentoMandante::create([
                'empresa_mandante_id' => $mandanteMinera->id,
                'tipo_documento_id' => $docPermisoCirculacion->id,
                'entidad_controlada' => 'VEHICULO', // Para todos los vehículos
                'es_obligatorio' => true,
            ]);
        }
        // Configuración que depende de una condición de la empresa contratista
        if ($mandanteMinera && $docEscrituraEmpresa && $condContratistaISO) {
            ConfiguracionDocumentoMandante::create([
                'empresa_mandante_id' => $mandanteMinera->id,
                'tipo_documento_id' => $docEscrituraEmpresa->id, // Ejemplo, podría ser otro doc
                'entidad_controlada' => 'EMPRESA',
                'condicion_contratista_id' => $condContratistaISO->id, // Requerido si la contratista es ISO 9001
                'es_obligatorio' => true,
                'observaciones' => 'Solo si la contratista declara tener ISO 9001.'
            ]);
        }
        // Configuración que depende de una condición del trabajador
         if ($mandanteMinera && $docExamenAltura && $condTrabajadorAltura) { // Reutilizo examen de altura como ejemplo
            ConfiguracionDocumentoMandante::create([
                'empresa_mandante_id' => $mandanteMinera->id,
                'tipo_documento_id' => $docExamenAltura->id,
                'entidad_controlada' => 'TRABAJADOR',
                'condicion_trabajador_id' => $condTrabajadorAltura->id, // Requerido si el trabajador tiene curso de altura
                'es_obligatorio' => true,
                'observaciones' => 'Adjuntar certificado del curso de altura si el trabajador lo posee.'
            ]);
        }


        // --- Configuraciones para Mandante Constructora ---
        if ($mandanteConstructora && $docCedula) {
            ConfiguracionDocumentoMandante::create([
                'empresa_mandante_id' => $mandanteConstructora->id,
                'tipo_documento_id' => $docCedula->id,
                'entidad_controlada' => 'TRABAJADOR',
                'es_obligatorio' => true,
            ]);
        }
        if ($mandanteConstructora && $docContrato && $cargoJefeObra) {
             ConfiguracionDocumentoMandante::create([
                'empresa_mandante_id' => $mandanteConstructora->id,
                'tipo_documento_id' => $docContrato->id,
                'entidad_controlada' => 'TRABAJADOR',
                'cargo_id' => $cargoJefeObra->id, // Contrato solo para Jefe de Obra (ejemplo)
                'es_obligatorio' => true,
            ]);
        }
        if ($mandanteConstructora && $docEscrituraEmpresa) {
            ConfiguracionDocumentoMandante::create([
                'empresa_mandante_id' => $mandanteConstructora->id,
                'tipo_documento_id' => $docEscrituraEmpresa->id,
                'entidad_controlada' => 'EMPRESA',
                'es_obligatorio' => true,
            ]);
        }


        if (config('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
        $this->command->info('Configuraciones de documentos mandante de ejemplo creadas.');
    }
}