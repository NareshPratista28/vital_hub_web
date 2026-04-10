<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Measurement;
use App\Models\Patient;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Get summary stats for the dashboard.
     */
    public function summary()
    {
        $stats = Cache::remember('api_dashboard_stats', 60, function () {
            return [
                'total_patients' => Patient::count(),
                'active_visits_today' => Visit::whereDate('visit_date', today())
                    ->whereIn('status', ['pending', 'in_progress'])
                    ->count(),
                'critical_24h' => Measurement::where('vital_status', 'critical')
                    ->where('recorded_at', '>=', now()->subHours(24))
                    ->count(),
                'warning_24h' => Measurement::where('vital_status', 'warning')
                    ->where('recorded_at', '>=', now()->subHours(24))
                    ->count(),
                'total_measurements_today' => Measurement::whereDate('recorded_at', today())->count(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get measurement history.
     */
    public function measurements(Request $request)
    {
        $limit = $request->query('limit', 20);

        $measurements = Measurement::with(['visit.patient', 'device'])
            ->orderBy('recorded_at', 'desc')
            ->paginate($limit);

        return response()->json([
            'success' => true,
            'message' => 'Measurement history retrieved successfully',
            'data' => $measurements->items(),
            'meta' => [
                'current_page' => $measurements->currentPage(),
                'last_page' => $measurements->lastPage(),
                'total' => $measurements->total(),
            ]
        ]);
    }
}
