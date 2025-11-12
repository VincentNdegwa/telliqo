<?php

namespace App\Filament\Pages;

use App\Models\GlobalSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;

class GlobalSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $view = 'filament.pages.global-settings';

    protected static ?string $navigationGroup = 'System Settings';

    protected static ?int $navigationSort = 10;

    public ?array $data = [];

    public function mount(): void
    {
        $aiEnabled = GlobalSetting::getValue('ai_enabled', false);
        $aiProvider = GlobalSetting::getValue('ai_provider', 'openai');
        $autoApproveEnabled = GlobalSetting::getValue('auto_approve_enabled', false);
        $maintenanceMode = GlobalSetting::getValue('maintenance_mode', false);

        $this->form->fill([
            'ai_enabled' => $aiEnabled,
            'ai_provider' => $aiProvider,
            'auto_approve_enabled' => $autoApproveEnabled,
            'maintenance_mode' => $maintenanceMode,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('AI Configuration')
                    ->schema([
                        Forms\Components\Toggle::make('ai_enabled')
                            ->label('Enable AI Features')
                            ->helperText('Enable AI-powered sentiment analysis and response generation'),
                    ]),

                Forms\Components\Section::make('System Settings')
                    ->schema([
                        Forms\Components\Toggle::make('maintenance_mode')
                            ->label('Maintenance Mode')
                            ->helperText('Put the application in maintenance mode'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        GlobalSetting::setValue('ai_enabled', $data['ai_enabled'] ?? false, 'Enable AI features');
        GlobalSetting::setValue('maintenance_mode', $data['maintenance_mode'] ?? false, 'Maintenance mode status');

        Notification::make()
            ->title('Settings saved successfully')
            ->success()
            ->send();
    }
}
