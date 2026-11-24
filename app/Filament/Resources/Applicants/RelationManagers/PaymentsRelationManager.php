<?php

namespace App\Filament\Resources\Applicants\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

        protected static ?string $title = 'পেমেন্ট';

    protected static ?string $recordTitleAttribute = 'payment_no';

    protected static bool $isLazy = false;

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query
                ->with('verifier')
            )

            ->columns([

                TextColumn::make('payment_no')
                    ->label('পেমেন্ট নম্বর')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('payment_for')
                    ->label('পেমেন্টের ধরন')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'application_fee' => 'আবেদন ফি',
                        'security_deposit' => 'জামানত',
                        'daily_rent' => 'দৈনিক ভাড়া',
                        'security_deposit_refund' => 'জামানত ফেরত',
                        'other' => 'অন্যান্য',
                        default => $state ?? '—',
                    }),

                TextColumn::make('gateway')
                    ->label('পেমেন্ট মাধ্যম')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'janata_pay' => 'জনতা পে',
                        'bkash' => 'বিকাশ',
                        'nagad' => 'নগদ',
                        'bangla_qr' => 'বাংলা QR',
                        'manual' => 'ম্যানুয়াল',
                        default => $state ?? '—',
                    }),

                TextColumn::make('amount')
                    ->label('পরিমাণ')
                    ->formatStateUsing(
                        fn ($state) => '৳ ' . number_format((float) $state, 2)
                    )
                    ->sortable(),

                TextColumn::make('status')
                    ->label('পেমেন্ট স্ট্যাটাস')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'initiated' => 'শুরু হয়েছে',
                        'pending' => 'অপেক্ষমাণ',
                        'success' => 'সফল',
                        'failed' => 'ব্যর্থ',
                        'canceled' => 'বাতিল',
                        'refunded' => 'ফেরত দেওয়া হয়েছে',
                        default => $state ?? '—',
                    })
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'success' => 'success',
                        'pending', 'initiated' => 'warning',
                        'failed', 'canceled' => 'danger',
                        'refunded' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('gateway_transaction_id')
                    ->label('Transaction ID')
                    ->searchable()
                    ->placeholder('—')
                    ->copyable(),

                TextColumn::make('paid_at')
                    ->label('পেমেন্টের তারিখ')
                    ->dateTime('d/m/Y h:i A')
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('verified_at')
                    ->label('যাচাইয়ের তারিখ')
                    ->dateTime('d/m/Y h:i A')
                    ->sortable()
                    ->placeholder('যাচাই হয়নি'),

                TextColumn::make('verifier.name')
                    ->label('যাচাইকারী')
                    ->placeholder('—'),

            ])

            ->defaultSort('created_at', 'desc')

            ->striped()

            ->emptyStateHeading('কোনো পেমেন্ট লেনদেন নেই')

            ->emptyStateDescription(
                'এই আবেদনকারীর কোনো পেমেন্ট লেনদেন পাওয়া যায়নি।'
            )

            ->paginated([10, 25, 50]);
    }

    // public function form(Schema $schema): Schema
    // {
    //     return $schema
    //         ->components([
    //             TextInput::make('payment_no')
    //                 ->required()
    //                 ->maxLength(255),
    //         ]);
    // }

    // public function table(Table $table): Table
    // {
    //     return $table
    //         ->recordTitleAttribute('payment_no')
    //         ->columns([
    //             TextColumn::make('payment_no')
    //                 ->searchable(),
    //         ])
    //         ->filters([
    //             TrashedFilter::make(),
    //         ])
    //         ->headerActions([
    //             CreateAction::make(),
    //             AssociateAction::make(),
    //         ])
    //         ->recordActions([
    //             EditAction::make(),
    //             DissociateAction::make(),
    //             DeleteAction::make(),
    //             ForceDeleteAction::make(),
    //             RestoreAction::make(),
    //         ])
    //         ->toolbarActions([
    //             BulkActionGroup::make([
    //                 DissociateBulkAction::make(),
    //                 DeleteBulkAction::make(),
    //                 ForceDeleteBulkAction::make(),
    //                 RestoreBulkAction::make(),
    //             ]),
    //         ])
    //         ->modifyQueryUsing(fn (Builder $query) => $query
    //             ->withoutGlobalScopes([
    //                 SoftDeletingScope::class,
    //             ]));
    // }
}
