<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $inspectorRole = Role::firstOrCreate(['name' => 'inspector']);
        $analystRole = Role::firstOrCreate(['name' => 'analyst']);
        $brokerRole = Role::firstOrCreate(['name' => 'broker']);

        // Create permissions
        $permissions = [
            'view vehicles',
            'view parties',
            'view cases',
            'view inspections',
            'view documents',
            'create cases',
            'update cases',
            'delete cases',
            'perform inspections',
            'manage users',
            'view analytics',
            'submit declarations',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign permissions to roles
        $adminRole->givePermissionTo(Permission::all());

        $inspectorRole->givePermissionTo([
            'view vehicles',
            'view parties',
            'view cases',
            'view inspections',
            'view documents',
            'update cases',
            'perform inspections',
        ]);

        $analystRole->givePermissionTo([
            'view vehicles',
            'view parties',
            'view cases',
            'view inspections',
            'view documents',
            'view analytics',
        ]);

        $brokerRole->givePermissionTo([
            'view cases',
            'view documents',
            'create cases',
            'submit declarations',
        ]);
    }
}