<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User; // Necesario si asignamos un rol a un usuario específico aquí

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // --- CREACIÓN DE PERMISOS ---
        // Los permisos pueden ser tan granulares como necesites.
        // Ejemplos: 'ver listado X', 'crear X', 'editar X', 'eliminar X', 'validar documento', etc.

        // Permisos para Admin ASEM (podría tener todos o ser super-admin)
        Permission::firstOrCreate(['name' => 'administrar plataforma']); // Permiso muy amplio

        // Permisos para Catálogos (ejemplo: Tipos de Documento)
        Permission::firstOrCreate(['name' => 'gestionar tipos_documentos']);
        Permission::firstOrCreate(['name' => 'gestionar criterios_evaluacion']);
        Permission::firstOrCreate(['name' => 'gestionar condiciones_maestro']); // Para ambas condiciones (empresa y trabajador)

        // Permisos para Entidades
        Permission::firstOrCreate(['name' => 'gestionar empresas_mandantes']);
        Permission::firstOrCreate(['name' => 'gestionar empresas_contratistas_plataforma']); // Admin ASEM gestiona qué contratistas existen
        Permission::firstOrCreate(['name' => 'gestionar cargos_plataforma']);
        Permission::firstOrCreate(['name' => 'gestionar vinculaciones_plataforma']);

        // Permisos para Configuración de Exigencias
        Permission::firstOrCreate(['name' => 'gestionar matriz_exigencias']);

        // Permisos para Analistas ASEM
        Permission::firstOrCreate(['name' => 'revisar documentos_pendientes']);
        Permission::firstOrCreate(['name' => 'validar documentos']);
        Permission::firstOrCreate(['name' => 'rechazar documentos']);

        // Permisos para Contratistas (Admin de la Contratista)
        Permission::firstOrCreate(['name' => 'gestionar mi_empresa_contratista']); // Datos de su propia empresa
        Permission::firstOrCreate(['name' => 'gestionar mis_trabajadores']);
        Permission::firstOrCreate(['name' => 'gestionar mis_vehiculos']);
        Permission::firstOrCreate(['name' => 'cargar mis_documentos']);
        Permission::firstOrCreate(['name' => 'ver mis_documentos_requeridos']);

        // --- CREACIÓN DE ROLES ---
        $roleAdminAsem = Role::firstOrCreate(['name' => 'AdminASEM']);
        $roleAnalistaAsem = Role::firstOrCreate(['name' => 'AnalistaASEM']);
        $roleContratistaAdmin = Role::firstOrCreate(['name' => 'ContratistaAdmin']);
        // Podríamos tener un rol 'ContratistaOperador' con menos permisos que ContratistaAdmin.

        // --- ASIGNACIÓN DE PERMISOS A ROLES ---

        // AdminASEM (tiene casi todos los permisos de administración de la plataforma)
        $roleAdminAsem->givePermissionTo([
            'administrar plataforma', // Si este es un permiso global que engloba todo
            'gestionar tipos_documentos',
            'gestionar criterios_evaluacion',
            'gestionar condiciones_maestro',
            'gestionar empresas_mandantes',
            'gestionar empresas_contratistas_plataforma',
            'gestionar cargos_plataforma',
            'gestionar vinculaciones_plataforma',
            'gestionar matriz_exigencias',
            // También podría tener permisos de analista si fuera necesario
            'revisar documentos_pendientes',
            'validar documentos',
            'rechazar documentos',
        ]);

        // AnalistaASEM
        $roleAnalistaAsem->givePermissionTo([
            'revisar documentos_pendientes',
            'validar documentos',
            'rechazar documentos',
            // Podría tener permisos de consulta sobre catálogos o entidades si es necesario para su labor
            // 'ver tipos_documentos', 'ver criterios_evaluacion', etc. (crear estos permisos si se necesitan)
        ]);

        // ContratistaAdmin
        $roleContratistaAdmin->givePermissionTo([
            'gestionar mi_empresa_contratista',
            'gestionar mis_trabajadores',
            'gestionar mis_vehiculos',
            'cargar mis_documentos',
            'ver mis_documentos_requeridos',
        ]);

        // Opcional: Crear un usuario Super Admin y asignarle el rol AdminASEM
        // Esto es mejor hacerlo en UserSeeder para mantener la lógica separada,
        // pero si solo es uno, podría ir aquí. Por ahora, lo dejamos para UserSeeder.

        $this->command->info('Roles y Permisos creados y asignados exitosamente.');
    }
}