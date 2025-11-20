<?php

namespace App\Filament\Resources\FeatureAddons\Pages;

use App\Filament\Resources\FeatureAddons\FeatureAddonResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFeatureAddons extends ListRecords
{
    protected static string $resource = FeatureAddonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
