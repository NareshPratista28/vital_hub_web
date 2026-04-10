<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class RealtimeVitalSignsChart extends ChartWidget
{
    protected ?string $heading = '📈 Tren Vital Signs (Real-time)';

    protected ?string $pollingInterval = '5s';

    protected function getData(): array
    {
        $data = \App\Models\Measurement::query()
            ->select(['spo2', 'pulse_rate', 'recorded_at'])
            ->latest('recorded_at')
            ->limit(15)
            ->get()
            ->reverse();

        return [
            'datasets' => [
                [
                    'label' => 'SpO₂ (%)',
                    'data' => $data->pluck('spo2')->map(fn ($state) => (float) $state)->toArray(),
                    'borderColor' => '#3b82f6', // blue-500
                ],
                [
                    'label' => 'Pulse Rate (bpm)',
                    'data' => $data->pluck('pulse_rate')->toArray(),
                    'borderColor' => '#ef4444', // red-500
                ],
            ],
            'labels' => $data->pluck('recorded_at')->map(fn ($date) => $date->format('H:i:s'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
