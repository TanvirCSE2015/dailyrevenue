<?php

namespace App\Filament\Resources\PublicNotices\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PublicNoticesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                 TextColumn::make('title')
                    ->label('নোটিশের শিরোনাম')
                    ->searchable()
                    ->sortable()
                    ->limit(45)
                    ->weight('medium'),

                TextColumn::make('notice_type')
                    ->label('নোটিশের ধরন')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'general' => 'সাধারণ',
                        'application' => 'আবেদন',
                        'allocation' => 'প্লট বরাদ্দ',
                        'payment' => 'পেমেন্ট',
                        'urgent' => 'জরুরি',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'urgent' => 'danger',
                        'application' => 'info',
                        'allocation' => 'success',
                        'payment' => 'warning',
                        default => 'gray',
                    }),

                IconColumn::make('is_important')
                    ->label('গুরুত্বপূর্ণ')
                    ->boolean(),

                TextColumn::make('published_at')
                    ->label('প্রকাশের তারিখ')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('expires_at')
                    ->label('মেয়াদ শেষ')
                    ->date('d/m/Y')
                    ->placeholder('নির্দিষ্ট নয়')
                    ->sortable(),

                ToggleColumn::make('is_published')
                    ->label('প্রকাশিত')
                    ->sortable(),

                TextColumn::make('creator.name')
                    ->label('তৈরিকারী')
                    ->placeholder('অজানা')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('তৈরির তারিখ')
                    ->dateTime('d/m/Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([

                SelectFilter::make('notice_type')
                    ->label('নোটিশের ধরন')
                    ->options([
                        'general' => 'সাধারণ',
                        'application' => 'আবেদন',
                        'allocation' => 'প্লট বরাদ্দ',
                        'payment' => 'পেমেন্ট',
                        'urgent' => 'জরুরি',
                    ]),

                TernaryFilter::make('is_important')
                    ->label('গুরুত্বপূর্ণ নোটিশ'),

                TernaryFilter::make('is_published')
                    ->label('প্রকাশের অবস্থা')
                    ->trueLabel('প্রকাশিত')
                    ->falseLabel('অপ্রকাশিত')
                    ->placeholder('সকল নোটিশ'),

            ])
            ->recordActions([
                EditAction::make()
                    ->label('সম্পাদনা'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('নির্বাচিত নোটিশ মুছুন'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->emptyStateHeading('কোনো নোটিশ পাওয়া যায়নি')
            ->emptyStateDescription('নতুন নোটিশ তৈরি করে প্রকাশ করতে পারেন।');
    }
}
