<?php

namespace App\Filament\Widgets;

use App\Services\AnalyticsService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $t = app(AnalyticsService::class)->totals();

        return [
            Stat::make('Buku Terbit', number_format($t['books']))
                ->description($t['all_books'].' total termasuk draf')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('primary'),
            Stat::make('Penulis', number_format($t['authors']))
                ->description('Kontributor akademik')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),
            Stat::make('Total Unduhan', number_format($t['downloads']))
                ->description(number_format($t['views']).' kali dilihat')
                ->descriptionIcon('heroicon-m-arrow-down-tray')
                ->color('success'),
            Stat::make('Naskah Menunggu', number_format($t['pending_submissions']))
                ->description('Perlu ditinjau editor')
                ->descriptionIcon('heroicon-m-inbox-arrow-down')
                ->color($t['pending_submissions'] > 0 ? 'warning' : 'gray'),
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()?->can('view analytics') ?? false;
    }
}
