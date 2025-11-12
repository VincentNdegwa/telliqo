<?php

namespace App\Filament\Resources\GlobalSettingsResource\Pages;

use App\Filament\Resources\GlobalSettingsResource;
use Filament\Resources\Pages\Page;

class GlobalSettings extends Page
{
    protected static string $resource = GlobalSettingsResource::class;

    protected static string $view = 'filament.resources.global-settings-resource.pages.global-settings';
}
