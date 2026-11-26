<?php

namespace App\Filament\Resources\Applicants\Schemas;

use App\Models\Applicant;
use App\Services\ApplicantFinancialSummaryService;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;

class ApplicantInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                 Grid::make(3)
                    ->schema([

                        // বাম পাশ: আবেদনকারীর বিস্তারিত তথ্য
                        Grid::make(1)
                            ->schema([

                                // ১. ব্যক্তিগত তথ্য
                                Section::make('ব্যক্তিগত তথ্য')
                                    ->description('আবেদনকারীর ব্যক্তিগত ও পরিচয় সংক্রান্ত তথ্য')
                                    ->icon('heroicon-o-user')
                                    ->schema([
                                        TextEntry::make('applicant_no')
                                            ->label('আবেদনকারী নম্বর')
                                            ->weight('bold')
                                            ->color('primary')
                                            ->copyable(),

                                        TextEntry::make('created_at')
                                            ->label('নিবন্ধনের তারিখ')
                                            ->dateTime('d/m/Y h:i A'),

                                        TextEntry::make('is_active')
                                            ->label('আবেদনকারীর অবস্থা')
                                            ->formatStateUsing(fn ($state): string => $state
                                                ? 'সক্রিয়'
                                                : 'নিষ্ক্রিয়')
                                            ->badge()
                                            ->color(fn ($state): string => $state
                                                ? 'success'
                                                : 'danger'),
                                        TextEntry::make('name')
                                            ->label('আবেদনকারীর নাম')
                                            ->weight('bold'),

                                        TextEntry::make('father_name')
                                            ->label('পিতার নাম'),

                                        TextEntry::make('mother_name')
                                            ->label('মাতার নাম'),

                                        TextEntry::make('mobile')
                                            ->label('মোবাইল নম্বর')
                                            ->copyable(),

                                        TextEntry::make('nid')
                                            ->label('জাতীয় পরিচয়পত্র নম্বর')
                                            ->copyable(),
                                    ])
                                    ->columns(3),

                                // ২. ঠিকানা
                                Section::make('ঠিকানা')
                                    ->description('আবেদনকারীর বর্তমান ও স্থায়ী ঠিকানা')
                                    ->icon('heroicon-o-map-pin')
                                    ->schema([
                                        TextEntry::make('present_address')
                                            ->label('বর্তমান ঠিকানা'),

                                        TextEntry::make('permanent_address')
                                            ->label('স্থায়ী ঠিকানা'),
                                    ])
                                    ->columns(2),

                                // ৩. ছবি ও ডকুমেন্ট
                                Section::make('ছবি ও ডকুমেন্ট')
                                    ->description('আবেদনকারীর ছবি এবং জাতীয় পরিচয়পত্র')
                                    ->icon('heroicon-o-identification')
                                    ->schema([
                                        ImageEntry::make('image')
                                            ->label('আবেদনকারীর ছবি')
                                            ->disk('public')
                                            ->height(160)
                                            ->square(),

                                        ImageEntry::make('nid_front')
                                            ->label('NID-এর সামনের অংশ')
                                            ->disk('public')
                                            ->height(160),

                                        ImageEntry::make('nid_back')
                                            ->label('NID-এর পেছনের অংশ')
                                            ->disk('public')
                                            ->height(160),
                                    ])
                                    ->columns(3),

                                // ৪. আবেদনকারীর পরিচিতি
                                // Section::make('আবেদনকারীর পরিচিতি')
                                //     ->description('আবেদনকারীর নিবন্ধন ও বর্তমান অবস্থা')
                                //     ->icon('heroicon-o-identification')
                                //     ->schema([
                                //         TextEntry::make('applicant_no')
                                //             ->label('আবেদনকারী নম্বর')
                                //             ->weight('bold')
                                //             ->copyable(),

                                //         TextEntry::make('created_at')
                                //             ->label('নিবন্ধনের তারিখ')
                                //             ->dateTime('d/m/Y h:i A'),

                                //         TextEntry::make('is_active')
                                //             ->label('আবেদনকারীর অবস্থা')
                                //             ->formatStateUsing(fn ($state): string => $state
                                //                 ? 'সক্রিয়'
                                //                 : 'নিষ্ক্রিয়')
                                //             ->badge()
                                //             ->color(fn ($state): string => $state
                                //                 ? 'success'
                                //                 : 'danger'),
                                //     ])
                                //     ->columns(3),

                            ])
                            ->columnSpan(2),

                        // ডান পাশ: আর্থিক সারসংক্ষেপ
                        Section::make('আর্থিক সারসংক্ষেপ')
                            ->description('আবেদনকারীর আর্থিক হিসাব')
                            ->icon('heroicon-o-banknotes')
                            ->schema([
                                View::make('backend.applicants.financial-summary')
                                    ->viewData(fn (?Applicant $record): array => [
                                        'summary' => $record
                                            ? app(ApplicantFinancialSummaryService::class)
                                                ->calculate($record)
                                            : null,
                                    ]),
                            ])
                            ->columnSpan(1),

                    ])
                    ->columnSpanFull(),
            ]);
    }
}
