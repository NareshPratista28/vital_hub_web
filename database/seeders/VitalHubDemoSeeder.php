<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Device;
use App\Models\Visit;
use App\Models\Measurement;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class VitalHubDemoSeeder extends Seeder
{
    public function run(): void
    {
        // =====================================================
        // 1. Admin User
        // =====================================================
        User::updateOrCreate(
            ['email' => 'admin@vitalhub.com'],
            [
                'name'     => 'Admin VitalHub',
                'password' => Hash::make('password'),
                'role'     => 'admin',
            ]
        );

        // =====================================================
        // 2. Dokter (dipertahankan untuk data relasi Visit)
        // =====================================================
        $doctors = [
            ['employee_id' => 'DR001', 'name' => 'Dr. Rina Sari, Sp.PD',   'specialization' => 'Penyakit Dalam'],
            ['employee_id' => 'DR002', 'name' => 'Dr. Budi Santoso, Sp.JP', 'specialization' => 'Jantung dan Pembuluh Darah'],
        ];
        foreach ($doctors as $d) {
            Doctor::updateOrCreate(['employee_id' => $d['employee_id']], $d);
        }

        $doctor1 = Doctor::where('employee_id', 'DR001')->first();
        $doctor2 = Doctor::where('employee_id', 'DR002')->first();

        // =====================================================
        // 3. Oximeter Devices
        // =====================================================
        $oximeters = [
            ['mac_address' => 'AA:BB:CC:DD:EE:01', 'device_type' => 'oximeter', 'name' => 'Oximeter #1', 'is_active' => true],
            ['mac_address' => 'AA:BB:CC:DD:EE:02', 'device_type' => 'oximeter', 'name' => 'Oximeter #2', 'is_active' => true],
        ];
        foreach ($oximeters as $o) {
            Device::updateOrCreate(['mac_address' => $o['mac_address']], $o);
        }

        $device1 = Device::where('mac_address', 'AA:BB:CC:DD:EE:01')->first();
        $device2 = Device::where('mac_address', 'AA:BB:CC:DD:EE:02')->first();

        // =====================================================
        // 4. Pasien
        // =====================================================
        $patientsData = [
            ['medical_record_no' => 'RM-001', 'name' => 'Budi Raharjo',   'birth_date' => '1975-06-15', 'gender' => 'M'],
            ['medical_record_no' => 'RM-002', 'name' => 'Siti Aminah',    'birth_date' => '1988-03-22', 'gender' => 'F'],
            ['medical_record_no' => 'RM-003', 'name' => 'Wahyu Setiawan', 'birth_date' => '1960-11-08', 'gender' => 'M'],
            ['medical_record_no' => 'RM-004', 'name' => 'Dewi Lestari',   'birth_date' => '1995-07-30', 'gender' => 'F'],
        ];
        foreach ($patientsData as $p) {
            Patient::updateOrCreate(['medical_record_no' => $p['medical_record_no']], $p);
        }

        $patients = Patient::all()->keyBy('medical_record_no');

        // =====================================================
        // 5. Kunjungan Hari Ini
        // =====================================================
        $visits = [
            ['patient' => 'RM-001', 'doctor' => $doctor1, 'status' => 'in_progress'],
            ['patient' => 'RM-002', 'doctor' => $doctor1, 'status' => 'in_progress'],
            ['patient' => 'RM-003', 'doctor' => $doctor2, 'status' => 'in_progress'],
            ['patient' => 'RM-004', 'doctor' => $doctor2, 'status' => 'pending'],
        ];

        // Sce pengukuran: [SpO2, PulseRate] → menentukan status gabungan
        $scenarios = [
            [97.5, 72],   // Normal SpO2, Normal HR     → status: normal
            [92.0, 108],  // Warning SpO2, Warning HR   → status: warning
            [86.0, 138],  // Critical SpO2, Critical HR → status: critical
            [99.0, 68],   // Normal SpO2, Normal HR     → status: normal
        ];

        foreach ($visits as $i => $v) {
            $patient = $patients[$v['patient']];
            $device  = ($i % 2 === 0) ? $device1 : $device2;

            $visit = Visit::updateOrCreate(
                ['patient_id' => $patient->id, 'visit_date' => today()->toDateTimeString()],
                ['doctor_id' => $v['doctor']->id, 'status' => $v['status']]
            );

            // Hapus pengukuran lama untuk visit ini, buat ulang
            Measurement::where('visit_id', $visit->id)->delete();

            [$baseSpo2, $basePr] = $scenarios[$i];

            // Buat beberapa sesi pengukuran historis (1 sesi = 1 baris)
            for ($j = 5; $j >= 0; $j--) {
                $spo2 = $baseSpo2 + rand(-1, 1);
                $pr   = $basePr + rand(-3, 3);

                Measurement::create([
                    'visit_id'     => $visit->id,
                    'device_id'    => $device->id,
                    'spo2'         => max(0, min(100, $spo2)),
                    'pulse_rate'   => max(0, $pr),
                    'vital_status' => Measurement::computeOverallStatus($spo2, $pr),
                    'recorded_at'  => now()->subMinutes($j * 5),
                ]);
            }
        }

        $this->command->info('✅ Demo data berhasil dibuat!');
        $this->command->info('📧 Login: admin@vitalhub.com | Password: password');
        $this->command->info('🌐 Buka: http://localhost:8000/admin');
        $this->command->newLine();
        $this->command->table(
            ['No. RM', 'Nama', 'SpO₂', 'Pulse Rate', 'Status'],
            [
                ['RM-001', 'Budi Raharjo',   '~97.5%', '~72 bpm',  '✅ Normal'],
                ['RM-002', 'Siti Aminah',    '~92.0%', '~108 bpm', '⚠️ Perhatian'],
                ['RM-003', 'Wahyu Setiawan', '~86.0%', '~138 bpm', '🚨 Kritis'],
                ['RM-004', 'Dewi Lestari',   '~99.0%', '~68 bpm',  '✅ Normal'],
            ]
        );
    }
}
