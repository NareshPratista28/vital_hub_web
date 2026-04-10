<?php

namespace App\Filament\Resources\Visits\RelationManagers;

use App\Models\DeviceReading;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DeviceReadingsRelationManager extends RelationManager
{
    protected static string $relationship = 'deviceReadings';

    protected static ?string $title = 'Data Vital Signs';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('device_id')
                    ->label('Alat / Device')
                    ->relationship('device', 'name')
                    ->required(),

                Select::make('reading_type')
                    ->label('Jenis Pengukuran')
                    ->options([
                        'spo2'       => 'SpO₂ (Saturasi Oksigen)',
                        'pulse_rate' => 'Pulse Rate (Detak Jantung)',
                    ])
                    ->required(),

                TextInput::make('reading_value')
                    ->label('Nilai')
                    ->required()
                    ->numeric(),

                TextInput::make('unit')
                    ->label('Satuan')
                    ->required()
                    ->placeholder('% atau bpm'),

                DateTimePicker::make('recorded_at')
                    ->label('Waktu Pengukuran')
                    ->required()
                    ->default(now()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reading_type')
            ->defaultSort('recorded_at', 'desc')
            ->columns([
                TextColumn::make('recorded_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i')
                    ->timezone('Asia/Jakarta')
                    ->sortable(),

                TextColumn::make('device.name')
                    ->label('Alat')
                    ->default('—')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('reading_type')
                    ->label('Jenis')
                    ->formatStateUsing(fn (string $state) => match (strtolower($state)) {
                        'spo2'       => 'SpO₂',
                        'pulse_rate' => 'Pulse Rate',
                        default      => strtoupper($state),
                    })
                    ->badge()
                    ->color('info'),

                TextColumn::make('reading_value')
                    ->label('Nilai')
                    ->formatStateUsing(fn ($state, $record) => $state . ' ' . $record->unit),

                TextColumn::make('vital_status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'critical' => '🚨 Kritis',
                        'warning'  => '⚠️ Perhatian',
                        'normal'   => '✅ Normal',
                        default    => 'Belum Diklasifikasi',
                    })
                    ->color(fn (?string $state) => match ($state) {
                        'critical' => 'danger',
                        'warning'  => 'warning',
                        'normal'   => 'success',
                        default    => 'gray',
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()->label('Tambah Pengukuran'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                \Filament\Actions\BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
