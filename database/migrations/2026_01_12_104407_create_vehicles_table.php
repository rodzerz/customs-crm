<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::create('vehicles', function (Blueprint $table) {
    $table->id();
    $table->string('external_id')->unique(); // veh-000001
    $table->string('plate_no', 20)->index();
    $table->char('country', 2);
    $table->string('make')->nullable();
    $table->string('model')->nullable();
    $table->string('vin', 32)->unique();
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
