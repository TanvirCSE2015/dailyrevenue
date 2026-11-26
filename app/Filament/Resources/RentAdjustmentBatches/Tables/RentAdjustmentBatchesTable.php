<?php

namespace App\Filament\Resources\RentAdjustmentBatches\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RentAdjustmentBatchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
             ->columns([
                TextColumn::make('id')
                    ->label('ক্রমিক')
                    ->sortable(),

                TextColumn::make('applicant.applicant_no')
                    ->label('আবেদনকারী নম্বর')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('adjustment_type')
                    ->label('মওকুফের কারণ')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'illness' => 'অসুস্থতা',
                        'absence' => 'অনুপস্থিতি',
                        'rain' => 'বৃষ্টি',
                        'other' => 'অন্যান্য',
                        default => '—',
                    })
                    ->badge(),

                TextColumn::make('adjustment_basis')
                    ->label('মওকুফের পদ্ধতি')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'days' => 'দিনের ভিত্তিতে',
                        'dates' => 'তারিখের ভিত্তিতে',
                        'amount' => 'টাকার ভিত্তিতে',
                        default => '—',
                    })
                    ->badge(),

                TextColumn::make('adjustment_days')
                    ->label('মওকুফের দিন')
                    ->formatStateUsing(
                        fn ($state): string =>
                            $state !== null ? "{$state} দিন" : '—'
                    ),

                TextColumn::make('requested_amount')
                    ->label('নির্ধারিত মওকুফ')
                    ->formatStateUsing(
                        fn ($state): string =>
                            $state !== null
                                ? '৳ ' . number_format((float) $state, 2)
                                : '—'
                    ),

                TextColumn::make('approved_amount')
                    ->label('প্রকৃত মওকুফ')
                    ->formatStateUsing(
                        fn ($state): string =>
                            '৳ ' . number_format((float) $state, 2)
                    )
                    ->sortable(),

                TextColumn::make('status')
                    ->label('অবস্থা')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'pending' => 'অপেক্ষমাণ',
                        'approved' => 'অনুমোদিত',
                        'rejected' => 'বাতিল',
                        default => '—',
                    })
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('requester.name')
                    ->label('কার্যকরকারী Admin')
                    ->placeholder('—'),

                TextColumn::make('approved_at')
                    ->label('কার্যকরের তারিখ')
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),

            ])
            ->filters([

                SelectFilter::make('adjustment_type')
                    ->label('মওকুফের কারণ')
                    ->options([
                        'illness' => 'অসুস্থতা',
                        'absence' => 'অনুপস্থিতি',
                        'rain' => 'বৃষ্টি',
                        'other' => 'অন্যান্য',
                    ]),

                SelectFilter::make('adjustment_basis')
                    ->label('মওকুফের পদ্ধতি')
                    ->options([
                        'days' => 'দিনের ভিত্তিতে',
                        'dates' => 'তারিখের ভিত্তিতে',
                        'amount' => 'টাকার ভিত্তিতে',
                    ]),

                SelectFilter::make('status')
                    ->label('অবস্থা')
                    ->options([
                        'pending' => 'অপেক্ষমাণ',
                        'approved' => 'অনুমোদিত',
                        'rejected' => 'বাতিল',
                    ]),

            ])
            ->recordActions([
                ViewAction::make()
                    ->label('বিস্তারিত'),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
