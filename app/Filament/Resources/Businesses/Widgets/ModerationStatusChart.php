<?php

namespace App\Filament\Resources\Businesses\Widgets;

use App\Models\Enums\ModerationStatus;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Model;

class ModerationStatusChart extends ChartWidget
{
    
    public ?Model $record = null;
    
    protected int | string | array $columnSpan = 1;


    protected function getData(): array
    {
        if (!$this->record) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $approved = $this->record->feedback()->where('moderation_status', ModerationStatus::PUBLISHED)->count();
        $soft_flagged = $this->record->feedback()->where('moderation_status', ModerationStatus::SOFT_FLAGGED)->count();
        $flagged = $this->record->feedback()->where('moderation_status', ModerationStatus::FLAGGED)->count();

        return [
            'datasets' => [
                [
                    'data' => [$approved, $soft_flagged, $flagged],
                    'backgroundColor' => [
                        '#22c55e', // green for approved
                        '#eab308', // yellow for soft flagged
                        '#ef4444', // red for flagged
                    ],
                ],
            ],
            'labels' => ['Approved', 'Soft Flagged', 'Flagged'],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
