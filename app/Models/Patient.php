<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Visit;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'medical_record_no',
        'name',
        'birth_date',
        'gender',
    ];

    // Relasi: Satu Pasien memiliki banyak Kunjungan (1 to Many)
    public function visits()
    {
        return $this->hasMany(Visit::class);
    }
}
