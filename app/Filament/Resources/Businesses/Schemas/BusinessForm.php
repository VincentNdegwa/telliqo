<?php

namespace App\Filament\Resources\Businesses\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class BusinessForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Business Information')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn(string $state, callable $set) => $set('slug', \Str::slug($state))),
                        TextInput::make('slug')
                            ->unique(ignoreRecord: true)
                            ->required(),
                        Textarea::make('description')
                            ->columnSpanFull(),
                        Select::make('category_id')
                            ->relationship('category', 'name')
                            ->required(),
                        TextInput::make('email')
                            ->label('Email address')
                            ->email()
                            ->required(),
                        TextInput::make('phone')
                            ->tel(),
                        TextInput::make('address'),
                        TextInput::make('city'),
                        TextInput::make('state'),
                        TextInput::make('country'),
                        TextInput::make('postal_code'),
                        TextInput::make('website')
                            ->url(),
                        TextInput::make('logo'),
                        Toggle::make('is_active')
                            ->required(),
                    ]),

                Section::make('Owner Details')
                    ->schema([
                        TextInput::make('owners.name')
                            ->label('Owner Name')
                            ->required(),
                        TextInput::make('owners.email')
                            ->label('Owner Email')
                            ->email()
                            ->required(),
                        TextInput::make('owners.password')
                            ->label('Owner Password')
                            ->password()
                            ->required()
                            ->dehydrateStateUsing(fn(string $state): string => bcrypt($state))
                            ->dehydrated(fn(?string $state): bool => filled($state)),
                    ]),

            ]);
    }
}
