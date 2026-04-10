<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Device;

class DeviceController extends Controller
{
    public function index()
    {
        // Hanya ambil alat yang statusnya aktif
        $devices = Device::where('is_active', true)
            ->get(['id', 'name', 'device_type', 'mac_address']);

        return response()->json([
            'success' => true,
            'message' => 'Daftar perangkat berhasil diambil',
            'data' => $devices
        ], 200);
    }
}
