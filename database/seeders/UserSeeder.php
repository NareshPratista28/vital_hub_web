<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Suster Siti',
            'email' => 'nurse@vitalhub.com',
            'password' => Hash::make('password123'),
            'role' => 'nurse',
        ]);

        User::create([
            'name' => 'Admin IT',
            'email' => 'admin@vitalhub.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);
    }
}
