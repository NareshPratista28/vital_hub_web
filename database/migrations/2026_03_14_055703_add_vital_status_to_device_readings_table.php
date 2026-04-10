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
        Schema::table('device_readings', function (Blueprint $table) {
            $table->enum('vital_status', ['normal', 'warning', 'critical'])
                  ->nullable()
                  ->after('unit')
                  ->comment('Classification: normal, warning, or critical based on thresholds');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_readings', function (Blueprint $table) {
            $table->dropColumn('vital_status');
        });
    }
};
