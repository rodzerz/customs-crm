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
        Schema::create('case_cargo_items', function (Blueprint $table) {
    $table->id();
    $table->string('external_id')->unique();
    $table->foreignId('case_id')->constrained();
    $table->string('hs_code', 10)->nullable();
    $table->text('description')->nullable();
    $table->decimal('weight', 10, 2)->nullable();
    $table->decimal('value', 12, 2)->nullable();
    $table->string('currency', 3)->nullable();
    $table->string('origin_country', 2)->nullable();
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('case_cargo_items');
    }
};
