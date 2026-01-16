<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        // Create admin user
        $admin = User::firstOrCreate([
            'email' => 'admin@example.com',
        ], [
            'name' => 'Admin User',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');

        // Create sample users for each role
        $inspector = User::firstOrCreate([
            'email' => 'inspector@example.com',
        ], [
            'name' => 'Inspector User',
            'password' => bcrypt('password'),
        ]);
        $inspector->assignRole('inspector');

        $analyst = User::firstOrCreate([
            'email' => 'analyst@example.com',
        ], [
            'name' => 'Analyst User',
            'password' => bcrypt('password'),
        ]);
        $analyst->assignRole('analyst');

        $broker = User::firstOrCreate([
            'email' => 'broker@example.com',
        ], [
            'name' => 'Broker User',
            'password' => bcrypt('password'),
        ]);
        $broker->assignRole('broker');
    }
}
