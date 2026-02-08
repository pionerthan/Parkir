<?php

namespace App\Filament\Resources\ParkirTransaksis\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ParkirTransaksiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('card_id')
                    ->required(),
                DateTimePicker::make('checkin_time'),
                DateTimePicker::make('checkout_time'),
                TextInput::make('duration')
                    ->numeric()
                    ->default(null),
                TextInput::make('fee')
                    ->numeric()
                    ->default(null),
                Select::make('status')
                    ->options(['IN' => 'I n', 'OUT' => 'O u t', 'DONE' => 'D o n e'])
                    ->required(),
            ]);
    }
}
