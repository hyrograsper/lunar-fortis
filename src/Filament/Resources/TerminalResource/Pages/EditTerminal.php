<?php

namespace Hyrograsper\LunarFortis\Filament\Resources\TerminalResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Hyrograsper\LunarFortis\Filament\Resources\TerminalResource;

class EditTerminal extends EditRecord
{
    protected static string $resource = TerminalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Terminal updated')
            ->body('The terminal has been updated successfully.');
    }
}