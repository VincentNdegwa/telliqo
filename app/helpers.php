<?php

use App\Models\Business;
use App\Models\User;
use Illuminate\Support\Facades\Log;

if (!function_exists('user_can')) {
    function user_can(string $permission, ?Business $business = null): bool
    {
        $user = auth()->user();
        
        if (!$user instanceof User) {
            return false;
        }

        $business = $business ?? $user->getCurrentBusiness();
        
        if (!$business) {
            return false;
        }

        $has_permission = $user->hasPermission($permission, $business);

        Log::info("User has permision: ". ($has_permission ? "true" : "false"));

        return $has_permission;
    }
}

if (!function_exists('user_has_role')) {
    function user_has_role(string $role, ?Business $business = null): bool
    {
        $user = auth()->user();
        
        if (!$user instanceof User) {
            return false;
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
