<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Models\Role;
use Database\Seeders\LaratrustSeeder;
use Illuminate\Console\Command;

class SyncBusinessOwnerPermissions extends Command
{
    protected $signature = 'business:sync-owner-permissions';
    protected $description = 'Sync business owner permissions with Laratrust';

    public function handle(): int
    {
        $businesses = Business::all();
        $totalSynced = 0;

        foreach ($businesses as $business) {
            $syncedCount = LaratrustSeeder::syncOwnerPermissions($business);
            $totalSynced += $syncedCount;
            
            if ($syncedCount > 0) {
                $this->info("Synced {$syncedCount} owner(s) for {$business->name}");
            }
        }

        $this->info("Successfully synced {$totalSynced} business owner(s) across all businesses!");
        return 0;
    }
}
