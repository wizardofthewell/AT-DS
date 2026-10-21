<?php

namespace App\Filament\Resources\Users\Tables;

use App\Filament\Resources\Users\UserResource;
use App\Models\AuditLog;
use App\Models\User;
use App\Support\Impersonation;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->weight('medium'),
                TextColumn::make('email')->searchable(),
                IconColumn::make('is_super_admin')
                    ->label('Super admin')
                    ->boolean()
                    ->falseIcon(null)
                    ->visible(fn (): bool => (bool) auth()->user()?->isSuperAdmin()),
                TextColumn::make('last_login_at')
                    ->label('Last sign-in')
                    ->state(fn (User $record) => AuditLog::where('event', 'login')->where('user_id', $record->getKey())->latest('created_at')->value('created_at'))
                    ->since()
                    ->placeholder('Never')
                    ->visible(fn (): bool => (bool) auth()->user()?->isSuperAdmin()),
                TextColumn::make('created_at')->label('Added')->date(),
            ])
            ->recordActions([
                Action::make('impersonate')
                    ->label('Log in as')
                    ->icon('heroicon-o-arrow-right-end-on-rectangle')
                    ->color('warning')
                    ->visible(fn (User $record): bool => Impersonation::canImpersonate(auth()->user(), $record))
                    ->requiresConfirmation()
                    ->modalDescription(fn (User $record): string => "See the admin exactly as {$record->name} does. A banner at the top lets you return to your own account.")
                    ->action(function (User $record) {
                        abort_unless(Impersonation::canImpersonate(auth()->user(), $record), 403);

                        Impersonation::start($record);

                        return redirect(filament()->getUrl());
                    }),
                EditAction::make()
                    ->visible(fn (User $record): bool => UserResource::canEdit($record)),
                DeleteAction::make()
                    ->visible(fn (User $record): bool => UserResource::canDelete($record) && ! $record->is(auth()->user())),
            ]);
    }
}
