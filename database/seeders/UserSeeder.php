<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar la tabla de usuarios antes de sembrar, EXCEPTO si tienes un usuario admin que quieres conservar.
        // User::truncate(); // CUIDADO: esto elimina todos los usuarios.
        // Si vas a ejecutar seeders múltiples veces, considera una estrategia para no duplicar o para actualizar.
        // Por ahora, crearemos usuarios solo si no existen con ese email.

        // --- Usuario Admin ASEM ---
        $adminAsem = User::firstOrCreate(
            ['email' => 'admin.asem@example.com'],
            [
                'name' => 'Administrador ASEM Plataforma',
                'password' => Hash::make('password'), // Cambia 'password' por una contraseña segura
            ]
        );
        // Asignar rol 'AdminASEM' si existe, si no, se puede crear aquí también o fallar.
        // Asumimos que RoleAndPermissionSeeder ya se ejecutó.
        if ($adminAsem && Role::where('name', 'AdminASEM')->exists()) {
            $adminAsem->assignRole('AdminASEM');
        }

        // --- Usuario Analista ASEM ---
        $analistaAsem = User::firstOrCreate(
            ['email' => 'analista.asem@example.com'],
            [
                'name' => 'Analista ASEM Validador',
                'password' => Hash::make('password'), // Cambia 'password'
            ]
        );
        if ($analistaAsem && Role::where('name', 'AnalistaASEM')->exists()) {
            $analistaAsem->assignRole('AnalistaASEM');
        }

        // --- Usuario para Empresa Contratista 1 (Admin de la Contratista) ---
        $contratistaAdmin1 = User::firstOrCreate(
            ['email' => 'contratista.admin1@example.com'],
            [
                'name' => 'Admin Contratista Uno',
                'password' => Hash::make('password'), // Cambia 'password'
            ]
        );
        if ($contratistaAdmin1 && Role::where('name', 'ContratistaAdmin')->exists()) {
            $contratistaAdmin1->assignRole('ContratistaAdmin');
        }
        // Nota: La vinculación de este usuario con una EmpresaContratista específica
        // se hará en el EmpresaContratistaSeeder.

        // --- Usuario para Empresa Contratista 2 (Admin de la Contratista) ---
        $contratistaAdmin2 = User::firstOrCreate(
            ['email' => 'contratista.admin2@example.com'],
            [
                'name' => 'Admin Contratista Dos',
                'password' => Hash::make('password'), // Cambia 'password'
            ]
        );
        if ($contratistaAdmin2 && Role::where('name', 'ContratistaAdmin')->exists()) {
            $contratistaAdmin2->assignRole('ContratistaAdmin');
        }


        // Puedes añadir más usuarios de prueba según necesites.

        // Si quieres crear usuarios con Factory para pruebas más masivas (después de configurar la factory):
        // User::factory()->count(5)->create()->each(function ($user) {
        //     // Asignar un rol por defecto a usuarios de factory si es necesario
        //     // $user->assignRole('AlgunRolPorDefecto');
        // });

        $this->command->info('Usuarios de prueba creados y roles asignados.');
    }
}