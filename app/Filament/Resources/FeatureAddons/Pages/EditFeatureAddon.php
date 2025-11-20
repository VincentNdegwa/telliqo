<?php

namespace App\Filament\Resources\FeatureAddons\Pages;

use App\Filament\Resources\FeatureAddons\FeatureAddonResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFeatureAddon extends EditRecord
{
    protected static string $resource = FeatureAddonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
