<?php

namespace App\Filament\Resources\Visits\RelationManagers;

use App\Models\Measurement;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MeasurementsRelationManager extends RelationManager
{
    protected static string $relationship = 'measurements';

    protected static ?string $title = 'Hasil Pengukuran Oximeter';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->defaultSort('recorded_at', 'desc')
            ->columns([
                TextColumn::make('recorded_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i:s')
                    ->timezone('Asia/Jakarta')
                    ->sortable(),

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
                    ->formatStateUsing(fn (?string $state) => Measurement::statusLabel($state ?? ''))
                    ->color(fn (?string $state) => Measurement::statusColor($state ?? 'normal')),
            ])
            ->filters([])
            ->headerActions([
                // Tidak ada Create — data hanya masuk dari API/Flutter
            ])
            ->recordActions([
                DeleteAction::make(),
            ])
            ->toolbarActions([
                \Filament\Actions\BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
