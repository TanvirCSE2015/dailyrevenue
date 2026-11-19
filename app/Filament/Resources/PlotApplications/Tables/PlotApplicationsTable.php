<?php

namespace App\Filament\Resources\PlotApplications\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class PlotApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('applicant.image')
                    ->label('ছবি')
                    ->disk('public')
                    ->circular()
                    ->size(45),

                TextColumn::make('application_no')
                    ->label('আবেদন নম্বর')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->color('primary'),

                // TextColumn::make('applicant.applicant_no')
                //     ->label('আবেদনকারী নম্বর')
                //     ->searchable()
                //     ->sortable(),

                TextColumn::make('applicant.name')
                    ->label('আবেদনকারীর নাম')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('applicant.mobile')
                    ->label('মোবাইল নম্বর')
                    ->searchable(),

                TextColumn::make('plot.plot_number')
                    ->label('আবেদনকৃত প্লট')
                    ->sortable()
                    ->placeholder('নির্ধারিত নয়'),

                TextColumn::make('applied_at')
                    ->label('আবেদনের তারিখ')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('application_fee_amount')
                    ->label('আবেদন ফি')
                    ->formatStateUsing(
                        fn ($state) => '৳ ' . number_format((float) $state, 2)
                    )
                    ->sortable(),

                TextColumn::make('status')
                    ->label('আবেদনের অবস্থা')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending_payment' => 'পেমেন্ট অপেক্ষমাণ',
                        'payment_submitted' => 'পেমেন্ট যাচাই অপেক্ষমাণ',
                        'submitted' => 'জমা হয়েছে',
                        'under_review' => 'যাচাই চলছে',
                        'approved' => 'অনুমোদিত',
                        'rejected' => 'বাতিল',
                        'cancelled' => 'বাতিলকৃত',
                        'allocated' => 'প্লট বরাদ্দ হয়েছে',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending_payment' => 'warning',
                        'payment_submitted' => 'info',
                        'submitted' => 'primary',
                        'under_review' => 'info',
                        'approved' => 'success',
                        'allocated' => 'success',
                        'rejected', 'cancelled' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('তৈরির সময়')
                    ->dateTime('d/m/Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
