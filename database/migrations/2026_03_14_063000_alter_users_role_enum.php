<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Ubah role enum menjadi hanya 'admin' dan 'user' (prototype sederhana).
     */
    public function up(): void
    {
        // Pertama: set semua user yang ada ke 'admin' agar tidak ada data conflict (Database Agnostic)
        DB::table('users')->whereIn('role', ['nurse', 'doctor'])->update(['role' => 'admin']);

        // Lalu: ubah definisi ENUM
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'user') NOT NULL DEFAULT 'user'");
        } else {
            // PostgreSQL handling
            Schema::table('users', function (Blueprint $table) {
                // In Postgres, we'll convert it to a string temporarily or use a change() if supported.
                // For simplicity and compatibility during migration, we reflect the current project state.
                $table->string('role')->default('user')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'nurse', 'doctor') NOT NULL DEFAULT 'nurse'");
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role')->default('nurse')->change();
            });
        }
    }
};

