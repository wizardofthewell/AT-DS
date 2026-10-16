<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Inquiries\InquiryResource;
use App\Filament\Resources\Services\ServiceResource;
use App\Filament\Resources\Testimonials\TestimonialResource;
use App\Models\Inquiry;
use App\Models\Service;
use App\Models\Testimonial;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SiteOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $new = Inquiry::where('status', 'new')->count();

        return [
            Stat::make('New inquiries', $new)
                ->description($new ? 'Waiting for a reply' : 'All caught up')
                ->descriptionIcon($new ? 'heroicon-m-bell-alert' : 'heroicon-m-check-circle')
                ->color($new ? 'danger' : 'success')
                ->url(InquiryResource::getUrl('index')),
            Stat::make('Inquiries this month', Inquiry::where('created_at', '>=', now()->startOfMonth())->count())
                ->description(Inquiry::where('status', 'booked')->count().' booked overall')
                ->descriptionIcon('heroicon-m-calendar-days'),
            Stat::make('Services', Service::published()->count())
                ->description('Published on the site')
                ->url(ServiceResource::getUrl('index')),
            Stat::make('Testimonials', Testimonial::published()->count())
                ->description('Kind words shared')
                ->url(TestimonialResource::getUrl('index')),
        ];
    }
}
