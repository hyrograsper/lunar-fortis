<?php

namespace Hyrograsper\LunarFortis\Filament\Resources;

use BackedEnum;
use Exception;
use Filament\Actions\Action as TableAction;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Hyrograsper\LunarFortis\Filament\Resources\TerminalResource\Pages;
use Hyrograsper\LunarFortis\Models\Terminal;
use Illuminate\Database\Eloquent\Builder;

class TerminalResource extends Resource
{
    protected static ?string $model = Terminal::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';

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

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Terminal Information')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->rules(['required', 'string', 'max:255'])
                            ->label('Terminal Name'),

                        Toggle::make('active')
                            ->default(true)
                            ->label('Active'),

                        TextInput::make('serial_number')
                            ->readOnly()
                            ->disabled()
                            ->required()
                            ->unique(Terminal::class, 'serial_number', ignoreRecord: true)
                            ->rules(['required', 'string', 'max:255'])
                            ->label('Serial Number'),

                        TextInput::make('fortis_id')
                            ->readOnly()
                            ->disabled()
                            ->unique(Terminal::class, 'fortis_id', ignoreRecord: true)
                            ->rules(['nullable', 'string', 'max:255'])
                            ->label('Fortis Terminal ID')
                            ->helperText('The terminal ID from Fortis API (auto-filled during sync)'),

                        Select::make('location_id')
                            ->required()
                            ->default(config('services.fortis.locationId'))
                            ->options([
                                config('services.fortis.locationId') => 'Default Location',
                            ])
                            ->rules(['nullable', 'string', 'max:255'])
                            ->label('Location ID'),

                        TextInput::make('terminal_application_id')
                            ->rules(['nullable', 'string', 'max:255'])
                            ->label('Application ID'),

                        Select::make('terminal_manufacturer_code')
                            ->options(Terminal::getManufacturerCodeLabels())
                            ->rules(Terminal::getValidationRules()['terminal_manufacturer_code'])
                            ->label('Manufacturer Code'),

                        TextInput::make('default_product_transaction_id')
                            ->rules(['nullable', 'string', 'max:255'])
                            ->label('Default Product Transaction ID'),
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

                Tables\Filters\SelectFilter::make('terminal_manufacturer_code')
                    ->options(Terminal::getManufacturerCodeLabels())
                    ->label('Manufacturer'),

                Tables\Filters\Filter::make('needs_sync')
                    ->query(function (Builder $query): Builder {
                        return $query->where(function ($q) {
                            $q->whereNull('synced_at')
                                ->orWhere('synced_at', '<', now()->subHours(24));
                        });
                    })
                    ->label('Needs Sync'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),

                TableAction::make('sync')
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
                        } catch (Exception $exception) {
                            Notification::make()
                                ->title('Sync failed')
                                ->body($exception->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn (Terminal $record) => ! empty($record->fortis_id))
                    ->requiresConfirmation(),

                TableAction::make('capture_payment')
                    ->icon('heroicon-o-credit-card')
                    ->color('warning')
                    ->schema([
                        TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->inputMode('decimal')
                            ->mask(RawJs::make('$money($input, \'.\', \'\', 2)'))
                            ->placeholder('100.00')
                            ->suffix('dollars')
                            ->label('Amount'),
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
                    ->action(function (Terminal $record, array $data) {
                        try {
                            $amount = (int) bcmul($data['amount'], '100');
                            $options = [
                                'description' => $data['description'],
                                'order_number' => now()->format('YmdHis'),
                            ];

                            $result = match ($data['flow_type']) {
                                'authorize' => $record->authorizePayment($amount, $options),
                                default => $record->processCompletePayment($amount, $options),
                            };

                            if ($result['success']) {
                                Notification::make()
                                    ->title('Successful')
                                    ->body("Transaction ID: {$result['transaction_id']}")
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Payment failed')
                                    ->body($result['error'] ?? 'Unknown error')
                                    ->danger()
                                    ->send();
                            }
                        } catch (Exception $exception) {
                            Notification::make()
                                ->title('Payment error')
                                ->body($exception->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn ($record) => $record->isReadyForPayments())
                    ->requiresConfirmation()
                    ->modalDescription('This will process a real transaction.'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),

                    BulkAction::make('sync_selected')
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
                                } catch (Exception $exception) {
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

                    BulkAction::make('toggle_active')
                        ->icon('heroicon-o-power')
                        ->color('warning')
                        ->schema([
                            Select::make('active')
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
                TableAction::make('sync_all')
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
                        } catch (Exception $exception) {
                            Notification::make()
                                ->title('Sync failed')
                                ->body($exception->getMessage())
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
        return [];
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
