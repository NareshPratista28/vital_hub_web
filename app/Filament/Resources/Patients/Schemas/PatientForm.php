<?php

namespace App\Filament\Resources\Patients\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PatientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('medical_record_no')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                DatePicker::make('birth_date')
                    ->required(),
                Select::make('gender')
                    ->options(['M' => 'M', 'F' => 'F'])
                    ->required(),
            ]);
    }
}
