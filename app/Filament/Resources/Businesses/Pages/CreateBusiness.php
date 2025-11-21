<?php

namespace App\Filament\Resources\Businesses\Pages;

use App\Filament\Resources\Businesses\BusinessResource;
use App\Models\Role;
use App\Services\MetricsService;
use Database\Seeders\BusinessSettingsSeeder;
use Database\Seeders\LaratrustSeeder;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateBusiness extends CreateRecord
{
    protected static string $resource = BusinessResource::class;


    protected function handleRecordCreation(array $data): Model
    {
        $ownersData = $data['owners'] ?? [];
        unset($data['owners']);

        $data['onboarding_completed_at'] = now();

        $business = parent::handleRecordCreation($data);

         if (!empty($ownersData)) {
           $owner = $business->users()->create($ownersData, [
                'role' => 'owner',
                'is_active' => true,
                'joined_at' => now(),
            ]);

            $ownerRole = Role::where('name', 'owner')->first();
            if ($ownerRole) {
                $owner->addRole($ownerRole, $business);
            }
        }

        $settings_seeder = new BusinessSettingsSeeder();
        $settings_seeder->createDefaultSettings($business);

        $metric_service = new MetricsService();
        $metric_service->getMetrics($business->id);

        return $business;
    }


}
