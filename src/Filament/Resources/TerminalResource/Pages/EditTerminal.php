<?php

namespace Hyrograsper\LunarFortis\Filament\Resources\TerminalResource\Pages;

use Exception;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Hyrograsper\LunarFortis\Filament\Resources\TerminalResource;
use Illuminate\Database\Eloquent\Model;

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

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            return parent::handleRecordUpdate($record, $data);
        } catch (Exception $exception) {
            Notification::make()
                ->title('Terminal update failed')
                ->body($exception->getMessage())
                ->danger()
                ->persistent()
                ->send();

            $this->halt();

            throw $exception;
        }
    }
}
