<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Visit;

class VisitController extends Controller
{
    /**
     * Menampilkan daftar antrean/kunjungan yang belum selesai
     */
    public function index()
    {
        $visits = Visit::with([
            'patient:id,medical_record_no,name,gender,birth_date',
            'doctor:id,name,specialization'
        ])
            ->whereIn('status', ['pending', 'in_progress'])
            ->orderBy('id', 'desc') // Ambil yang paling baru dibuat
            ->get()
            ->unique('patient_id')
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'Daftar antrean berhasil diambil',
            'data' => $visits
        ], 200);
    }

    /**
     * Menampilkan detail satu kunjungan beserta histori pengukuran alatnya
     */
    public function show($id)
    {
        $visit = Visit::with([
            'patient',
            'doctor',
            'deviceReadings.device' // Eager load bertingkat untuk melihat alat yang dipakai
        ])->find($id);

        if (!$visit) {
            return response()->json([
                'success' => false,
                'message' => 'Data kunjungan tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail kunjungan berhasil diambil',
            'data' => $visit
        ], 200);
    }
}
