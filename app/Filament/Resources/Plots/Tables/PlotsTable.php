<?php

namespace App\Filament\Resources\Plots\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class PlotsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('plot_number')
                    ->label('প্লট নম্বর')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold')
                    ->color('primary'),

                TextColumn::make('name')
                    ->label('প্লটের নাম')
                    ->searchable()
                    ->sortable()
                    ->placeholder('নাম দেওয়া হয়নি'),

                TextColumn::make('location')
                    ->label('অবস্থান')
                    ->searchable()
                    ->placeholder('উল্লেখ নেই'),

                TextColumn::make('length')
                    ->label('দৈর্ঘ্য')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' ফুট')
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('width')
                    ->label('প্রস্থ')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' ফুট')
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('status')
                    ->label('বর্তমান অবস্থা')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'available' => 'খালি',
                        'reserved' => 'সংরক্ষিত',
                        'allocated' => 'বরাদ্দকৃত',
                        'maintenance' => 'রক্ষণাবেক্ষণে',
                        'inactive' => 'নিষ্ক্রিয়',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'available' => 'success',
                        'reserved' => 'warning',
                        'allocated' => 'info',
                        'maintenance' => 'danger',
                        'inactive' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),
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
