<?php

namespace App\Filament\Resources\Businesses\RelationManagers;

use App\Models\Role;
use Filament\Actions\Action;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Log;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    /**
     * Define the reusable user form schema components.
     */
    private function getUserFormSchema(bool $isCreating = false): array
    {
        return [
            TextInput::make('name')
                ->required(),
            TextInput::make('email')
                ->label('Email address')
                ->email()
                ->required(),
            Select::make('role')
                ->options(
                    Role::where(function ($query) {
                        $query->where(function ($q) {
                            $q->where('name', '<>', 'owner')
                                ->orWhere('team_id', $this->ownerRecord->id);
                        });
                    })->pluck('name', 'id')
                )
                ->required(),
            TextInput::make('password')
                ->password()
                ->required($isCreating)
                ->dehydrateStateUsing(fn (string $state): string => bcrypt($state))
                ->dehydrated(fn (?string $state): bool => filled($state)),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components($this->getUserFormSchema(true));
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable()
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('createUser')
                    ->label('Create User')
                    ->action(function (array $data) {
                        $role = Role::find($data['role']);

                        Log::info("Owner record: ", [
                            "record" => $this->ownerRecord->toArray()
                        ]);

                        $user = $this->ownerRecord->users()->create([
                            'name' => $data['name'],
                            'email' => $data['email'],
                            'password' => $data['password'],
                        ]);

                        $user->addRole($role, $this->ownerRecord);
                    })
                    ->form($this->getUserFormSchema(true)),
            ])
            ->recordActions([
                Action::make('editUser')
                    ->label('Edit User')
                    ->fillForm(fn ($record) => $record->toArray())
                    ->form($this->getUserFormSchema(false))
                    ->action(function (array $data, $record) {
                        $role = Role::find($data['role']);

                        if ($role && $role->name === 'owner') {
                            $exists = \App\Models\User::whereHas('roles', fn ($q) => $q->where('name', 'owner'))->where('id', '<>', $record->id)->exists();

                            if ($exists) {
                                Notification::make()->danger()->title('Owner already exists')->body('A user with the owner role already exists.')->send();
                                return;
                            }
                        }

                        $update = [
                            'name' => $data['name'],
                            'email' => $data['email'],
                        ];

                        // Password hashing is now handled in getUserFormSchema via dehydrateStateUsing
                        if (!empty($data['password'])) {
                            $update['password'] = $data['password'];
                        }

                        $record->update($update);

                        if ($role) {
                            try {
                                $record->addRole($role, $this->ownerRecord);
                            } catch (\Throwable $e) {
                                try {
                                    $record->addRole($role);
                                } catch (\Throwable $e) {
                                    // ignore
                                }
                            }
                        }

                        Notification::make()->success()->title('User updated')->send();
                    }),
                DetachAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}