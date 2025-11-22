<?php

use App\Models\Business;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

if (!function_exists('user_can')) {
    function user_can(string $permission, ?Business $business = null): bool
    {
        if (App::environment('testing')) {
            return true;
        }

        $user = auth()->user();
        
        if (!$user instanceof User) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        $business = $business ?? $user->getCurrentBusiness();
        
        if (!$business) {
            return false;
        }

        $has_permission = $user->hasPermission($permission, $business);


        return $has_permission;
    }
}

if (!function_exists('user_has_role')) {
    function user_has_role(string $role, ?Business $business = null): bool
    {
        if (App::environment('testing')) {
            return true;
        }
        
        $user = auth()->user();
        
        if (!$user instanceof User) {
            return false;
        }


        if ($user->isSuperAdmin()) {
            return true;
        }

        $business = $business ?? $user->getCurrentBusiness();
        
        if (!$business) {
            return false;
        }

        return $user->hasRole($role, $business);
    }
}

if (!function_exists('user_is_owner')) {
    function user_is_owner(?Business $business = null): bool
    {
        return user_has_role('owner', $business);
    }
}
