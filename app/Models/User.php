<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Doctor;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'doctor_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function doctorProfile()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // Izinkan login ke dashboard jika role pengguna valid
        return in_array($this->role, ['admin', 'doctor', config('roles.default', 'admin')]);
        // Catatan: Anda bisa mengganti return menjadi `true` jika ingin semua orang bisa login.
    }
}
