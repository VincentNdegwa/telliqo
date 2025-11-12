<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): Response
    {
        $user = auth()->user();

        if ($user && $user->isSuperAdmin()) {
            return $request->wantsJson()
                ? new JsonResponse('', 204)
                : redirect()->intended(route('filament.admin.pages.dashboard'));
        }

        if ($user && !$user->hasCompletedOnboarding()) {
            return $request->wantsJson()
                ? new JsonResponse('', 204)
                : redirect()->route('onboarding.show');
        }

        return $request->wantsJson()
            ? new JsonResponse('', 204)
            : redirect()->intended(route('dashboard'));
    }
}
