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
        Schema::table('measurements', function (Blueprint $table) {
            $table->index('recorded_at');
            $table->index('vital_status');
            $table->index(['vital_status', 'recorded_at']);
        });

        Schema::table('visits', function (Blueprint $table) {
            $table->index('visit_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->dropIndex(['visit_date']);
            $table->dropIndex(['status']);
        });

        Schema::table('measurements', function (Blueprint $table) {
            $table->dropIndex(['recorded_at']);
            $table->dropIndex(['vital_status']);
            $table->dropIndex(['vital_status', 'recorded_at']);
        });
    }
};
