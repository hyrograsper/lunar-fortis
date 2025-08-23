<?php

namespace Hyrograsper\LunarFortis\Filament\Resources\TerminalResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Hyrograsper\LunarFortis\Filament\Resources\TerminalResource;
use Filament\Notifications\Notification;

class CreateTerminal extends CreateRecord
{
    protected static string $resource = TerminalResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Terminal created')
            ->body('The terminal has been created successfully.');
    }
}