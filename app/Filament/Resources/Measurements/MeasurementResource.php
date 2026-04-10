<?php

namespace App\Filament\Resources\Measurements;

use App\Filament\Resources\Measurements\Pages\ListMeasurements;
use App\Models\Measurement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MeasurementResource extends Resource
{
    protected static ?string $model = Measurement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHeart;

    protected static ?string $navigationLabel = 'Hasil Pengukuran';

    protected static ?string $modelLabel = 'Pengukuran';

    protected static ?string $pluralModelLabel = 'Semua Pengukuran';

    protected static ?int $navigationSort = 2;

    // Resource ini hanya tampil, tidak ada form Create (data masuk via API)
    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                Measurement::query()
                    ->with(['visit.patient', 'device'])
                    ->latest('recorded_at')
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
                    ->sortable(),

                TextColumn::make('visit.patient.medical_record_no')
                    ->label('No. RM')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('spo2')
                    ->label('SpO₂ (%)')
                    ->formatStateUsing(fn ($state) => number_format($state, 1) . ' %')
                    ->sortable(),

                TextColumn::make('pulse_rate')
                    ->label('Pulse Rate (bpm)')
                    ->formatStateUsing(fn ($state) => $state . ' bpm')
                    ->sortable(),

                TextColumn::make('vital_status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Measurement::statusLabel($state))
                    ->color(fn (string $state) => Measurement::statusColor($state))
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('vital_status')
                    ->label('Filter Status')
                    ->options([
                        'normal'   => '✅ Normal',
                        'warning'  => '⚠️ Perhatian',
                        'critical' => '🚨 Kritis',
                    ]),
            ])
            ->defaultSort('recorded_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMeasurements::route('/'),
        ];
    }

    // Tidak ada Create/Edit dari web — data hanya masuk via API
    public static function canCreate(): bool
    {
        return false;
    }
}
