<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use Spatie\Permission\Models\Role;
use App\Models\User;

$admin = User::firstOrCreate([
    'email' => 'admin@example.com',
], [
    'name' => 'Admin User',
    'password' => bcrypt('password'),
]);
$admin->assignRole('admin');

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

echo "Default users created!\n";
