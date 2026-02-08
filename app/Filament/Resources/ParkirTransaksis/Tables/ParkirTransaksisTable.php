<?php

namespace App\Filament\Resources\ParkirTransaksis\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ParkirTransaksisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('card_id')
                    ->searchable(),
                TextColumn::make('checkin_time')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('checkout_time')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('duration')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('fee')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
