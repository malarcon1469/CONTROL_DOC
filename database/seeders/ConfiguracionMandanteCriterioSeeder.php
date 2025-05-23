<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ConfiguracionMandanteCriterio;
use App\Models\ConfiguracionDocumentoMandante;
use App\Models\CriterioEvaluacionMaestro;
use App\Models\EmpresaMandante;
use App\Models\TipoDocumento;
use Illuminate\Support\Facades\DB;

class ConfiguracionMandanteCriterioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (config('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }
        ConfiguracionMandanteCriterio::truncate();

        // Criterios
        $critLegible = CriterioEvaluacionMaestro::where('nombre_criterio', 'Legibilidad del Documento')->first();
        $critVigente = CriterioEvaluacionMaestro::where('nombre_criterio', 'Vigencia del Documento')->first();
        $critDatosCoinciden = CriterioEvaluacionMaestro::where('nombre_criterio', 'Datos Coinciden')->first();
        $critFirmaTimbre = CriterioEvaluacionMaestro::where('nombre_criterio', 'Firma y Timbre')->first();

        // Mandantes y Tipos de Documento para identificar configuraciones
        $mandanteMinera = EmpresaMandante::where('rut_empresa_mandante', '76000001-K')->first();
        $docCedula = TipoDocumento::where('nombre', 'Cédula de Identidad')->first();
        $docLicencia = TipoDocumento::where('nombre', 'Licencia de Conducir')->first();
        $docContrato = TipoDocumento::where('nombre', 'Contrato de Trabajo')->first();

        // --- Asignar criterios a Configuraciones Específicas ---

        // Ejemplo 1: Cédula de Identidad para Mandante Minera
        if ($mandanteMinera && $docCedula) {
            $configCedulaMinera = ConfiguracionDocumentoMandante::where('empresa_mandante_id', $mandanteMinera->id)
                                    ->where('tipo_documento_id', $docCedula->id)
                                    ->where('entidad_controlada', 'TRABAJADOR')
                                    ->first();

            if ($configCedulaMinera && $critLegible) {
                $configCedulaMinera->criteriosEvaluacionMaestro()->attach($critLegible->id, ['es_criterio_obligatorio' => true]);
            }
            if ($configCedulaMinera && $critVigente) {
                $configCedulaMinera->criteriosEvaluacionMaestro()->attach($critVigente->id, ['es_criterio_obligatorio' => true, 'instruccion_adicional_criterio' => 'Revisar especialmente la fecha de vencimiento.']);
            }
            if ($configCedulaMinera && $critDatosCoinciden) {
                $configCedulaMinera->criteriosEvaluacionMaestro()->attach($critDatosCoinciden->id, ['es_criterio_obligatorio' => true]);
            }
        }

        // Ejemplo 2: Licencia de Conducir para Mandante Minera (para Operador Camión)
        if ($mandanteMinera && $docLicencia) {
            // Necesitamos ser más específicos si hay varias configs para el mismo tipo de doc
            $cargoOpCamion = \App\Models\Cargo::where('nombre_cargo', 'Operador Camión Extracción')->first();
            if ($cargoOpCamion) {
                 $configLicenciaMineraOp = ConfiguracionDocumentoMandante::where('empresa_mandante_id', $mandanteMinera->id)
                                        ->where('tipo_documento_id', $docLicencia->id)
                                        ->where('cargo_id', $cargoOpCamion->id)
                                        ->where('entidad_controlada', 'TRABAJADOR')
                                        ->first();

                if ($configLicenciaMineraOp && $critLegible) {
                    $configLicenciaMineraOp->criteriosEvaluacionMaestro()->attach($critLegible->id); // es_obligatorio por defecto es true
                }
                if ($configLicenciaMineraOp && $critVigente) {
                    $configLicenciaMineraOp->criteriosEvaluacionMaestro()->attach($critVigente->id);
                }
                if ($configLicenciaMineraOp && $critDatosCoinciden) {
                     $configLicenciaMineraOp->criteriosEvaluacionMaestro()->attach($critDatosCoinciden->id, ['instruccion_adicional_criterio' => 'Verificar que la clase de licencia sea la correcta para el cargo.']);
                }
            }
        }

        // Ejemplo 3: Contrato de Trabajo para Mandante Minera
        if ($mandanteMinera && $docContrato) {
            $configContratoMinera = ConfiguracionDocumentoMandante::where('empresa_mandante_id', $mandanteMinera->id)
                                    ->where('tipo_documento_id', $docContrato->id)
                                    ->where('entidad_controlada', 'TRABAJADOR')
                                    ->first(); // Asumiendo que hay uno genérico para todos, sino filtrar más

            if ($configContratoMinera && $critLegible) {
                $configContratoMinera->criteriosEvaluacionMaestro()->attach($critLegible->id);
            }
            if ($configContratoMinera && $critFirmaTimbre) {
                $configContratoMinera->criteriosEvaluacionMaestro()->attach($critFirmaTimbre->id, ['instruccion_adicional_criterio' => 'Debe estar firmado por ambas partes.']);
            }
             if ($configContratoMinera && $critDatosCoinciden) {
                $configContratoMinera->criteriosEvaluacionMaestro()->attach($critDatosCoinciden->id);
            }
        }

        // Puedes añadir más asignaciones para otras configuraciones y mandantes

        if (config('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
        $this->command->info('Criterios de evaluación asignados a configuraciones de ejemplo.');
    }
}