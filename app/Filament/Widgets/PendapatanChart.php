<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\ParkirTransaksi;

class PendapatanChart extends ChartWidget
{
    public function getHeading(): string
    {
        return 'Grafik Pendapatan Parkir';
    }

    protected function getData(): array
    {
        $data = ParkirTransaksi::selectRaw('DATE(created_at) as tanggal, SUM(fee) as total')
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Pendapatan',
                    'data' => $data->pluck('total'),
                ],
            ],
            'labels' => $data->pluck('tanggal'),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
