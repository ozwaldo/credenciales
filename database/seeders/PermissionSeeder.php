<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reiniciar cache de roles y permisos
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear permisos
        Permission::create(['name' => 'manage estudiantes']);
        Permission::create(['name' => 'manage visitantes']);
        Permission::create(['name' => 'manage eventos']);
        Permission::create(['name' => 'verify credenciales']);
        Permission::create(['name' => 'view own credencial']);
        Permission::create(['name' => 'access admin panel']); // Permiso para acceder al panel de administración se puede quitar

        // Rol Administrador
        $adminRole = Role::findByName('admin');
        if ($adminRole) {
            $adminRole->givePermissionTo([
                'manage estudiantes',
                'manage visitantes',
                'manage eventos',
                'verify credenciales',
                'access admin panel'
            ]);
        }

        // Rol Estudiante
        $studentRole = Role::findByName('estudiante');
        if ($studentRole) {
            $studentRole->givePermissionTo(['view own credencial']);
        }

        // Rol Visitante
        $visitanteRole = Role::findByName('visitante');
        if ($visitanteRole) {
            $visitanteRole->givePermissionTo(['verify credenciales']);
        }

        // Crear rol verificador
        $verificadorRole = Role::firstOrCreate(['name' => 'verificador']);
        if ($verificadorRole) {
            $verificadorRole->givePermissionTo([
                'verify credenciales',
                'access admin panel'
            ]);
        }
    }
}
