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
        Schema::create('inspections', function (Blueprint $table) {
    $table->id();
    $table->string('external_id')->unique();
    $table->foreignId('case_id')->constrained();
    $table->string('type'); // document, rtg, physical
    $table->string('result'); // release, hold, reject
    $table->text('comment')->nullable();
    $table->foreignId('performed_by')->constrained('users');
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspections');
    }
};
