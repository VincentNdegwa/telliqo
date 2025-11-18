<?php

namespace App\Filament\Resources\Businesses\Pages;

use App\Filament\Resources\Businesses\BusinessResource;
use App\Models\Business;
use App\Models\Feedback;
use Filament\Actions\EditAction;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\FontWeight;

class ViewBusiness extends ViewRecord
{
    protected static string $resource = BusinessResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    /**
     * @param Schema $schema
     * @return Schema
     */
    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Business Overview')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Business Name')
                                    ->weight(FontWeight::Bold),
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
                                    ->openUrlInNewTab(),
                                TextEntry::make('is_active')
                                    ->label('Status')
                                    ->badge()
                                    ->formatStateUsing(fn ($state) => $state ? 'Active' : 'Inactive')
                                    ->color(fn ($state) => $state ? 'success' : 'danger'),
                            ]),
                    ])
                    ->collapsible()
                    ->columnSpanFull(),

                // Performance Stats
                Section::make('Performance Statistics')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('total_feedback')
                                    ->label('Total Feedback')
                                    ->state(fn ($record) => $record->feedback()->count())
                                    ->badge()
                                    ->color('info')
                                    ->weight(FontWeight::Bold),
                                
                                TextEntry::make('average_rating')
                                    ->label('Average Rating')
                                    ->state(function ($record) {
                                        $avg = $record->feedback()->avg('rating');
                                        return $avg ? number_format($avg, 2) . ' / 5.00' : 'N/A';
                                    })
                                    ->badge()
                                    ->color(function ($record) {
                                        $avg = $record->feedback()->avg('rating');
                                        if (!$avg) return 'gray';
                                        if ($avg >= 4.5) return 'success';
                                        if ($avg >= 3.5) return 'warning';
                                        return 'danger';
                                    })
                                    ->weight(FontWeight::Bold),
                                
                                TextEntry::make('nps_score')
                                    ->label('NPS Score')
                                    ->state(function ($record) {
                                        return $this->calculateNPS($record);
                                    })
                                    ->badge()
                                    ->color(function ($record) {
                                        $nps = $this->calculateNPS($record, true);
                                        if ($nps === null) return 'gray';
                                        if ($nps >= 50) return 'success';
                                        if ($nps >= 0) return 'warning';
                                        return 'danger';
                                    })
                                    ->weight(FontWeight::Bold),
                                
                                TextEntry::make('total_customers')
                                    ->label('Total Customers')
                                    ->state(fn ($record) => $record->customers()->count()) 
                                    ->badge()
                                    ->color('primary')
                                    ->weight(FontWeight::Bold),
                            ]),
                    ])
                    ->columnSpanFull(),

                // Ranking Section
                Section::make('Rankings & Position')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('category_rank')
                                    ->label('Rank in Category')
                                    ->state(function ($record) {
                                        return $this->getCategoryRank($record);
                                    })
                                    ->badge()
                                    ->color('primary')
                                    ->icon('heroicon-o-trophy'),
                                
                                TextEntry::make('overall_rank')
                                    ->label('Overall Rank')
                                    ->state(function ($record) {
                                        return $this->getOverallRank($record);
                                    })
                                    ->badge()
                                    ->color('success')
                                    ->icon('heroicon-o-star'),
                                
                                TextEntry::make('nps_rank')
                                    ->label('NPS Rank')
                                    ->state(function ($record) {
                                        return $this->getNPSRank($record);
                                    })
                                    ->badge()
                                    ->color('warning')
                                    ->icon('heroicon-o-chart-bar'),
                            ]),
                        
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('category_percentile')
                                    ->label('Category Performance')
                                    ->state(function ($record) {
                                        $rank = $this->getCategoryRank($record, true);
                                        $total = Business::where('category_id', $record->category_id)->count();
                                        if ($total == 0) return 'N/A';
                                        $percentile = 100 - (($rank - 1) / $total * 100);
                                        return "Top " . number_format($percentile, 1) . "% in " . $record->category->name;
                                    })
                                    ->placeholder('N/A')
                                    ->columnSpan(1),
                                
                                TextEntry::make('overall_percentile')
                                    ->label('Overall Performance')
                                    ->state(function ($record) {
                                        $rank = $this->getOverallRank($record, true);
                                        $total = Business::count();
                                        if ($total == 0) return 'N/A';
                                        $percentile = 100 - (($rank - 1) / $total * 100);
                                        return "Top " . number_format($percentile, 1) . "% overall";
                                    })
                                    ->placeholder('N/A')
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->columnSpanFull(),

                // Sentiment Analysis
                Section::make('Feedback Analysis')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('positive_feedback')
                                    ->label('Positive Feedback')
                                    ->state(function ($record) {
                                        $count = $record->feedback()->where('sentiment', 'positive')->count();
                                        $total = $record->feedback()->count();
                                        $percentage = $total > 0 ? number_format(($count / $total) * 100, 1) : 0;
                                        return "{$count} ({$percentage}%)";
                                    })
                                    ->badge()
                                    ->color('success')
                                    ->icon('heroicon-o-hand-thumb-up'),
                                
                                TextEntry::make('neutral_feedback')
                                    ->label('Neutral Feedback')
                                    ->state(function ($record) {
                                        $count = $record->feedback()->where('sentiment', 'neutral')->count();
                                        $total = $record->feedback()->count();
                                        $percentage = $total > 0 ? number_format(($count / $total) * 100, 1) : 0;
                                        return "{$count} ({$percentage}%)";
                                    })
                                    ->badge()
                                    ->color('warning')
                                    ->icon('heroicon-o-minus-circle'),
                                
                                TextEntry::make('negative_feedback')
                                    ->label('Negative Feedback')
                                    ->state(function ($record) {
                                        $count = $record->feedback()->where('sentiment', 'negative')->count();
                                        $total = $record->feedback()->count();
                                        $percentage = $total > 0 ? number_format(($count / $total) * 100, 1) : 0;
                                        return "{$count} ({$percentage}%)";
                                    })
                                    ->badge()
                                    ->color('danger')
                                    ->icon('heroicon-o-hand-thumb-down'),
                            ]),
                    ])
                    ->columnSpanFull(),

                // Rating Distribution
                Section::make('Rating Distribution')
                    ->schema([
                        Grid::make(5)
                            ->schema([
                                TextEntry::make('five_star')
                                    ->label('5 Stars')
                                    ->state(function ($record) {
                                        $count = $record->feedback()->where('rating', 5)->count();
                                        $total = $record->feedback()->count();
                                        $percentage = $total > 0 ? number_format(($count / $total) * 100, 1) : 0;
                                        return "{$count} ({$percentage}%)";
                                    })
                                    ->badge()
                                    ->color('success'),
                                
                                TextEntry::make('four_star')
                                    ->label('4 Stars')
                                    ->state(function ($record) {
                                        $count = $record->feedback()->where('rating', 4)->count();
                                        $total = $record->feedback()->count();
                                        $percentage = $total > 0 ? number_format(($count / $total) * 100, 1) : 0;
                                        return "{$count} ({$percentage}%)";
                                    })
                                    ->badge()
                                    ->color('info'),
                                
                                TextEntry::make('three_star')
                                    ->label('3 Stars')
                                    ->state(function ($record) {
                                        $count = $record->feedback()->where('rating', 3)->count();
                                        $total = $record->feedback()->count();
                                        $percentage = $total > 0 ? number_format(($count / $total) * 100, 1) : 0;
                                        return "{$count} ({$percentage}%)";
                                    })
                                    ->badge()
                                    ->color('warning'),
                                
                                TextEntry::make('two_star')
                                    ->label('2 Stars')
                                    ->state(function ($record) {
                                        $count = $record->feedback()->where('rating', 2)->count();
                                        $total = $record->feedback()->count();
                                        $percentage = $total > 0 ? number_format(($count / $total) * 100, 1) : 0;
                                        return "{$count} ({$percentage}%)";
                                    })
                                    ->badge()
                                    ->color('danger'),
                                
                                TextEntry::make('one_star')
                                    ->label('1 Star')
                                    ->state(function ($record) {
                                        $count = $record->feedback()->where('rating', 1)->count();
                                        $total = $record->feedback()->count();
                                        $percentage = $total > 0 ? number_format(($count / $total) * 100, 1) : 0;
                                        return "{$count} ({$percentage}%)";
                                    })
                                    ->badge()
                                    ->color('danger'),
                            ]),
                    ])
                    ->columnSpanFull(),

                // Moderation Stats
                Section::make('Moderation Status')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('approved')
                                    ->label('Approved')
                                    ->state(fn ($record) => $record->feedback()->where('moderation_status', 'approved')->count())
                                    ->badge()
                                    ->color('success')
                                    ->icon('heroicon-o-check-circle'),
                                
                                TextEntry::make('pending')
                                    ->label('Pending')
                                    ->state(fn ($record) => $record->feedback()->where('moderation_status', 'soft_flagged')->count())
                                    ->badge()
                                    ->color('warning')
                                    ->icon('heroicon-o-clock'),
                                
                                TextEntry::make('flagged')
                                    ->label('Flagged')
                                    ->state(fn ($record) => $record->feedback()->where('moderation_status', 'hard_flagged')->count())
                                    ->badge()
                                    ->color('danger')
                                    ->icon('heroicon-o-flag'),
                            ]),
                    ])
                    ->columnSpanFull(),

                // Recent Trends
                Section::make('Recent Trends (Last 30 Days)')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('new_feedback_30d')
                                    ->label('New Feedback')
                                    ->state(function ($record) {
                                        return $record->feedback()
                                            ->where('created_at', '>=', now()->subDays(30))
                                            ->count();
                                    })
                                    ->badge()
                                    ->color('primary'),
                                
                                TextEntry::make('avg_rating_30d')
                                    ->label('Avg Rating')
                                    ->state(function ($record) {
                                        $avg = $record->feedback()
                                            ->where('created_at', '>=', now()->subDays(30))
                                            ->avg('rating');
                                        return $avg ? number_format($avg, 2) : 'N/A';
                                    })
                                    ->badge()
                                    ->color('info'),
                                
                                TextEntry::make('new_customers_30d')
                                    ->label('New Customers')
                                    ->state(function ($record) {
                                        return $record->customers()
                                            ->where('created_at', '>=', now()->subDays(30))
                                            ->count();
                                    })
                                    ->badge()
                                    ->color('success'),
                                
                                TextEntry::make('review_requests_30d')
                                    ->label('Review Requests')
                                    ->state(function ($record) {
                                        return $record->reviewRequests() 
                                            ->where('created_at', '>=', now()->subDays(30))
                                            ->count();
                                    })
                                    ->badge()
                                    ->color('warning'),
                            ]),
                    ])
                    ->columnSpanFull(),

                // Address Information
                Section::make('Location Details')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('address')
                                    ->icon('heroicon-o-map-pin')
                                    ->placeholder('N/A'),
                                TextEntry::make('city')
                                    ->placeholder('N/A'),
                                TextEntry::make('state')
                                    ->placeholder('N/A'),
                                TextEntry::make('country')
                                    ->placeholder('N/A'),
                                TextEntry::make('postal_code')
                                    ->placeholder('N/A'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->columnSpanFull(),
            ]);
    }


    private function calculateNPS(Business $business, bool $numeric = false): string|int|null
    {
        $total = $business->feedback()->count();
        
        if ($total == 0) {
            return $numeric ? null : 'N/A';
        }

        $promoters = $business->feedback()->whereIn('rating', [5, 4])->count();
        $detractors = $business->feedback()->whereIn('rating', [1, 2, 3])->count();
        
        $nps = (($promoters - $detractors) / $total) * 100;
        
        return $numeric ? round($nps) : round($nps) . '%';
    }


    private function getCategoryRank(Business $business, bool $numeric = false): string|int
    {
        $categoryBusinesses = Business::where('category_id', $business->category_id)
            ->withAvg('feedback', 'rating')
            ->withCount('feedback')
            ->get()
            ->sortByDesc(function ($b) {
                return $b->feedback_avg_rating ?? 0;
            })
            ->values();

        $rank = $categoryBusinesses->search(function ($b) use ($business) {
            return $b->id === $business->id;
        }) + 1;

        $total = $categoryBusinesses->count();
        
        return $numeric ? $rank : "#{$rank} of {$total}";
    }


    private function getOverallRank(Business $business, bool $numeric = false): string|int
    {
        $allBusinesses = Business::withAvg('feedback', 'rating')
            ->withCount('feedback')
            ->get()
            ->sortByDesc(function ($b) {
                return $b->feedback_avg_rating ?? 0;
            })
            ->values();

        $rank = $allBusinesses->search(function ($b) use ($business) {
            return $b->id === $business->id;
        }) + 1;

        $total = $allBusinesses->count();
        
        return $numeric ? $rank : "#{$rank} of {$total}";
    }


    private function getNPSRank(Business $business, bool $numeric = false): string|int
    {
        $allBusinesses = Business::withCount(['feedback as promoters' => function ($query) {
            $query->whereIn('rating', [5, 4]);
        }])
        ->withCount(['feedback as detractors' => function ($query) {
            $query->whereIn('rating', [1, 2, 3]);
        }])
        ->withCount('feedback')
        ->get()
        ->map(function ($b) {
            $total = $b->feedback_count;
            if ($total == 0) {
                $b->nps_score = -100;
            } else {
                $b->nps_score = (($b->promoters - $b->detractors) / $total) * 100;
            }
            return $b;
        })
        ->sortByDesc('nps_score')
        ->values();

        $rank = $allBusinesses->search(function ($b) use ($business) {
            return $b->id === $business->id;
        }) + 1;

        $total = $allBusinesses->count();
        
        return $numeric ? $rank : "#{$rank} of {$total}";
    }
}