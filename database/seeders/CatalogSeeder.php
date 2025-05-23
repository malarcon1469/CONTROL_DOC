<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoDocumento;
use App\Models\CriterioEvaluacionMaestro;
use App\Models\CondicionContratistaMaestro;
use App\Models\CondicionTrabajadorMaestro;
use Illuminate\Support\Facades\DB;

class CatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Para MySQL, desactivar chequeo de FKs si vamos a truncar
        if (config('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        // Truncar tablas (opcional, pero útil para consistencia si se ejecuta varias veces)
        // Asegúrate que el orden de truncado sea correcto si hay FKs, o desactiva FKs como arriba.
        TipoDocumento::truncate();
        CriterioEvaluacionMaestro::truncate();
        CondicionContratistaMaestro::truncate();
        CondicionTrabajadorMaestro::truncate();


        // --- Tipos de Documento ---
        TipoDocumento::create(['nombre' => 'Cédula de Identidad', 'descripcion' => 'Documento Nacional de Identificación.', 'es_vencible' => true, 'requiere_archivo' => true]);
        TipoDocumento::create(['nombre' => 'Licencia de Conducir', 'descripcion' => 'Autorización para conducir vehículos.', 'es_vencible' => true, 'requiere_archivo' => true]);
        TipoDocumento::create(['nombre' => 'Certificado de Antecedentes', 'descripcion' => 'Certificado de antecedentes penales.', 'es_vencible' => true, 'requiere_archivo' => true]);
        TipoDocumento::create(['nombre' => 'Contrato de Trabajo', 'descripcion' => 'Documento legal de vinculación laboral.', 'es_vencible' => false, 'requiere_archivo' => true]);
        TipoDocumento::create(['nombre' => 'Certificado AFP', 'descripcion' => 'Certificado de cotizaciones previsionales.', 'es_vencible' => false, 'requiere_archivo' => true]);
        TipoDocumento::create(['nombre' => 'Certificado Salud', 'descripcion' => 'Certificado de cotizaciones de salud.', 'es_vencible' => false, 'requiere_archivo' => true]);
        TipoDocumento::create(['nombre' => 'Examen de Altura Física', 'descripcion' => 'Certifica aptitud para trabajos en altura.', 'es_vencible' => true, 'requiere_archivo' => true]);
        TipoDocumento::create(['nombre' => 'Permiso de Circulación', 'descripcion' => 'Documento vehicular obligatorio.', 'es_vencible' => true, 'requiere_archivo' => true]);
        TipoDocumento::create(['nombre' => 'Seguro Obligatorio (SOAP)', 'descripcion' => 'Seguro obligatorio de accidentes personales para vehículos.', 'es_vencible' => true, 'requiere_archivo' => true]);
        TipoDocumento::create(['nombre' => 'Revisión Técnica Vehicular', 'descripcion' => 'Certificado de inspección técnica de vehículos.', 'es_vencible' => true, 'requiere_archivo' => true]);
        TipoDocumento::create(['nombre' => 'Escritura de Constitución Empresa', 'descripcion' => 'Documento legal de creación de la empresa.', 'es_vencible' => false, 'requiere_archivo' => true]);
        TipoDocumento::create(['nombre' => 'Certificado Vigencia Empresa', 'descripcion' => 'Certificado de vigencia de la sociedad.', 'es_vencible' => true, 'requiere_archivo' => true]);


        // --- Criterios de Evaluación Maestro ---
        CriterioEvaluacionMaestro::create(['nombre_criterio' => 'Legibilidad del Documento', 'descripcion_criterio' => 'El documento es claro y se puede leer sin dificultad.']);
        CriterioEvaluacionMaestro::create(['nombre_criterio' => 'Vigencia del Documento', 'descripcion_criterio' => 'La fecha de vencimiento del documento es posterior a la fecha actual.']);
        CriterioEvaluacionMaestro::create(['nombre_criterio' => 'Datos Coinciden', 'descripcion_criterio' => 'La información del documento coincide con los datos del trabajador/empresa/vehículo.']);
        CriterioEvaluacionMaestro::create(['nombre_criterio' => 'Firma y Timbre', 'descripcion_criterio' => 'El documento cuenta con las firmas y/o timbres requeridos.']);
        CriterioEvaluacionMaestro::create(['nombre_criterio' => 'Completo y Sin Enmiendas', 'descripcion_criterio' => 'El documento está completo y no presenta alteraciones.']);


        // --- Condiciones de Contratista Maestro ---
        CondicionContratistaMaestro::create(['nombre_condicion' => 'Certificación ISO 9001', 'descripcion_condicion' => 'La empresa posee certificación ISO 9001 vigente.']);
        CondicionContratistaMaestro::create(['nombre_condicion' => 'Inscrita en ChileProveedores', 'descripcion_condicion' => 'La empresa está inscrita y habilitada en ChileProveedores.']);
        CondicionContratistaMaestro::create(['nombre_condicion' => 'Plan de Emergencia Aprobado', 'descripcion_condicion' => 'La empresa cuenta con un plan de emergencia aprobado.']);
        CondicionContratistaMaestro::create(['nombre_condicion' => 'No registra deudas previsionales', 'descripcion_condicion' => 'La empresa no registra deudas previsionales.']);


        // --- Condiciones de Trabajador Maestro ---
        CondicionTrabajadorMaestro::create(['nombre_condicion' => 'Licencia de Conducir Clase A4', 'descripcion_condicion' => 'Habilitado para conducir camiones simples.']);
        CondicionTrabajadorMaestro::create(['nombre_condicion' => 'Licencia de Conducir Clase B', 'descripcion_condicion' => 'Habilitado para conducir vehículos particulares.']);
        CondicionTrabajadorMaestro::create(['nombre_condicion' => 'Curso Trabajos en Altura', 'descripcion_condicion' => 'Certificación para realizar trabajos en altura física.']);
        CondicionTrabajadorMaestro::create(['nombre_condicion' => 'Examen Psicosensotécnico Riguroso', 'descripcion_condicion' => 'Aprobación de examen psicotécnico específico.']);

        if (config('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
        $this->command->info('Catálogos universales poblados exitosamente.');
    }
}