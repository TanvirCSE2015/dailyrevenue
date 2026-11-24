<?php

namespace App\Filament\Resources\Applicants\Schemas;

use App\Models\Applicant;
use App\Services\ApplicantFinancialSummaryService;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\View;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;

class ApplicantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    // ধাপ ১: ব্যক্তিগত তথ্য
                    Step::make('ব্যক্তিগত তথ্য')
                        // ->description('ব্যক্তিগত ও পরিচয় সংক্রান্ত তথ্য')
                        ->icon('heroicon-o-user')
                        ->schema([
                            TextInput::make('applicant_no')
                                ->label('আবেদনকারী নম্বর')
                                ->placeholder('স্বয়ংক্রিয়ভাবে তৈরি হবে')
                                ->disabled()
                                ->dehydrated(false)
                                ->visibleOn(['edit', 'view']),

                            TextInput::make('name')
                                ->label('আবেদনকারীর নাম')
                                ->placeholder('আবেদনকারীর পূর্ণ নাম লিখুন')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('father_name')
                                ->label('পিতার নাম')
                                ->placeholder('পিতার নাম লিখুন')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('mother_name')
                                ->label('মাতার নাম')
                                ->placeholder('মাতার নাম লিখুন')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('mobile')
                                ->label('মোবাইল নম্বর')
                                ->placeholder('01XXXXXXXXX')
                                ->tel()
                                ->required()
                                ->unique(
                                    table: 'applicants',
                                    column: 'mobile',
                                    ignoreRecord: true,
                                )
                                ->maxLength(20),

                            TextInput::make('nid')
                                ->label('জাতীয় পরিচয়পত্র নম্বর')
                                ->placeholder('NID নম্বর লিখুন')
                                ->required()
                                ->unique(
                                    table: 'applicants',
                                    column: 'nid',
                                    ignoreRecord: true,
                                )
                                ->maxLength(30),
                            Toggle::make('is_active')
                                ->label('আবেদনকারী সক্রিয়')
                                ->default(true)
                                ->inline(false),
                        ])
                        ->columns(4),

                    // ধাপ ২: ঠিকানা
                    Step::make('ঠিকানা')
                        // ->description('বর্তমান ও স্থায়ী ঠিকানা')
                        ->icon('heroicon-o-map-pin')
                        ->schema([
                            Textarea::make('present_address')
                                ->label('বর্তমান ঠিকানা')
                                ->placeholder('আবেদনকারীর বর্তমান ঠিকানা লিখুন')
                                ->required()
                                ->rows(2),

                            Textarea::make('permanent_address')
                                ->label('স্থায়ী ঠিকানা')
                                ->placeholder('আবেদনকারীর স্থায়ী ঠিকানা লিখুন')
                                ->required()
                                ->rows(2),
                        ])
                        ->columns(2),

                    // ধাপ ৩: ছবি ও ডকুমেন্ট
                    Step::make('ছবি ও ডকুমেন্ট')
                        // ->description('ছবি এবং NID-এর কপি সংযুক্ত করুন')
                        ->icon('heroicon-o-identification')
                        ->schema([
                            FileUpload::make('image')
                                ->label('আবেদনকারীর ছবি')
                                ->image()
                                ->imageEditor()
                                ->disk('public')
                                ->directory('applicants/images')
                                ->visibility('public')
                                ->maxSize(2048)
                                ->acceptedFileTypes([
                                    'image/jpeg',
                                    'image/png',
                                    'image/webp',
                                ])
                                ->helperText('JPG, PNG অথবা WEBP। সর্বোচ্চ ২ MB।')
                                ->required(),

                            FileUpload::make('nid_front')
                                ->label('NID-এর সামনের অংশ')
                                ->image()
                                ->imageEditor()
                                ->disk('public')
                                ->directory('applicants/nid/front')
                                ->visibility('public')
                                ->maxSize(2048)
                                ->acceptedFileTypes([
                                    'image/jpeg',
                                    'image/png',
                                    'image/webp',
                                ])
                                ->helperText('জাতীয় পরিচয়পত্রের সামনের অংশের ছবি')
                                ->required(),

                            FileUpload::make('nid_back')
                                ->label('NID-এর পেছনের অংশ')
                                ->image()
                                ->imageEditor()
                                ->disk('public')
                                ->directory('applicants/nid/back')
                                ->visibility('public')
                                ->maxSize(2048)
                                ->acceptedFileTypes([
                                    'image/jpeg',
                                    'image/png',
                                    'image/webp',
                                ])
                                ->helperText('জাতীয় পরিচয়পত্রের পেছনের অংশের ছবি')
                                ->required(),

                            
                        ])
                        ->columns(3),
                          // ধাপ ৪: আর্থিক সারসংক্ষেপ
                        Step::make('আর্থিক সারসংক্ষেপ')
                            // ->description('আবেদনকারীর সব ধরনের আর্থিক হিসাব')
                            ->icon('heroicon-o-banknotes')
                            ->visibleOn(['edit','view'])
                            ->schema([
                                View::make(
                                    'backend.applicants.financial-summary'
                                )
                                    ->viewData(fn (?Applicant $record): array => [
                                        'summary' => $record
                                            ? app(ApplicantFinancialSummaryService::class)
                                                ->calculate($record)
                                            : null,
                                    ]),
                            ]),
                ])
                    ->columnSpanFull()
                    ->skippable()
                    ->persistStepInQueryString(),
            ]);
    }
}
