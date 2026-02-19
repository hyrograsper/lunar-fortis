<?php

namespace Hyrograsper\LunarFortis\Filament\Resources\TerminalResource\Pages;

use Exception;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Hyrograsper\LunarFortis\Filament\Resources\TerminalResource;
use Hyrograsper\LunarFortis\Models\Terminal;

class ViewTerminal extends ViewRecord
{
    protected static string $resource = TerminalResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Terminal Overview')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('title')
                                    ->label('Terminal Name'),

                                TextEntry::make('serial_number')
                                    ->label('Serial Number'),

                                TextEntry::make('fortis_id')
                                    ->label('Fortis ID')
                                    ->placeholder('Not synced'),
                            ]),

                        Grid::make(3)
                            ->schema([
                                IconEntry::make('active')
                                    ->boolean()
                                    ->label('Active'),

                                TextEntry::make('isReadyForPayments')
                                    ->getStateUsing(fn (Terminal $record) => $record->isReadyForPayments() ? 'Ready' : 'Not Ready')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'Ready' => 'success',
                                        default => 'warning',
                                    })
                                    ->label('Payment Status'),

                                TextEntry::make('synced_at')
                                    ->dateTime()
                                    ->since()
                                    ->label('Last Sync')
                                    ->placeholder('Never synced'),
                            ]),
                    ]),

                Section::make('Configuration Details')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('terminal_application_id')
                                    ->label('Application ID')
                                    ->placeholder('Not configured'),

                                TextEntry::make('terminal_manufacturer_code')
                                    ->label('Manufacturer Code')
                                    ->placeholder('Not configured'),

                                TextEntry::make('default_product_transaction_id')
                                    ->label('Default Product Transaction ID')
                                    ->placeholder('Not configured'),
                            ]),
                    ]),

                Section::make('System Information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('fortis_created_at')
                                    ->dateTime()
                                    ->label('Created in Fortis')
                                    ->placeholder('Not available'),

                                TextEntry::make('fortis_modified_at')
                                    ->dateTime()
                                    ->label('Modified in Fortis')
                                    ->placeholder('Not available'),

                                TextEntry::make('last_registration_ts')
                                    ->dateTime()
                                    ->label('Last Registration')
                                    ->placeholder('Not available'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->dateTime()
                                    ->label('Created Locally'),

                                TextEntry::make('updated_at')
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
                    } catch (Exception $exception) {
                        Notification::make()
                            ->title('Sync failed')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->visible(fn () => ! empty($this->record->fortis_id))
                ->requiresConfirmation(),

            Actions\Action::make('test_payment')
                ->icon('heroicon-o-credit-card')
                ->color('warning')
                ->schema([
                    TextInput::make('amount')
                        ->required()
                        ->numeric()
                        ->inputMode('decimal')
                        ->mask(RawJs::make('$money($input, \'.\', \'\', 2)'))
                        ->default(100)
                        ->suffix('dollars')
                        ->label('Test Amount'),
                    TextInput::make('description')
                        ->helperText('To help identify the transaction')
                        ->label('Description'),
                    Select::make('flow_type')
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

                        $result = $data['flow_type'] === 'authorize'
                            ? $this->record->authorizePayment($amount, $options)
                            : $this->record->processCompletePayment($amount, $options);

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
                    } catch (Exception $exception) {
                        Notification::make()
                            ->title('Test payment error')
                            ->body($exception->getMessage())
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
