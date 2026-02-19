<?php

namespace Hyrograsper\LunarFortis\Filament\Resources\TerminalResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;
use Hyrograsper\LunarFortis\Filament\Resources\TerminalResource;

class ListTerminals extends ListRecords
{
    protected static string $resource = TerminalResource::class;

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('heroicon-o-plus'),
        ];
    }
}
