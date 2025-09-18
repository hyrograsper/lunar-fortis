<?php

namespace Hyrograsper\LunarFortis\Filament\Resources\TerminalResource\Pages;

use Exception;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Hyrograsper\LunarFortis\Filament\Resources\TerminalResource;
use Illuminate\Database\Eloquent\Model;

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

    protected function handleRecordCreation(array $data): Model
    {
        try {
            return parent::handleRecordCreation($data);
        } catch (Exception $exception) {
            // Handle Fortis sync failures during creation
            Notification::make()
                ->title('Terminal creation failed')
                ->body($exception->getMessage())
                ->danger()
                ->persistent()
                ->send();

            $this->halt();
<<<<<<< HEAD

            // This line should never be reached due to halt() above, but PHPStan requires it
            throw $exception;
=======
>>>>>>> origin/main
        }
    }
}
