<?php

namespace App\Filament\Resources\Plans\Pages;

use App\Filament\Resources\Plans\PlanResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPlan extends EditRecord
{
    protected static string $resource = PlanResource::class;

    protected array $featuresData = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['features'] = $this->record->getFeaturesFormState();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->featuresData = $data['features'] ?? [];

        unset($data['features']);

        return $data;
    }

    protected function afterSave(): void
    {
        if (! empty($this->featuresData)) {
            $this->record->syncFeatures($this->featuresData);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
