<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Admin\CondicionContratistaMaestroController;
use App\Http\Controllers\Api\Admin\TipoDocumentoController;
use App\Http\Controllers\Api\Admin\CriterioEvaluacionMaestroController;
use App\Http\Controllers\Api\Admin\CondicionTrabajadorMaestroController;
use App\Http\Controllers\Api\Admin\EmpresaMandanteController;
use App\Http\Controllers\Api\Admin\CargoController;
use App\Http\Controllers\Api\Admin\VinculacionController;
use App\Http\Controllers\Api\Admin\EmpresaContratistaController;
use App\Http\Controllers\Api\Admin\ConfiguracionDocumentoMandanteController; // <--- AÑADE ESTE IMPORT

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Rutas de Autenticación
Route::post('/login', [AuthController::class, 'login'])->name('api.login');

// Rutas protegidas por Sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('/user', [AuthController::class, 'user'])->name('api.user');

    // --- GRUPO DE RUTAS PARA ASEM ADMIN ---
    // Todas las rutas aquí requieren que el usuario autenticado tenga el rol 'AdminASEM'
    Route::middleware(['role:AdminASEM'])->prefix('admin')->name('admin.')->group(function () {
        
        Route::apiResource('condiciones-contratista-maestro', CondicionContratistaMaestroController::class);
        Route::apiResource('tipos-documentos', TipoDocumentoController::class);
        Route::apiResource('criterios-evaluacion-maestro', CriterioEvaluacionMaestroController::class);
        Route::apiResource('condiciones-trabajador-maestro', CondicionTrabajadorMaestroController::class);
        Route::apiResource('empresas-mandantes', EmpresaMandanteController::class);
        Route::apiResource('cargos', CargoController::class);
        Route::apiResource('vinculaciones', VinculacionController::class);
        Route::apiResource('empresas-contratistas', EmpresaContratistaController::class);
        Route::apiResource('configuraciones-documentos-mandante', ConfiguracionDocumentoMandanteController::class); // <--- AÑADE ESTA LÍNEA
        
    });

    // --- GRUPO DE RUTAS PARA CONTRATISTA ADMIN ---
    // Route::middleware(['role:ContratistaAdmin'])->prefix('contratista')->name('contratista.')->group(function () {
        // Endpoints para que el ContratistaAdmin gestione su propia empresa, trabajadores, vehículos, documentos.
    // });

    // --- GRUPO DE RUTAS PARA ANALISTA ASEM ---
    // Route::middleware(['role:AnalistaASEM'])->prefix('analista')->name('analista.')->group(function () {
        // Endpoints para que el AnalistaASEM revise y valide/rechace documentos.
    // });

});

// Ruta de prueba que puedes eliminar luego, si aún existe
Route::get('/test', function () {
    return response()->json(['message' => 'API funcionando sin autenticación']);
});