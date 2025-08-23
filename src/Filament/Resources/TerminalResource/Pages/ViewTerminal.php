<?php

namespace Hyrograsper\LunarFortis\Filament\Resources\TerminalResource\Pages;

use Filament\Actions;
use Filament\Forms;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Hyrograsper\LunarFortis\Filament\Resources\TerminalResource;
use Hyrograsper\LunarFortis\Models\Terminal;

class ViewTerminal extends ViewRecord
{
    protected static string $resource = TerminalResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Terminal Overview')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('title')
                                    ->label('Terminal Name'),

                                Infolists\Components\TextEntry::make('serial_number')
                                    ->label('Serial Number'),

                                Infolists\Components\TextEntry::make('fortis_id')
                                    ->label('Fortis ID')
                                    ->placeholder('Not synced'),
                            ]),

                        Infolists\Components\Grid::make(4)
                            ->schema([
                                Infolists\Components\IconEntry::make('active')
                                    ->boolean()
                                    ->label('Active'),

                                Infolists\Components\IconEntry::make('is_provisioned')
                                    ->boolean()
                                    ->label('Provisioned'),

                                Infolists\Components\TextEntry::make('isReadyForPayments')
                                    ->getStateUsing(fn (Terminal $record) => $record->isReadyForPayments() ? 'Ready' : 'Not Ready')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'Ready' => 'success',
                                        'Not Ready' => 'warning',
                                    })
                                    ->label('Payment Status'),

                                Infolists\Components\TextEntry::make('synced_at')
                                    ->dateTime()
                                    ->since()
                                    ->label('Last Sync')
                                    ->placeholder('Never synced'),
                            ]),
                    ]),

                Infolists\Components\Section::make('Capabilities')
                    ->schema([
                        Infolists\Components\TextEntry::make('capabilities')
                            ->getStateUsing(fn (Terminal $record) => $record->getCapabilitiesString())
                            ->badge()
                            ->separator(', ')
                            ->label('Supported Features'),
                    ]),

                Infolists\Components\Section::make('Network Configuration')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('local_ip_address')
                                    ->label('IP Address')
                                    ->placeholder('Not configured'),

                                Infolists\Components\TextEntry::make('mac_address')
                                    ->label('MAC Address')
                                    ->placeholder('Not configured'),

                                Infolists\Components\TextEntry::make('port')
                                    ->label('Port')
                                    ->placeholder('Not configured'),
                            ]),

                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('communication_type')
                                    ->label('Communication Type')
                                    ->badge(),

                                Infolists\Components\TextEntry::make('terminal_number')
                                    ->label('Terminal Number')
                                    ->placeholder('Not assigned'),
                            ]),
                    ])
                    ->collapsible(),

                Infolists\Components\Section::make('Configuration Details')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('terminal_application_id')
                                    ->label('Application ID')
                                    ->placeholder('Not configured'),

                                Infolists\Components\TextEntry::make('terminal_manufacturer_code')
                                    ->label('Manufacturer Code')
                                    ->placeholder('Not configured'),
                            ]),

                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('default_product_transaction_id')
                                    ->label('Default Product Transaction ID')
                                    ->placeholder('Not configured'),

                                Infolists\Components\TextEntry::make('terminal_cvm_id')
                                    ->label('CVM ID')
                                    ->placeholder('Not configured'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Infolists\Components\Section::make('Receipt Configuration')
                    ->schema([
                        Infolists\Components\Fieldset::make('Header Lines')
                            ->schema([
                                Infolists\Components\TextEntry::make('header_lines')
                                    ->getStateUsing(fn (Terminal $record) => implode("\n", $record->getHeaderLinesAttribute()))
                                    ->placeholder('No header lines configured')
                                    ->label('Header')
                                    ->markdown(),
                            ]),

                        Infolists\Components\Fieldset::make('Footer Lines')
                            ->schema([
                                Infolists\Components\TextEntry::make('trailer_lines')
                                    ->getStateUsing(fn (Terminal $record) => implode("\n", $record->getTrailerLinesAttribute()))
                                    ->placeholder('No footer lines configured')
                                    ->label('Footer')
                                    ->markdown(),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Infolists\Components\Section::make('Lodging Configuration')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('default_checkin')
                                    ->date()
                                    ->label('Default Check-in')
                                    ->placeholder('Not configured'),

                                Infolists\Components\TextEntry::make('default_checkout')
                                    ->date()
                                    ->label('Default Check-out')
                                    ->placeholder('Not configured'),
                            ]),

                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('default_room_rate')
                                    ->money('USD', divideBy: 100)
                                    ->label('Default Room Rate')
                                    ->placeholder('Not configured'),

                                Infolists\Components\TextEntry::make('default_room_number')
                                    ->label('Default Room Number')
                                    ->placeholder('Not configured'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->visible(fn (Terminal $record) => $record->default_checkin || $record->default_checkout || $record->default_room_rate || $record->default_room_number),

                Infolists\Components\Section::make('System Information')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('fortis_created_at')
                                    ->dateTime()
                                    ->label('Created in Fortis')
                                    ->placeholder('Not available'),

                                Infolists\Components\TextEntry::make('fortis_modified_at')
                                    ->dateTime()
                                    ->label('Modified in Fortis')
                                    ->placeholder('Not available'),

                                Infolists\Components\TextEntry::make('last_registration_ts')
                                    ->dateTime()
                                    ->label('Last Registration')
                                    ->placeholder('Not available'),
                            ]),

                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('created_at')
                                    ->dateTime()
                                    ->label('Created Locally'),

                                Infolists\Components\TextEntry::make('updated_at')
                                    ->dateTime()
                                    ->label('Last Updated'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->icon('heroicon-o-pencil'),

            Actions\Action::make('sync')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->action(function () {
                    try {
                        $synced = Terminal::syncSingleFromFortis($this->record->fortis_id);
                        if ($synced) {
                            Notification::make()
                                ->title('Terminal synced successfully')
                                ->success()
                                ->send();

                            $this->refreshFormData(['*']);
                        } else {
                            Notification::make()
                                ->title('Terminal not found in Fortis API')
                                ->warning()
                                ->send();
                        }
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Sync failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->visible(fn () => ! empty($this->record->fortis_id))
                ->requiresConfirmation(),

            Actions\Action::make('test_payment')
                ->icon('heroicon-o-credit-card')
                ->color('warning')
                ->form([
                    Forms\Components\TextInput::make('amount')
                        ->required()
                        ->numeric()
                        ->default(100)
                        ->suffix('cents')
                        ->label('Test Amount'),
                    Forms\Components\TextInput::make('description')
                        ->default('Test transaction')
                        ->label('Description'),
                ])
                ->action(function (array $data) {
                    try {
                        $result = $this->record->processPayment(
                            amount: $data['amount'],
                            options: [
                                'description' => $data['description'],
                                'order_number' => 'TEST-'.now()->format('YmdHis'),
                            ]
                        );

                        if ($result['success']) {
                            Notification::make()
                                ->title('Test payment successful')
                                ->body("Transaction ID: {$result['transaction_id']}")
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Test payment failed')
                                ->body($result['error'] ?? 'Unknown error')
                                ->danger()
                                ->send();
                        }
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Test payment error')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->visible(fn () => $this->record->isReadyForPayments())
                ->requiresConfirmation()
                ->modalDescription('This will process a real test transaction. Make sure you are in a test environment.'),

            Actions\DeleteAction::make()
                ->icon('heroicon-o-trash'),
        ];
    }
}
