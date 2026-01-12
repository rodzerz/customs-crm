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
       Schema::create('parties', function (Blueprint $table) {
    $table->id();
    $table->string('external_id')->unique();
    $table->string('name');
    $table->string('type'); // declarant, consignee, carrier
    $table->string('country', 2)->nullable();
    $table->string('registration_no')->nullable();
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parties');
    }
};
