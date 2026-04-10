<?php

namespace App\Filament\Widgets;

use App\Models\Device;
use App\Models\Measurement;
use App\Models\Patient;
use App\Models\Visit;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class VitalSignsStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $statsData = \Illuminate\Support\Facades\Cache::remember('vital_signs_stats', 60, function () {
            return [
                'totalPatients' => Patient::count(),
                'activeVisitsToday' => Visit::whereDate('visit_date', today())
                    ->whereIn('status', ['pending', 'in_progress'])
                    ->count(),
                'criticalLast24h' => Measurement::where('vital_status', 'critical')
                    ->where('recorded_at', '>=', now()->subHours(24))
                    ->count(),
                'warningLast24h' => Measurement::where('vital_status', 'warning')
                    ->where('recorded_at', '>=', now()->subHours(24))
                    ->count(),
                'totalMeasurementsToday' => Measurement::whereDate('recorded_at', today())->count(),
            ];
        });

        $totalPatients = $statsData['totalPatients'];
        $activeVisitsToday = $statsData['activeVisitsToday'];
        $criticalLast24h = $statsData['criticalLast24h'];
        $warningLast24h = $statsData['warningLast24h'];
        $totalMeasurementsToday = $statsData['totalMeasurementsToday'];

        return [
            Stat::make('Total Pasien', $totalPatients)
                ->description('Terdaftar di sistem')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),

            Stat::make('Kunjungan Pasien Hari Ini', $activeVisitsToday)
                ->description("{$totalMeasurementsToday} pengukuran hari ini")
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),

            Stat::make('⚠️ Alert Kritis (24 Jam)', $criticalLast24h)
                ->description("{$warningLast24h} perhatian lainnya")
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($criticalLast24h > 0 ? 'danger' : 'success'),
        ];
    }
}
