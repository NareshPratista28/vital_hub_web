<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\DeviceReading;
use App\Models\Measurement;

class Visit extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'visit_date',
        'status',
    ];

    protected $casts = [
        'visit_date' => 'datetime',
    ];

    // Relasi: Kunjungan ini milik satu Pasien (Many to 1)
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    // Relasi: Kunjungan ini ditangani oleh satu Dokter (Many to 1)
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    // Relasi: Dalam satu Kunjungan, ada banyak sesi Pengukuran oximeter (1 to Many)
    public function measurements()
    {
        return $this->hasMany(Measurement::class);
    }

    // Legacy — dipertahankan jika ada data lama
    public function deviceReadings()
    {
        return $this->hasMany(DeviceReading::class);
    }
}
