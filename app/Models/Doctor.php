<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Visit;

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'name',
        'specialization',
    ];

    // Relasi: Satu Dokter bisa menangani banyak Kunjungan (1 to Many)
    public function visits()
    {
        return $this->hasMany(Visit::class);
    }
}
