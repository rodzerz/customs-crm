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
        Schema::table('cases', function (Blueprint $table) {
            // Add route, origin country, destination country
            $table->string('route')->nullable()->after('risk_score');
            $table->string('origin_country')->nullable()->after('route');
            $table->string('destination_country')->nullable()->after('origin_country');
            
            // Add declared and actual values
            $table->decimal('declared_value', 15, 2)->nullable()->after('destination_country');
            $table->decimal('actual_value', 15, 2)->nullable()->after('declared_value');
            
            // Add previous violations count
            $table->integer('previous_violations')->default(0)->after('actual_value');
            
            // Add risk reason
            $table->text('risk_reason')->nullable()->after('previous_violations');
            
            // Add status updated at
            $table->timestamp('status_updated_at')->nullable()->after('arrived_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->dropColumn([
                'route', 'origin_country', 'destination_country',
                'declared_value', 'actual_value', 'previous_violations',
                'risk_reason', 'status_updated_at'
            ]);
        });
    }
};
