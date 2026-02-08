<?php

namespace App\Filament\Resources\ParkirTransaksis\Pages;

use App\Filament\Resources\ParkirTransaksis\ParkirTransaksiResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListParkirTransaksis extends ListRecords
{
    protected static string $resource = ParkirTransaksiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
