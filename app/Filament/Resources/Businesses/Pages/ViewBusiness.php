<?php

namespace App\Filament\Resources\Businesses\Pages;

use App\Filament\Resources\Businesses\BusinessResource;
use App\Filament\Resources\Businesses\Widgets\CustomerEngagementStats;
use App\Filament\Resources\Businesses\Widgets\CustomerOverview;
use App\Filament\Resources\Businesses\Widgets\FeedbackTrendChart;
use App\Filament\Resources\Businesses\Widgets\ModerationStatusChart;
use App\Filament\Resources\Businesses\Widgets\MonthlyPerformanceChart;
use App\Filament\Resources\Businesses\Widgets\NPSBreakdownChart;
use App\Filament\Resources\Businesses\Widgets\RankingOverview;
use App\Filament\Resources\Businesses\Widgets\RatingDistributionChart;
use App\Filament\Resources\Businesses\Widgets\SentimentDonutChart;
use App\Services\MetricsService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section as ComponentsSection;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Collection;

class ViewBusiness extends ViewRecord
{
    protected static string $resource = BusinessResource::class;

    protected MetricsService $metricsService;
    protected array $summaryMetrics = [];
    protected ?Collection $rankingMetrics = null;

    public function __construct()
    {
        $this->metricsService = new MetricsService();
    }

    protected static ?string $title = 'Business Details';

    public function getTitle(): string
    {
        return $this->record->name ?? static::$title;
    }

    public function mount(int | string $record): void
    {
        parent::mount($record);
        $this->loadBusinessMetrics();
    }

    protected function loadBusinessMetrics(): void
    {
        $this->summaryMetrics = $this->metricsService->getSummary($this->record->id, 30);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            Action::make("Public View")
                ->url(fn () => route('business.public', ['business' => $this->record->slug]))
                ->openUrlInNewTab()
                ->icon('heroicon-m-arrow-top-right-on-square')
                ->color('info') 
                ->button(),
        ];
    }

    public function getHeaderWidgets(): array
    {
        return [
            CustomerOverview::class,
            RankingOverview::class,
        ];
    }

    public function getFooterWidgets(): array
    {
        return [
            CustomerEngagementStats::class,
            NPSBreakdownChart::class,
            ModerationStatusChart::class,
            FeedbackTrendChart::class,
            RatingDistributionChart::class,
            SentimentDonutChart::class,
            MonthlyPerformanceChart::class,
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                ComponentsSection::make('Business Information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Business Name')
                                    ->weight(FontWeight::Bold)
                                    ->columnSpan(2),
                                TextEntry::make('is_active')
                                    ->label('Status')
                                    ->badge()
                                    ->formatStateUsing(fn ($state) => $state ? 'Active' : 'Inactive')
                                    ->color(fn ($state) => $state ? 'success' : 'danger'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('category.name')
                                    ->label('Category')
                                    ->badge()
                                    ->color('info'),
                                TextEntry::make('email')
                                    ->icon('heroicon-o-envelope')
                                    ->copyable(),
                                TextEntry::make('phone')
                                    ->icon('heroicon-o-phone')
                                    ->copyable(),
                                TextEntry::make('website')
                                    ->icon('heroicon-o-globe-alt')
                                    ->url(fn ($state) => $state)
                                    ->openUrlInNewTab()
                                    ->placeholder('N/A'),
                                TextEntry::make('address')
                                    ->icon('heroicon-o-map-pin')
                                    ->placeholder('N/A'),
                                TextEntry::make('city')
                                    ->placeholder('N/A'),
                            ]),
                    ])
                    ->collapsible()
                    ->columnSpanFull(),

                Section::make('Business Settings')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('auto_approve_feedback')
                                    ->label('Auto Approve')
                                    ->badge()
                                    ->formatStateUsing(fn ($state) => $state ? 'Enabled' : 'Disabled')
                                    ->color(fn ($state) => $state ? 'success' : 'gray'),
                                TextEntry::make('require_customer_name')
                                    ->label('Require Customer Name')
                                    ->badge()
                                    ->formatStateUsing(fn ($state) => $state ? 'Required' : 'Optional')
                                    ->color(fn ($state) => $state ? 'warning' : 'gray'),
                                TextEntry::make('feedback_email_notifications')
                                    ->label('Email Notifications')
                                    ->badge()
                                    ->formatStateUsing(fn ($state) => $state ? 'Enabled' : 'Disabled')
                                    ->color(fn ($state) => $state ? 'success' : 'gray'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->columnSpanFull(),

                Section::make('Branding')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('brand_color_primary')
                                    ->label('Primary Color')
                                    ->placeholder('N/A'),
                                TextEntry::make('brand_color_secondary')
                                    ->label('Secondary Color')
                                    ->placeholder('N/A'),
                                TextEntry::make('custom_thank_you_message')
                                    ->label('Thank You Message')
                                    ->placeholder('N/A')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->columnSpanFull(),
            ]);
    }
}