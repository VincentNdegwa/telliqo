<?php

namespace App\Filament\Resources\Plans\Pages;

use App\Filament\Resources\Plans\PlanResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePlan extends CreateRecord
{
    protected static string $resource = PlanResource::class;

    protected array $featuresData = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->featuresData = $data['features'] ?? [];

        unset($data['features']);

        return $data;
    }

    protected function afterCreate(): void
    {
        if (! empty($this->featuresData)) {
            $this->record->syncFeatures($this->featuresData);
        }
    }
}
