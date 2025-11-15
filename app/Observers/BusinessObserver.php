<?php

namespace App\Observers;

use App\Models\Business;
use App\Models\Role;

class BusinessObserver
{
    public function created(Business $business): void
    {
        $ownerRole = Role::where('name', 'owner')->first();
        
        if (!$ownerRole) {
            return;
        }

        $owners = $business->users()->wherePivot('role', 'owner')->get();
        
        foreach ($owners as $owner) {
            if (!$owner->hasRole('owner', $business)) {
                $owner->addRole($ownerRole, $business);
            }
        }
    }
}
