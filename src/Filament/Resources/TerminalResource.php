<?php

namespace Hyrograsper\LunarFortis\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Hyrograsper\LunarFortis\Filament\Resources\TerminalResource\Pages;
use Hyrograsper\LunarFortis\Models\Terminal;
use Illuminate\Database\Eloquent\Builder;

class TerminalResource extends Resource
{
    protected static ?string $model = Terminal::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('Settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('Fortis Terminals');
    }

    public static function getPluralLabel(): string
    {
        return __('Terminals');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Terminal Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->rules(['required', 'string', 'max:255'])
                            ->label('Terminal Name'),

                        Forms\Components\TextInput::make('serial_number')
                            ->required()
                            ->unique(Terminal::class, 'serial_number', ignoreRecord: true)
                            ->rules(['required', 'string', 'max:255'])
                            ->label('Serial Number'),

                        Forms\Components\TextInput::make('fortis_id')
                            ->unique(Terminal::class, 'fortis_id', ignoreRecord: true)
                            ->rules(['nullable', 'string', 'max:255'])
                            ->label('Fortis Terminal ID')
                            ->helperText('The terminal ID from Fortis API (auto-filled during sync)'),

                        Forms\Components\Select::make('location_id')
                            ->required()
                            ->default(config('services.fortis.locationId'))
                            ->options([
                                config('services.fortis.locationId') => 'Default Location',
                            ])
                            ->rules(['nullable', 'string', 'max:255'])
                            ->label('Location ID'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Configuration')
                    ->schema([
                        Forms\Components\TextInput::make('terminal_application_id')
                            ->rules(['nullable', 'string', 'max:255'])
                            ->label('Application ID'),

                        Forms\Components\TextInput::make('terminal_cvm_id')
                            ->rules(['nullable', 'string', 'max:255'])
                            ->label('CVM ID'),

                        Forms\Components\Select::make('terminal_manufacturer_code')
                            ->options(Terminal::getManufacturerCodeLabels())
                            ->rules(Terminal::getValidationRules()['terminal_manufacturer_code'])
                            ->label('Manufacturer Code'),

                        Forms\Components\TextInput::make('default_product_transaction_id')
                            ->rules(['nullable', 'string', 'max:255'])
                            ->label('Default Product Transaction ID'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Network Configuration')
                    ->schema([
                        Forms\Components\TextInput::make('mac_address')
                            ->rules(['nullable', 'string', 'max:255'])
                            ->label('MAC Address'),

                        Forms\Components\TextInput::make('local_ip_address')
                            ->rules(['nullable', 'ip'])
                            ->label('IP Address'),

                        Forms\Components\TextInput::make('port')
                            ->numeric()
                            ->rules(['nullable', 'integer', 'between:1,65535'])
                            ->label('Port'),

                        Forms\Components\TextInput::make('terminal_number')
                            ->rules(['nullable', 'string', 'max:255'])
                            ->label('Terminal Number'),

                        Forms\Components\Select::make('communication_type')
                            ->options(Terminal::getCommunicationTypeLabels())
                            ->rules(Terminal::getValidationRules()['communication_type'])
                            ->default('http')
                            ->label('Communication Type'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Terminal Capabilities')
                    ->schema([
                        Forms\Components\Toggle::make('active')
                            ->default(true)
                            ->label('Active'),

                        Forms\Components\Toggle::make('is_provisioned')
                            ->label('Is Provisioned'),

                        Forms\Components\Toggle::make('debit')
                            ->label('Debit Support'),

                        Forms\Components\Toggle::make('emv')
                            ->default(true)
                            ->label('EMV/Chip Support'),

                        Forms\Components\Toggle::make('cashback_enable')
                            ->label('Cashback Support'),

                        Forms\Components\Toggle::make('print_enable')
                            ->label('Print Receipts'),

                        Forms\Components\Toggle::make('sig_capture_enable')
                            ->label('Signature Capture'),

                        Forms\Components\Toggle::make('tip_enable')
                            ->label('Tip Support'),

                        Forms\Components\Toggle::make('validated_decryption')
                            ->label('Validated Decryption'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Receipt Customization')
                    ->schema([
                        Forms\Components\Fieldset::make('Header Lines')
                            ->schema([
                                Forms\Components\TextInput::make('header_line_1')->rules(['nullable', 'string', 'max:255'])->label('Line 1'),
                                Forms\Components\TextInput::make('header_line_2')->rules(['nullable', 'string', 'max:255'])->label('Line 2'),
                                Forms\Components\TextInput::make('header_line_3')->rules(['nullable', 'string', 'max:255'])->label('Line 3'),
                                Forms\Components\TextInput::make('header_line_4')->rules(['nullable', 'string', 'max:255'])->label('Line 4'),
                                Forms\Components\TextInput::make('header_line_5')->rules(['nullable', 'string', 'max:255'])->label('Line 5'),
                            ])
                            ->columns(1),

                        Forms\Components\Fieldset::make('Footer Lines')
                            ->schema([
                                Forms\Components\TextInput::make('trailer_line_1')->rules(['nullable', 'string', 'max:255'])->label('Line 1'),
                                Forms\Components\TextInput::make('trailer_line_2')->rules(['nullable', 'string', 'max:255'])->label('Line 2'),
                                Forms\Components\TextInput::make('trailer_line_3')->rules(['nullable', 'string', 'max:255'])->label('Line 3'),
                                Forms\Components\TextInput::make('trailer_line_4')->rules(['nullable', 'string', 'max:255'])->label('Line 4'),
                                Forms\Components\TextInput::make('trailer_line_5')->rules(['nullable', 'string', 'max:255'])->label('Line 5'),
                            ])
                            ->columns(1),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Lodging Configuration')
                    ->schema([
                        Forms\Components\DatePicker::make('default_checkin')
                            ->rules(['nullable', 'date'])
                            ->label('Default Check-in Date'),

                        Forms\Components\DatePicker::make('default_checkout')
                            ->rules(['nullable', 'date', 'after:default_checkin'])
                            ->label('Default Check-out Date'),

                        Forms\Components\TextInput::make('default_room_rate')
                            ->numeric()
                            ->rules(['nullable', 'integer', 'min:0'])
                            ->suffix('cents')
                            ->label('Default Room Rate'),

                        Forms\Components\TextInput::make('default_room_number')
                            ->rules(['nullable', 'string', 'max:255'])
                            ->label('Default Room Number'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->label('Terminal Name'),

                Tables\Columns\TextColumn::make('serial_number')
                    ->searchable()
                    ->sortable()
                    ->label('Serial Number'),

                Tables\Columns\IconColumn::make('active')
                    ->boolean()
                    ->label('Status'),

                Tables\Columns\IconColumn::make('is_provisioned')
                    ->boolean()
                    ->label('Provisioned'),

                Tables\Columns\TextColumn::make('capabilities')
                    ->getStateUsing(function (Terminal $record): string {
                        return $record->getCapabilitiesString();
                    })
                    ->badge()
                    ->separator(', ')
                    ->label('Capabilities'),

                Tables\Columns\TextColumn::make('synced_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Last Sync')
                    ->since(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggledHiddenByDefault()
                    ->label('Created'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('active')
                    ->label('Active Status'),

                Tables\Filters\TernaryFilter::make('is_provisioned')
                    ->label('Provisioned'),

                Tables\Filters\SelectFilter::make('terminal_manufacturer_code')
                    ->options([
                        '1' => 'Manufacturer 1',
                        '2' => 'Manufacturer 2',
                        '4' => 'Manufacturer 4',
                        '100' => 'Manufacturer 100',
                    ])
                    ->label('Manufacturer'),

                Tables\Filters\Filter::make('ready_for_payments')
                    ->query(fn (Builder $query): Builder => $query->where('active', true)->where('is_provisioned', true))
                    ->label('Ready for Payments'),

                Tables\Filters\Filter::make('needs_sync')
                    ->query(function (Builder $query): Builder {
                        return $query->where(function ($q) {
                            $q->whereNull('synced_at')
                                ->orWhere('synced_at', '<', now()->subHours(24));
                        });
                    })
                    ->label('Needs Sync'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('sync')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->action(function (Terminal $record) {
                        try {
                            $synced = Terminal::syncSingleFromFortis($record->fortis_id);
                            if ($synced) {
                                Notification::make()
                                    ->title('Terminal synced successfully')
                                    ->success()
                                    ->send();
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
                    ->visible(fn (Terminal $record) => ! empty($record->fortis_id))
                    ->requiresConfirmation(),

                Tables\Actions\Action::make('test_payment')
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
                    ->action(function (Terminal $record, array $data) {
                        try {
                            $result = $record->processPayment(
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
                    ->visible(fn (Terminal $record) => $record->isReadyForPayments())
                    ->requiresConfirmation()
                    ->modalDescription('This will process a real test transaction. Make sure you are in a test environment.'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('sync_selected')
                        ->icon('heroicon-o-arrow-path')
                        ->color('info')
                        ->action(function ($records) {
                            $synced = 0;
                            $errors = 0;

                            foreach ($records as $record) {
                                if (empty($record->fortis_id)) {
                                    continue;
                                }

                                try {
                                    $result = Terminal::syncSingleFromFortis($record->fortis_id);
                                    if ($result) {
                                        $synced++;
                                    }
                                } catch (\Exception $e) {
                                    $errors++;
                                }
                            }

                            Notification::make()
                                ->title("Sync completed: {$synced} synced, {$errors} errors")
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('toggle_active')
                        ->icon('heroicon-o-power')
                        ->color('warning')
                        ->form([
                            Forms\Components\Select::make('active')
                                ->required()
                                ->options([
                                    true => 'Activate',
                                    false => 'Deactivate',
                                ])
                                ->label('Action'),
                        ])
                        ->action(function ($records, array $data) {
                            $updated = 0;
                            foreach ($records as $record) {
                                $record->update(['active' => $data['active']]);
                                $updated++;
                            }

                            $action = $data['active'] ? 'activated' : 'deactivated';
                            Notification::make()
                                ->title("{$updated} terminals {$action}")
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('sync_all')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->action(function () {
                        try {
                            $stats = Terminal::syncFromFortis();

                            Notification::make()
                                ->title('Sync completed successfully')
                                ->body("Created: {$stats['created']}, Updated: {$stats['updated']}, Errors: {$stats['errors']}")
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Sync failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalDescription('This will sync all terminals from the Fortis API. This may take a few moments.'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTerminals::route('/'),
            'create' => Pages\CreateTerminal::route('/create'),
            'view' => Pages\ViewTerminal::route('/{record}'),
            'edit' => Pages\EditTerminal::route('/{record}/edit'),
        ];
    }
}
