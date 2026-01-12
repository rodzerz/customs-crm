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
        Schema::create('cases', function (Blueprint $table) {
    $table->id();
    $table->string('external_id')->unique();
    $table->foreignId('vehicle_id')->constrained();
    $table->string('status')->index(); 
    // new, screening, in_inspection, on_hold, released, closed
    $table->integer('risk_score')->default(0);
    $table->timestamp('arrived_at')->nullable();
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cases');
    }
};
