<?php

namespace Hyrograsper\LunarFortis\Filament\Resources\TerminalResource\Pages;

use Filament\Actions;
use Filament\Forms;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\RawJs;
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

                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\IconEntry::make('active')
                                    ->boolean()
                                    ->label('Active'),

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

                Infolists\Components\Section::make('Configuration Details')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('terminal_application_id')
                                    ->label('Application ID')
                                    ->placeholder('Not configured'),

                                Infolists\Components\TextEntry::make('terminal_manufacturer_code')
                                    ->label('Manufacturer Code')
                                    ->placeholder('Not configured'),

                                Infolists\Components\TextEntry::make('default_product_transaction_id')
                                    ->label('Default Product Transaction ID')
                                    ->placeholder('Not configured'),
                            ]),
                    ]),

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
                    ]),
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

<<<<<<< HEAD
            Actions\Action::make('test_payment')
=======
            Actions\Action::make('capture_payment')
>>>>>>> origin/main
                ->icon('heroicon-o-credit-card')
                ->color('warning')
                ->form([
                    Forms\Components\TextInput::make('amount')
                        ->required()
                        ->numeric()
                        ->inputMode('decimal')
                        ->mask(RawJs::make('$money($input, \'.\', \'\', 2)'))
                        ->default(100)
                        ->suffix('dollars')
                        ->label('Test Amount'),
                    Forms\Components\TextInput::make('description')
                        ->helperText('To help identify the transaction')
                        ->label('Description'),
<<<<<<< HEAD
                    Forms\Components\Select::make('flow_type')
                        ->options([
                            'complete' => 'Complete Payment (authorize + capture)',
                            'authorize' => 'Authorization Only',
                        ])
                        ->default('complete')
                        ->required()
                        ->label('Payment Flow'),
                ])
                ->action(function (array $data) {
                    try {
                        $amount = (int) bcmul($data['amount'], '100');
                        $options = [
                            'description' => $data['description'],
                            'order_number' => 'TEST-'.now()->format('YmdHis'),
                        ];

                        $result = match ($data['flow_type']) {
                            'complete' => $this->record->processCompletePayment($amount, $options),
                            'authorize' => $this->record->authorizePayment($amount, $options),
                            default => $this->record->processCompletePayment($amount, $options),
                        };
=======
                ])
                ->action(function (array $data) {
                    try {
                        $result = $this->record->processPayment(
                            amount: (int) bcmul($data['amount'], '100'),
                            options: [
                                'description' => $data['description'],
                                'order_number' => 'TEST-'.now()->format('YmdHis'),
                            ]
                        );
>>>>>>> origin/main

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
                ->modalDescription('This will process a real transaction.'),

            Actions\DeleteAction::make()
                ->icon('heroicon-o-trash'),
        ];
    }
}
