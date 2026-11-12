<?php

namespace App\Filament\Resources\Applicants\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ApplicantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
               ImageColumn::make('image')
                    ->label('ছবি')
                    ->disk('public')
                    ->circular()
                    ->size(45),

                TextColumn::make('applicant_no')
                    ->label('আবেদনকারী নম্বর')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold')
                    ->color('primary'),

                TextColumn::make('name')
                    ->label('আবেদনকারীর নাম')
                    ->searchable()
                    ->sortable()
                    ->description(
                        fn ($record) => 'পিতা: ' . ($record->father_name ?? '—')
                    ),

                TextColumn::make('mobile')
                    ->label('মোবাইল নম্বর')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('nid')
                    ->label('NID নম্বর')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('applications_count')
                    ->label('মোট আবেদন')
                    ->counts('applications')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('info'),

                TextColumn::make('activeAllocation.plot.plot_number')
                    ->label('বরাদ্দকৃত প্লট')
                    ->placeholder('বরাদ্দ নেই')
                    ->badge()
                    ->color('success')
                    ->toggleable(),

                TextColumn::make('is_active')
                    ->label('অবস্থা')
                    ->badge()
                    ->formatStateUsing(
                        fn ($state): string => $state
                            ? 'সক্রিয়'
                            : 'নিষ্ক্রিয়'
                    )
                    ->color(
                        fn ($state): string => $state
                            ? 'success'
                            : 'danger'
                    ),

                TextColumn::make('created_at')
                    ->label('নিবন্ধনের তারিখ')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('is_active')
                    ->label('আবেদনকারীর অবস্থা')
                    ->options([
                        '1' => 'সক্রিয়',
                        '0' => 'নিষ্ক্রিয়',
                    ]),

            ])
            ->recordActions([
                ViewAction::make('দেখুন')
                    ->icon(Heroicon::Eye),
                EditAction::make()
                    ->label('সম্পাদনা')
                    ->icon('heroicon-o-pencil-square'),
                
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->emptyStateHeading('কোনো আবেদনকারী পাওয়া যায়নি')
            ->emptyStateDescription(
                'নতুন আবেদনকারী নিবন্ধন করতে আবেদনকারী যুক্ত করুন বাটনে ক্লিক করুন।'
            )
            ->emptyStateIcon('heroicon-o-users');
    }
}
