<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\DeviceReading;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'mac_address',
        'device_type',
        'name',
        'is_active',
    ];

    // Relasi: Satu Alat bisa menghasilkan banyak data pengukuran (1 to Many)
    public function deviceReadings()
    {
        return $this->hasMany(DeviceReading::class);
    }
}
