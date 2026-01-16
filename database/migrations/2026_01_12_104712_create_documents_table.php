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
       Schema::create('documents', function (Blueprint $table) {
    $table->id();
    $table->string('external_id')->unique();
    $table->foreignId('case_id')->nullable()->constrained();
    $table->string('type')->nullable(); // invoice, declaration, rtg_image
    $table->string('file_path')->nullable();
    $table->timestamp('uploaded_at')->nullable();
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
