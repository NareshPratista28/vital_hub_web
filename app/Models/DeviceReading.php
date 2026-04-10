<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Visit;
use App\Models\Device;

class DeviceReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'visit_id',
        'device_id',
        'reading_type',
        'reading_value',
        'unit',
        'vital_status',
        'recorded_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'reading_value' => 'decimal:2',
    ];

    // Relasi: Hasil ukur ini milik satu sesi Kunjungan tertentu (Many to 1)
    public function visit()
    {
        return $this->belongsTo(Visit::class);
    }

    // Relasi: Hasil ukur ini dihasilkan oleh satu Alat tertentu (Many to 1)
    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * Klasifikasikan status vital sign berdasarkan jenis dan nilai pengukuran.
     * Supported types: 'spo2', 'pulse_rate'
     */
    public static function classifyVitalStatus(string $type, float $value): string
    {
        return match (strtolower($type)) {
            'spo2' => match (true) {
                $value >= 95      => 'normal',
                $value >= 90      => 'warning',
                default           => 'critical',
            },
            'pulse_rate' => match (true) {
                $value >= 60 && $value <= 100 => 'normal',
                ($value >= 50 && $value < 60) || ($value > 100 && $value <= 120) => 'warning',
                default => 'critical',
            },
            // Untuk reading_type lain, default ke normal
            default => 'normal',
        };
    }

    /**
     * Helper: dapatkan warna badge Filament berdasarkan status
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
     * Helper: dapatkan label yang ditampilkan ke user
     */
    public static function statusLabel(string $status): string
    {
        return match ($status) {
            'critical' => '🚨 Kritis',
            'warning'  => '⚠️ Perhatian',
            'normal'   => '✅ Normal',
            default    => 'Tidak Diketahui',
        };
    }
}
