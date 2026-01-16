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
    $table->string('external_id')->nullable()->unique();
    $table->foreignId('case_id')->constrained();
    $table->string('type')->nullable(); // document, rtg, physical
    $table->string('status')->nullable(); // release, hold, reject
    $table->text('comment')->nullable();
    $table->timestamp('performed_at')->nullable();
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
