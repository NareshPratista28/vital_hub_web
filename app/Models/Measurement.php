<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Measurement extends Model
{
    use HasFactory;

    protected $fillable = [
        'visit_id',
        'device_id',
        'spo2',
        'pulse_rate',
        'vital_status',
        'recorded_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'spo2'        => 'decimal:2',
        'pulse_rate'  => 'integer',
    ];

    // Relasi: Pengukuran ini milik satu Kunjungan
    public function visit()
    {
        return $this->belongsTo(Visit::class);
    }

    // Relasi: Pengukuran ini dilakukan menggunakan satu Alat
    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * Hitung status vital gabungan dari SpO2 dan Pulse Rate.
     * Ambil yang paling buruk (worst-case).
     *
     * Threshold SpO2:
     *   Normal   : >= 95%
     *   Warning  : 90 - 94%
     *   Critical : < 90%
     *
     * Threshold Pulse Rate:
     *   Normal   : 60 - 100 bpm
     *   Warning  : 50-59 atau 101-120 bpm
     *   Critical : < 50 atau > 120 bpm
     */
    public static function computeOverallStatus(float $spo2, int $pulseRate): string
    {
        $spo2Status = match (true) {
            $spo2 >= 95   => 'normal',
            $spo2 >= 90   => 'warning',
            default       => 'critical',
        };

        $prStatus = match (true) {
            $pulseRate >= 60 && $pulseRate <= 100 => 'normal',
            ($pulseRate >= 50 && $pulseRate < 60) || ($pulseRate > 100 && $pulseRate <= 120) => 'warning',
            default => 'critical',
        };

        // Prioritas: critical > warning > normal
        $priority = ['normal' => 1, 'warning' => 2, 'critical' => 3];

        return ($priority[$spo2Status] >= $priority[$prStatus])
            ? $spo2Status
            : $prStatus;
    }

    /**
     * Helper warna badge Filament berdasarkan status
     */
    public static function statusColor(string $status): string
    {
        return match ($status) {
            'critical' => 'danger',
            'warning'  => 'warning',
            'normal'   => 'success',
            default    => 'gray',
        };
    }

    /**
     * Helper label status dalam Bahasa Indonesia
     */
    public static function statusLabel(string $status): string
    {
        return match ($status) {
            'critical' => '🚨 Kritis',
            'warning'  => '⚠️ Perhatian',
            'normal'   => '✅ Normal',
            default    => '—',
        };
    }
}
