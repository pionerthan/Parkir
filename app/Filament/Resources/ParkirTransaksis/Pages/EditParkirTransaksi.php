<?php

namespace App\Filament\Resources\ParkirTransaksis\Pages;

use App\Filament\Resources\ParkirTransaksis\ParkirTransaksiResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditParkirTransaksi extends EditRecord
{
    protected static string $resource = ParkirTransaksiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
