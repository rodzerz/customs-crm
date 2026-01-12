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
    $table->foreignId('case_id')->constrained();
    $table->string('hs_code', 10);
    $table->decimal('weight', 10, 2);
    $table->decimal('value', 12, 2);
    $table->string('currency', 3);
    $table->string('origin_country', 2);
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
