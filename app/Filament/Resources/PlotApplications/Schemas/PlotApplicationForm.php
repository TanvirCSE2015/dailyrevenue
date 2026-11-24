<?php

namespace App\Filament\Resources\PlotApplications\Schemas;

use App\Models\Payment;
use App\Models\Plot;
use App\Models\PlotApplication;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PlotApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                  // -------------------------------------------------
                // Application Summary
                // -------------------------------------------------

                Section::make('আবেদনের সারসংক্ষেপ')
                    ->description('আবেদন নম্বর, তারিখ এবং বর্তমান অবস্থা')
                    ->icon('heroicon-o-document-text')
                    ->iconColor('primary')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([

                        TextEntry::make('application_no')
                            ->label('আবেদন নম্বর')
                            ->weight('bold')
                            ->copyable()
                            ->color('primary'),

                        TextEntry::make('applied_at')
                            ->label('আবেদনের তারিখ')
                            ->date('d F Y'),

                        TextEntry::make('status')
                            ->label('বর্তমান অবস্থা')
                            ->badge()
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'pending_payment' => 'পেমেন্ট অপেক্ষমাণ',
                                'payment_submitted' => 'পেমেন্ট যাচাই অপেক্ষমাণ',
                                'submitted' => 'আবেদন জমা হয়েছে',
                                'under_review' => 'যাচাই চলছে',
                                'approved' => 'অনুমোদিত',
                                'returned' => 'সংশোধনের জন্য ফেরত',
                                'rejected' => 'বাতিল',
                                'cancelled' => 'বাতিলকৃত',
                                'allocated' => 'প্লট বরাদ্দ হয়েছে',
                                default => $state,
                            })
                            ->color(fn ($state) => match ($state) {
                                'approved', 'allocated' => 'success',
                                'returned', 'pending_payment' => 'warning',
                                'rejected', 'cancelled' => 'danger',
                                'submitted', 'under_review',
                                'payment_submitted' => 'info',
                                default => 'gray',
                            }),

                    ]),

                // -------------------------------------------------
                // Applicant Information
                // -------------------------------------------------

                Section::make('আবেদনকারীর ব্যক্তিগত তথ্য')
                    ->description('আবেদনকারীর পরিচয়, ব্যক্তিগত ও যোগাযোগের তথ্য')
                    ->icon('heroicon-o-user')
                    ->iconColor('primary')
                    ->columnSpanFull()
                    ->schema([

                        Grid::make(4)
                            ->schema([

                                // বাম পাশে ছবি
                                ImageEntry::make('applicant.image')
                                    ->label('আবেদনকারীর ছবি')
                                    ->disk('public')
                                    ->imageHeight(190)
                                    ->columnSpan(1),

                                // ডান পাশে সব তথ্য
                                Grid::make(3)
                                    ->columnSpan(3)
                                    ->schema([

                                        TextEntry::make('applicant.applicant_no')
                                            ->label('আবেদনকারী নম্বর')
                                            ->weight('bold')
                                            ->copyable(),

                                        TextEntry::make('applicant.name')
                                            ->label('আবেদনকারীর নাম')
                                            ->weight('bold'),

                                        TextEntry::make('applicant.mobile')
                                            ->label('মোবাইল নম্বর')
                                            ->copyable(),

                                        TextEntry::make('applicant.father_name')
                                            ->label('পিতার নাম'),

                                        TextEntry::make('applicant.mother_name')
                                            ->label('মাতার নাম'),

                                        TextEntry::make('applicant.nid')
                                            ->label('জাতীয় পরিচয়পত্র নম্বর')
                                            ->copyable(),

                                        TextEntry::make('applicant.present_address')
                                            ->label('বর্তমান ঠিকানা')
                                            ,

                                        TextEntry::make('applicant.permanent_address')
                                            ->label('স্থায়ী ঠিকানা'),

                                    ]),

                            ]),

                    ]),

                // -------------------------------------------------
                // Applicant Documents
                // -------------------------------------------------

                Section::make('পরিচয়পত্রের ডকুমেন্ট')
                    ->description('আবেদনকারীর জমা দেওয়া জাতীয় পরিচয়পত্র')
                    ->icon('heroicon-o-identification')
                    ->iconColor('info')
                    ->columns(2)
                    ->columnSpanFull()
                    ->collapsible()
                    ->schema([

                        ImageEntry::make('applicant.nid_front')
                            ->label('NID-এর সামনের অংশ')
                            ->disk('public')
                            ->imageHeight(180),

                        ImageEntry::make('applicant.nid_back')
                            ->label('NID-এর পেছনের অংশ')
                            ->disk('public')
                            ->imageHeight(180),

                    ]),

                // -------------------------------------------------
                // Plot Application Information
                // -------------------------------------------------

                Section::make('প্লট আবেদনের তথ্য')
                    ->description('আবেদনকারীর নির্বাচিত প্লট ও আবেদন ফি')
                    ->icon('heroicon-o-map')
                    ->iconColor('success')
                    ->columns(4)
                    ->columnSpanFull()
                    ->schema([

                        Select::make('plot_id')
                            ->label('প্লট পরিবর্তন করুন')
                            ->placeholder('একটি খালি প্লট নির্বাচন করুন')
                            ->options(fn () => Plot::query()
                                ->where('status', 'available')
                                ->whereDoesntHave('activeAllocation')
                                ->orderBy('plot_number')
                                ->get()
                                ->mapWithKeys(fn (Plot $plot) => [
                                    $plot->id => $plot->plot_number
                                        . ' | ' . ($plot->location ?? 'অবস্থান উল্লেখ নেই')
                                        . ' | ' . ($plot->length ?? 0)
                                        . ' × ' . ($plot->width ?? 0),
                                ])
                                ->toArray()
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->visible(fn ($livewire): bool =>
                                $livewire instanceof EditRecord
                                && ! $livewire->getRecord()->allocation()->exists()
                            )
                            ->helperText('শুধুমাত্র খালি এবং বর্তমানে বরাদ্দ না থাকা প্লট নির্বাচন করা যাবে।'),

                        TextEntry::make('plot.plot_number')
                            ->label('বর্তমান প্লট নম্বর')
                            ->weight('bold')
                            ->color('primary'),

                        TextEntry::make('plot.location')
                            ->label('প্লটের অবস্থান'),

                        TextEntry::make('plot.size')
                            ->label('প্লটের আয়তন'),

                        TextEntry::make('application_fee_amount')
                            ->label('আবেদন ফি')
                            ->formatStateUsing(
                                fn ($state) => '৳ ' . number_format((float) $state, 2)
                            ),

                        TextEntry::make('application_fee_payment_status')
                            ->label('আবেদন ফি পরিশোধের অবস্থা')
                            ->state(fn (PlotApplication $record): string =>
                                Payment::query()
                                    ->where('application_id', $record->id)
                                    ->where('applicant_id', $record->applicant_id)
                                    ->where('payment_for', 'application_fee')
                                    ->where('status', 'success')
                                    ->where('amount', '>=', $record->application_fee_amount)
                                    ->exists()
                                        ? 'পরিশোধিত'
                                        : 'পরিশোধিত নয়'
                            )
                            ->badge()
                            ->color(fn ($state) =>
                                $state === 'পরিশোধিত' ? 'success' : 'danger'
                            ),

                        TextEntry::make('applicant_note')
                            ->label('আবেদনকারীর মন্তব্য')
                            ->placeholder('কোনো মন্তব্য নেই'),

                        TextEntry::make('admin_note')
                            ->label('সর্বশেষ Admin মন্তব্য')
                            ->placeholder('কোনো মন্তব্য নেই'),
                    ]),

                // -------------------------------------------------
                // System Information
                // -------------------------------------------------

                Section::make('সিস্টেম রেকর্ড')
                    ->description('আবেদন রেকর্ডের সময় ও সর্বশেষ পরিবর্তন')
                    ->icon('heroicon-o-clock')
                    ->iconColor('gray')
                    ->columns(2)
                    ->columnSpanFull()
                    ->collapsed()
                    ->schema([

                        TextEntry::make('created_at')
                            ->label('রেকর্ড তৈরির সময়')
                            ->dateTime('d F Y, h:i A'),

                        TextEntry::make('updated_at')
                            ->label('সর্বশেষ আপডেট')
                            ->dateTime('d F Y, h:i A'),

                    ]),

            ]);
    }
}
