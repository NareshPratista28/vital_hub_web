<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Measurement;

class MeasurementController extends Controller
{
    /**
     * Simpan hasil pengukuran oximeter.
     *
     * Payload dari Flutter:
     * {
     *   "visit_id": 1,
     *   "mac_address": "AA:BB:CC:DD:EE:01",
     *   "spo2": 92.5,
     *   "pulse_rate": 105
     * }
     */
    public function store(Request $request)
    {
        // 1. Validasi payload
        $validated = $request->validate([
            'visit_id'    => 'required|exists:visits,id',
            'mac_address' => 'nullable|string',
            'spo2'        => 'required|numeric|min:0|max:100',
            'pulse_rate'  => 'required|integer|min:0|max:300',
        ]);

        // 2. Cek alat terdaftar & aktif (fallback ke alat pertama untuk prototipe)
        $macAddress = $validated['mac_address'] ?? 'SIMULATOR';
        $device = Device::where('mac_address', $macAddress)->where('is_active', true)->first()
            ?? Device::where('is_active', true)->first();

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada alat medis yang aktif di sistem.',
            ], 403);
        }

        // 3. Hitung status vital gabungan
        $vitalStatus = Measurement::computeOverallStatus(
            (float) $validated['spo2'],
            (int)   $validated['pulse_rate']
        );

        // 4. Simpan record pengukuran
        $measurement = Measurement::create([
            'visit_id'     => $validated['visit_id'],
            'device_id'    => $device->id,
            'spo2'         => $validated['spo2'],
            'pulse_rate'   => $validated['pulse_rate'],
            'vital_status' => $vitalStatus,
            'recorded_at'  => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengukuran berhasil disimpan.',
            'data'    => [
                'id'           => $measurement->id,
                'spo2'         => $measurement->spo2,
                'pulse_rate'   => $measurement->pulse_rate,
                'vital_status' => $measurement->vital_status,
                'status_label' => Measurement::statusLabel($vitalStatus),
                'recorded_at'  => $measurement->recorded_at->toIso8601String(),
            ],
        ], 201);
    }
}
