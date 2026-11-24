<?php

namespace App\Filament\Resources\Applicants\RelationManagers;

use App\Models\DailyRentLedger;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OutstandingRentRelationManager extends RelationManager
{
    protected static string $relationship = 'dailyRentLedgers';

    protected static ?string $title = 'বকেয়া ভাড়া';
    protected static ?string $recordTitleAttribute = 'rent_date';
    protected static bool $isLazy = false;

    public function table(Table $table): Table
    {
        $morphClass = (new DailyRentLedger())->getMorphClass();

        return $table
            ->modifyQueryUsing(function (Builder $query) use ($morphClass) {

                $query
                    ->with([
                        'allocation.plot',

                        'adjustments' => fn ($query) =>
                            $query->where('status', 'approved'),

                        'paymentAllocations' => fn ($query) =>
                            $query->whereHas('payment', function ($paymentQuery) {
                                $paymentQuery
                                    ->where('status', 'success')
                                    ->whereNotNull('verified_at');
                            }),
                    ])
                    ->whereRaw(
                        "
                        (
                            COALESCE(daily_rent_ledgers.base_amount, 0)

                            - LEAST(
                                COALESCE(daily_rent_ledgers.base_amount, 0),

                                COALESCE((
                                    SELECT SUM(ra.approved_amount)
                                    FROM rent_adjustments AS ra
                                    WHERE ra.daily_rent_ledger_id = daily_rent_ledgers.id
                                    AND ra.status = 'approved'
                                ), 0)
                            )

                            - COALESCE((
                                SELECT SUM(pa.amount)
                                FROM payment_allocations AS pa
                                INNER JOIN payments AS p
                                    ON p.id = pa.payment_id
                                WHERE pa.payable_type = ?
                                AND pa.payable_id = daily_rent_ledgers.id
                                AND p.status = 'success'
                                AND p.verified_at IS NOT NULL
                            ), 0)
                        ) > 0
                        ",
                        [$morphClass]
                    );
            })

            ->columns([

                TextColumn::make('rent_date')
                    ->label('ভাড়ার তারিখ')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('allocation.plot.plot_number')
                    ->label('প্লট নম্বর')
                    ->placeholder('—'),

                TextColumn::make('base_amount')
                    ->label('মূল ভাড়া')
                    ->formatStateUsing(
                        fn ($state) => '৳ ' . number_format((float) $state, 2)
                    ),

                TextColumn::make('waived_amount')
                    ->label('মওকুফ')
                    ->state(function (DailyRentLedger $record): string {
                        $baseAmount = (float) $record->base_amount;

                        $waivedAmount = (float) $record->adjustments
                            ->sum('approved_amount');

                        $waivedAmount = min($baseAmount, $waivedAmount);

                        return '৳ ' . number_format($waivedAmount, 2);
                    }),

                TextColumn::make('paid_amount')
                    ->label('পরিশোধিত')
                    ->state(function (DailyRentLedger $record): string {
                        $paidAmount = (float) $record->paymentAllocations
                            ->sum('amount');

                        return '৳ ' . number_format($paidAmount, 2);
                    }),

                TextColumn::make('due_amount')
                    ->label('প্রকৃত বকেয়া')
                    ->state(function (DailyRentLedger $record): string {
                        $baseAmount = (float) $record->base_amount;

                        $waivedAmount = (float) $record->adjustments
                            ->sum('approved_amount');

                        $waivedAmount = min($baseAmount, $waivedAmount);

                        $paidAmount = (float) $record->paymentAllocations
                            ->sum('amount');

                        $dueAmount = max(
                            0,
                            $baseAmount - $waivedAmount - $paidAmount
                        );

                        return '৳ ' . number_format($dueAmount, 2);
                    })
                    ->color('danger')
                    ->weight('bold'),
                    

            ])

            ->defaultSort('rent_date', 'asc')

            ->striped()

            ->emptyStateHeading('কোনো বকেয়া ভাড়া নেই')
            ->emptyStateDescription(
                'এই আবেদনকারীর বর্তমানে কোনো বকেয়া দৈনিক ভাড়া নেই।'
            )

            ->paginated([10, 25, 50]);
    }

    // public function form(Schema $schema): Schema
    // {
    //     return $schema
    //         ->components([
    //             TextInput::make('rent_date')
    //                 ->required()
    //                 ->maxLength(255),
    //         ]);
    // }

    // public function table(Table $table): Table
    // {
    //     return $table
    //         ->recordTitleAttribute('rent_date')
    //         ->columns([
    //             TextColumn::make('rent_date')
    //                 ->searchable(),
    //         ])
    //         ->filters([
    //             //
    //         ])
    //         ->headerActions([
    //             CreateAction::make(),
    //         ])
    //         ->recordActions([
    //             EditAction::make(),
    //             DeleteAction::make(),
    //         ])
    //         ->toolbarActions([
    //             BulkActionGroup::make([
    //                 DeleteBulkAction::make(),
    //             ]),
    //         ]);
    // }
}
