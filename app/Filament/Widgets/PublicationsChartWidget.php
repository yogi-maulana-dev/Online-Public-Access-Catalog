<?php

namespace App\Filament\Widgets;

use App\Services\AnalyticsService;
use Filament\Widgets\ChartWidget;

class PublicationsChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Publikasi per Tahun';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $data = app(AnalyticsService::class)->publicationsPerYear(8);

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Buku',
                    'data' => $data['data'],
                    'backgroundColor' => 'rgba(30, 58, 95, 0.6)',
                    'borderColor' => '#1e3a5f',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $data['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    public static function canView(): bool
    {
        return auth()->user()?->can('view analytics') ?? false;
    }
}
