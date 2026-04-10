<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 1 baris = 1 sesi pengukuran oximeter (SpO2 + Pulse Rate sekaligus).
     */
    public function up(): void
    {
        Schema::create('measurements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('visit_id')
                ->constrained('visits')
                ->cascadeOnDelete();

            $table->foreignId('device_id')
                ->constrained('devices')
                ->cascadeOnDelete();

            // Nilai SpO2 (Saturasi Oksigen), contoh: 97.5 %
            $table->decimal('spo2', 5, 2)->comment('Saturasi oksigen (%)');

            // Nilai Pulse Rate (Detak Jantung), contoh: 72 bpm
            $table->unsignedSmallInteger('pulse_rate')->comment('Detak jantung (bpm)');

            // Status gabungan: diambil dari yang paling buruk antara SpO2 dan Pulse Rate
            $table->enum('vital_status', ['normal', 'warning', 'critical'])
                ->comment('Status gabungan: worst-case dari kedua parameter');

            // Waktu pengukuran dilakukan (dari device/app)
            $table->timestamp('recorded_at');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('measurements');
    }
};
