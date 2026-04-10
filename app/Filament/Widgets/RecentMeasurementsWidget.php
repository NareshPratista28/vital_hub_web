<?php

namespace App\Filament\Widgets;

use App\Models\Measurement;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Columns\TextColumn;

class RecentMeasurementsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = '📋 Pengukuran Vital Signs Terbaru';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Measurement::query()
                    ->with(['visit.patient', 'device'])
                    ->orderByDesc('recorded_at')
                    ->limit(20)
            )
            ->columns([
                TextColumn::make('recorded_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i:s')
                    ->timezone('Asia/Jakarta')
                    ->sortable(),

                TextColumn::make('visit.patient.name')
                    ->label('Nama Pasien')
                    ->searchable()
                    ->weight('semibold'),

                TextColumn::make('visit.patient.medical_record_no')
                    ->label('No. RM')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('spo2')
                    ->label('SpO₂')
                    ->formatStateUsing(fn ($state) => number_format($state, 1) . ' %')
                    ->sortable(),

                TextColumn::make('pulse_rate')
                    ->label('Pulse Rate')
                    ->formatStateUsing(fn ($state) => $state . ' bpm')
                    ->sortable(),

                TextColumn::make('vital_status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Measurement::statusLabel($state))
                    ->color(fn (string $state) => Measurement::statusColor($state)),
            ])
            ->emptyStateHeading('Belum ada data pengukuran')
            ->emptyStateDescription('Data akan muncul setelah pengukuran pertama disimpan.')
            ->emptyStateIcon('heroicon-o-heart')
            ->defaultSort('recorded_at', 'desc');
    }
}
