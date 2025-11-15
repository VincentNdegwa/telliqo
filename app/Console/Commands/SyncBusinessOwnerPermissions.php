<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Models\Role;
use Illuminate\Console\Command;

class SyncBusinessOwnerPermissions extends Command
{
    protected $signature = 'business:sync-owner-permissions';
    protected $description = 'Sync business owner permissions with Laratrust';

    public function handle(): int
    {
        $ownerRole = Role::where('name', 'owner')->first();
        
        if (!$ownerRole) {
            $this->error('Owner role not found. Please run LaratrustSeeder first.');
            return 1;
        }

        $businesses = Business::all();
        $syncedCount = 0;

        foreach ($businesses as $business) {
            $owners = $business->users()->wherePivot('role', 'owner')->get();
            
            foreach ($owners as $owner) {
                if (!$owner->hasRole('owner', $business)) {
                    $owner->addRole($ownerRole, $business);
                    $syncedCount++;
                    $this->info("Granted owner role to {$owner->email} for {$business->name}");
                }
            }
        }

        $this->info("Synced {$syncedCount} business owners successfully!");
        return 0;
    }
}
