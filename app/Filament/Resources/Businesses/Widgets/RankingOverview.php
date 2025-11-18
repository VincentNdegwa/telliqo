<?php

namespace App\Filament\Resources\Businesses\Widgets;

use App\Models\Business;
use App\Models\BusinessMetric;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class RankingOverview extends StatsOverviewWidget
{
    public ?Model $record = null;
    
    protected ?Collection $rankingMetrics = null;

    protected function getStats(): array
    {
        if (!$this->record) {
            return [];
        }

        $categoryRank = $this->getCategoryRank($this->record);
        $overallRank = $this->getOverallRank($this->record);
        $npsRank = $this->getNPSRank($this->record);
        
        $categoryTotal = $this->getCategoryRankTotal($this->record);
        $overallTotal = $this->getOverallRankTotal();

        // Calculate percentiles
        $categoryPercentile = $categoryTotal > 1 && is_numeric($categoryRank) 
            ? 100 - (($categoryRank - 1) / $categoryTotal * 100) 
            : 0;
        
        $overallPercentile = $overallTotal > 1 && is_numeric($overallRank) 
            ? 100 - (($overallRank - 1) / $overallTotal * 100) 
            : 0;

        return [
            Stat::make('Category Rank', is_numeric($categoryRank) ? "#{$categoryRank}" : $categoryRank)
                ->description("Top " . number_format($categoryPercentile, 1) . "% in " . $this->record->category->name)
                ->icon('heroicon-o-trophy')
                ->color($categoryPercentile >= 75 ? 'success' : ($categoryPercentile >= 50 ? 'warning' : 'danger')),
            
            Stat::make('Overall Rank', is_numeric($overallRank) ? "#{$overallRank}" : $overallRank)
                ->description("Top " . number_format($overallPercentile, 1) . "% of all businesses")
                ->icon('heroicon-o-star')
                ->color($overallPercentile >= 75 ? 'success' : ($overallPercentile >= 50 ? 'info' : 'warning')),
            
            Stat::make('NPS Rank', is_numeric($npsRank) ? "#{$npsRank}" : $npsRank)
                ->description("Based on Net Promoter Score")
                ->icon('heroicon-o-chart-bar')
                ->color('primary'),
        ];
    }

    private function getRankingMetrics(): Collection
    {
        if ($this->rankingMetrics !== null) {
            return $this->rankingMetrics;
        }

        $latestDate = BusinessMetric::max('metric_date');

        if (!$latestDate) {
            return $this->rankingMetrics = collect();
        }

        $this->rankingMetrics = BusinessMetric::query()
            ->where('metric_date', $latestDate)
            ->join('businesses', 'business_metrics.business_id', '=', 'businesses.id')
            ->select('business_metrics.*', 'businesses.category_id')
            ->get();
        
        return $this->rankingMetrics;
    }

    private function getOverallRankTotal(): int
    {
        return $this->getRankingMetrics()->count();
    }
    
    private function getCategoryRankTotal(Business $business): int
    {
        return $this->getRankingMetrics()
            ->where('category_id', $business->category_id)
            ->count();
    }
    
    private function getCategoryRank(Business $business): string|int
    {
        $rankingMetrics = $this->getRankingMetrics();

        if ($rankingMetrics->isEmpty()) {
            return 'N/A';
        }

        $categoryMetrics = $rankingMetrics->where('category_id', $business->category_id);
        
        $rankedList = $categoryMetrics
            ->sortByDesc(function ($metric) {
                return [$metric->avg_rating, $metric->total_feedback];
            })
            ->values();

        $rank = $rankedList->search(function ($metric) use ($business) {
            return $metric->business_id === $business->id;
        });

        if ($rank === false) {
            return 'N/A';
        }
        
        return $rank + 1;
    }

    private function getOverallRank(Business $business): string|int
    {
        $rankingMetrics = $this->getRankingMetrics();
        
        if ($rankingMetrics->isEmpty()) {
            return 'N/A';
        }

        $rankedList = $rankingMetrics
            ->sortByDesc(function ($metric) {
                return [$metric->avg_rating, $metric->total_feedback];
            })
            ->values();

        $rank = $rankedList->search(function ($metric) use ($business) {
            return $metric->business_id === $business->id;
        });

        if ($rank === false) {
            return 'N/A';
        }
        
        return $rank + 1;
    }

    private function getNPSRank(Business $business): string|int
    {
        $rankingMetrics = $this->getRankingMetrics();

        if ($rankingMetrics->isEmpty()) {
            return 'N/A';
        }
        
        $rankedList = $rankingMetrics
            ->sortByDesc('nps')
            ->values();

        $rank = $rankedList->search(function ($metric) use ($business) {
            return $metric->business_id === $business->id;
        });

        if ($rank === false) {
            return 'N/A';
        }
        
        return $rank + 1;
    }
}
