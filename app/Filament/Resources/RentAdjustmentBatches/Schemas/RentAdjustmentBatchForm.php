<?php

namespace App\Filament\Resources\RentAdjustmentBatches\Schemas;

use App\Models\Applicant;
use App\Models\DailyRentLedger;
use App\Services\RentAdjustmentService;
use Livewire\Component as LivewireComponent;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Throwable;

class RentAdjustmentBatchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                /*
                |--------------------------------------------------------------------------
                | ১. আবেদনকারী ও মওকুফের প্রাথমিক তথ্য
                |--------------------------------------------------------------------------
                */

                Section::make('মওকুফের প্রাথমিক তথ্য')
                    ->description(
                        'আবেদনকারী নির্বাচন করুন এবং মওকুফের কারণ ও পদ্ধতি নির্ধারণ করুন।'
                    )
                    ->icon('heroicon-o-user-circle')
                    ->columns(3)
                    ->schema([

                        Select::make('applicant_id')
                            ->label('আবেদনকারী নির্বাচন')
                            ->relationship(
                                name: 'applicant',
                                titleAttribute: 'applicant_no'
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn (Applicant $record): string =>
                                    "{$record->applicant_no} — {$record->name} ({$record->mobile})"
                            )
                            ->searchable(['applicant_no', 'name', 'mobile'])
                            ->required()
                            ->preload()
                            ->live()
                            ->placeholder(
                                'আবেদনকারী নম্বর, নাম অথবা মোবাইল দিয়ে খুঁজুন'
                            )
                            ->afterStateUpdated(
                                function (Set $set, $state): void {

                                    // আগের Applicant-এর Summary মুছে ফেলবে।
                                    $set('applicant_summary_cache', null);

                                    if (blank($state)) {
                                        return;
                                    }

                                    try {
                                        $summary = app(
                                            RentAdjustmentService::class
                                        )->getApplicantSummary((int) $state);

                                        $set(
                                            'applicant_summary_cache',
                                            json_encode(
                                                $summary,
                                                JSON_UNESCAPED_UNICODE
                                            )
                                        );
                                    } catch (Throwable $exception) {

                                        report($exception);

                                        $set(
                                            'applicant_summary_cache',
                                            json_encode([
                                                'status' => 'blocked',
                                                'message' =>
                                                    'আবেদনকারীর আর্থিক তথ্য সংগ্রহ করা সম্ভব হয়নি। '
                                                    . 'ত্রুটিটি পরীক্ষা করুন।',
                                            ], JSON_UNESCAPED_UNICODE)
                                        );
                                    }
                                }
                            )
                            ->columnSpanFull(),

                        // Summary-র ডেটা Form-এর সঙ্গে Submit হবে না।
                        Hidden::make('applicant_summary_cache')
                            ->dehydrated(false),

                        Placeholder::make('applicant_summary_display')
                            ->label('আবেদনকারীর আর্থিক সারসংক্ষেপ')
                            ->content(function (Get $get): HtmlString|string {

                                $cache = $get('applicant_summary_cache');

                                if (blank($cache)) {
                                    return new HtmlString(
                                        '<div class="rounded-xl border border-dashed border-gray-300 dark:border-gray-700 p-5 text-center text-sm text-gray-500">
                                            প্রথমে উপরের তালিকা থেকে একজন আবেদনকারী নির্বাচন করুন।
                                        </div>'
                                    );
                                }

                                $summary = json_decode($cache, true);

                                if (! is_array($summary)) {
                                    return new HtmlString(
                                        '<div class="rounded-xl border border-danger-300 bg-danger-50 dark:bg-danger-950 p-4 text-sm text-danger-700 dark:text-danger-300">
                                            আর্থিক সারসংক্ষেপ প্রদর্শন করা যাচ্ছে না।
                                        </div>'
                                    );
                                }

                                // Synchronization অথবা অন্য কোনো সমস্যা।
                                if (($summary['status'] ?? null) !== 'completed') {

                                    $message = e(
                                        $summary['message']
                                            ?? 'আর্থিক তথ্য প্রস্তুত করা যায়নি।'
                                    );

                                    return new HtmlString(
                                        '<div class="rounded-xl border border-danger-300 bg-danger-50 dark:bg-danger-950 p-4">
                                            <div class="flex items-center gap-2 font-semibold text-danger-700 dark:text-danger-300">
                                                <span>⚠</span>
                                                <span>সারসংক্ষেপ প্রস্তুত করা সম্ভব হয়নি</span>
                                            </div>
                                            <p class="mt-2 text-sm text-danger-700 dark:text-danger-300">'
                                                . $message .
                                            '</p>
                                        </div>'
                                    );
                                }

                                $applicant = $summary['applicant'] ?? [];

                                $name = e($applicant['name'] ?? '—');
                                $applicantNo = e($applicant['applicant_no'] ?? '—');
                                $mobile = e($applicant['mobile'] ?? '—');

                                $plots = $summary['plots'] ?? [];

                                $plotText = ! empty($plots)
                                    ? e(implode(', ', $plots))
                                    : '—';

                                $totalDue = number_format(
                                    (float) ($summary['total_due'] ?? 0),
                                    2
                                );

                                $outstandingDays = (int) (
                                    $summary['outstanding_days'] ?? 0
                                );

                                $firstDate = $summary['first_due_date'] ?? null;
                                $lastDate = $summary['last_due_date'] ?? null;

                                $firstDate = $firstDate
                                    ? e(
                                        \Carbon\Carbon::parse($firstDate)
                                            ->format('d/m/Y')
                                    )
                                    : '—';

                                $lastDate = $lastDate
                                    ? e(
                                        \Carbon\Carbon::parse($lastDate)
                                            ->format('d/m/Y')
                                    )
                                    : '—';

                                $pendingPayment = (bool) (
                                    $summary['has_pending_payment'] ?? false
                                );

                                
                            $html = '
                            <div style="width:100%; margin-top:12px;">

                                <table style="
                                    width:100%;
                                    margin-top:14px;
                                    border-collapse:separate;
                                    border-spacing:8px 0;
                                    table-layout:fixed;
                                ">
                                    <tbody>
                                        <tr>

                                            <td style="
                                                width:50%;
                                                padding:16px;
                                                border:1px solid #07a55a;
                                                border-radius:9px;
                                                background-color:#ebfffa;
                                                vertical-align:middle;
                                            ">
                                                <div style="font-size:16px; color:#285e07;">
                                                    মোট বকেয়া টাকা
                                                </div>

                                                <div style="
                                                    margin-top:7px;
                                                    font-size:24px;
                                                    font-weight:700;
                                                    color:#285e07;
                                                ">
                                                    ' . $firstDate . '
                                                </div>
                                            </td>

                                            <td style="
                                                width:50%;
                                                padding:16px;
                                                border:1px solid #aec90c;
                                                border-radius:9px;
                                                background-color:#e3edde;
                                                vertical-align:middle;
                                            ">
                                                <div style="font-size:16px; color:#475204;">
                                                    মোট বকেয়া দিনের সংখ্যা
                                                </div>

                                                <div style="
                                                    margin-top:7px;
                                                    font-size:24px;
                                                    font-weight:700;
                                                    color:#475204;
                                                ">
                                                    ' . $lastDate . ' 
                                                </div>
                                            </td>

                                            <td style="
                                                width:50%;
                                                padding:16px;
                                                border:1px solid #f59e0b;
                                                border-radius:9px;
                                                background-color:#fffbeb;
                                                vertical-align:middle;
                                            ">
                                                <div style="font-size:13px; color:#92400e;">
                                                    মোট বকেয়া টাকা
                                                </div>

                                                <div style="
                                                    margin-top:7px;
                                                    font-size:24px;
                                                    font-weight:700;
                                                    color:#b45309;
                                                ">
                                                    ৳' . $totalDue . '
                                                </div>
                                            </td>

                                            <td style="
                                                width:50%;
                                                padding:16px;
                                                border:1px solid #93c5fd;
                                                border-radius:9px;
                                                background-color:#eff6ff;
                                                vertical-align:middle;
                                            ">
                                                <div style="font-size:13px; color:#1e40af;">
                                                    মোট বকেয়া দিনের সংখ্যা
                                                </div>

                                                <div style="
                                                    margin-top:7px;
                                                    font-size:24px;
                                                    font-weight:700;
                                                    color:#1d4ed8;
                                                ">
                                                    ' . $outstandingDays . ' দিন
                                                </div>
                                            </td>

                                        </tr>
                                    </tbody>
                                </table>

                            </div>
                            ';


                                if ($outstandingDays === 0) {
                                    $html .= '
                                        <div class="rounded-xl border border-success-300 bg-success-50 dark:bg-success-950/30 p-4 text-sm text-success-700 dark:text-success-300">
                                            এই আবেদনকারীর বর্তমানে কোনো বকেয়া ভাড়া নেই। মওকুফ করার মতো বকেয়া পাওয়া যায়নি।
                                        </div>
                                    ';
                                }

                                if ($pendingPayment) {
                                    $html .= '
                                        <div class="rounded-xl border border-warning-300 bg-warning-50 dark:bg-warning-950/30 p-4 text-sm text-warning-700 dark:text-warning-300">
                                            ⚠ এই আবেদনকারীর একটি বা একাধিক Daily Rent Payment প্রক্রিয়াধীন রয়েছে। Payment নিষ্পত্তি না হওয়া পর্যন্ত মওকুফ কার্যক্রম করা উচিত নয়।
                                        </div>
                                    ';
                                }

                                return new HtmlString($html);
                            })
                            ->columnSpanFull(),

                        Select::make('adjustment_type')
                            ->label('মওকুফের কারণ')
                            ->options([
                                'illness' => 'অসুস্থতা',
                                'absence' => 'অনুপস্থিতি',
                                'rain' => 'বৃষ্টি',
                                'other' => 'অন্যান্য',
                            ])
                            ->placeholder('মওকুফের কারণ নির্বাচন করুন')
                            ->required(),

                        Select::make('adjustment_basis')
                            ->label('মওকুফের পদ্ধতি')
                            ->options([
                                'days' => 'নির্দিষ্ট দিনের সংখ্যা',
                                'dates' => 'নির্দিষ্ট তারিখ',
                                'amount' => 'নির্দিষ্ট টাকার পরিমাণ',
                            ])
                            ->placeholder('মওকুফের পদ্ধতি নির্বাচন করুন')
                            ->required()
                            ->default('amount')
                            ->live()
                            ->afterStateUpdated(function (Set $set): void {
                                $set('adjustment_days', null);
                                $set('dates', null);
                                $set('requested_amount', null);
                            }),

                            Select::make('adjustment_days')
                            ->label('মওকুফের দিনের সংখ্যা')
                            ->options(function (Get $get): array {

                                $summary = self::getCachedSummary($get);

                                $totalDays = (int) (
                                    $summary['outstanding_days'] ?? 0
                                );

                                $options = [];

                                for ($i = 1; $i <= $totalDays; $i++) {
                                    $options[$i] = self::toBanglaNumber($i) . ' দিন';
                                }

                                return $options;
                            })
                            ->required(
                                fn (Get $get): bool =>
                                    $get('adjustment_basis') === 'days'
                            )
                            ->visible(
                                fn (Get $get): bool =>
                                    $get('adjustment_basis') === 'days'
                            )
                            ->placeholder('দিনের সংখ্যা নির্বাচন করুন')
                            ->native(false)
                            ->helperText(
                                'সর্বোচ্চ বকেয়া দিনের সংখ্যা পর্যন্ত নির্বাচন করা যাবে। সিস্টেম FIFO নিয়মে সবচেয়ে পুরোনো বকেয়া দিন থেকে মওকুফ করবে।'
                            ),

                        /*
                        |--------------------------------------------------------------------------
                        | নির্দিষ্ট তারিখ — Multiple Select
                        |--------------------------------------------------------------------------
                        */

                        Select::make('dates')
                            ->label('মওকুফের তারিখসমূহ')
                            ->multiple()
                            ->options(
                                fn (Get $get): array =>
                                    self::getOutstandingDateOptions($get)
                            )
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->required(
                                fn (Get $get): bool =>
                                    $get('adjustment_basis') === 'dates'
                            )
                            ->visible(
                                fn (Get $get): bool =>
                                    $get('adjustment_basis') === 'dates'
                            )
                            ->placeholder('এক বা একাধিক বকেয়া তারিখ নির্বাচন করুন')
                            ->helperText(
                                'শুধুমাত্র আবেদনকারীর বকেয়া থাকা তারিখগুলো দেখানো হবে। একাধিক তারিখ নির্বাচন করা যাবে।'
                            ),

                        /*
                        |--------------------------------------------------------------------------
                        | নির্দিষ্ট টাকার পরিমাণ
                        |--------------------------------------------------------------------------
                        */

                        TextInput::make('requested_amount')
                            ->label('মওকুফের মোট টাকার পরিমাণ')
                            ->numeric()
                            ->minValue(0.01)
                            ->step(0.01)
                            ->prefix('৳')
                            ->live(debounce: 500)
                            ->afterStateUpdated(
                                function (
                                    $state,
                                    Get $get,
                                    LivewireComponent $livewire,
                                    TextInput $component
                                ): void {

                                    $statePath = $component->getStatePath();

                                    $totalDue = (float) (
                                        self::getCachedSummary($get)['total_due'] ?? 0
                                    );

                                    $enteredAmount = is_numeric($state)
                                        ? (float) $state
                                        : 0;

                                    if ($enteredAmount > $totalDue) {

                                        $livewire->addError(
                                            $statePath,
                                            'মওকুফের পরিমাণ আবেদনকারীর মোট বকেয়া টাকা ৳'
                                            . number_format($totalDue, 2)
                                            . '-এর বেশি হতে পারবে না।'
                                        );

                                        return;
                                    }

                                    // Amount সঠিক হলে আগের Error সরিয়ে দেবে।
                                    $livewire->resetValidation($statePath);
                                }
                            )
                            ->required(
                                fn (Get $get): bool =>
                                    $get('adjustment_basis') === 'amount'
                            )
                            ->visible(
                                fn (Get $get): bool =>
                                    $get('adjustment_basis') === 'amount'
                            )
                            ->placeholder('যেমন: ২০০.০০')
                            ->helperText(
                                fn (Get $get): string =>
                                    'সর্বোচ্চ মওকুফযোগ্য টাকা: ৳'
                                    . number_format(
                                        (float) (
                                            self::getCachedSummary($get)['total_due'] ?? 0
                                        ),
                                        2
                                    )
                            ),

                    ])
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | ৩. কারণ ও প্রমাণপত্র
                |--------------------------------------------------------------------------
                */

                Section::make('কারণ ও প্রমাণপত্র')
                    ->description(
                        'মওকুফের যৌক্তিক কারণ লিখুন এবং প্রয়োজনে প্রমাণপত্র সংযুক্ত করুন।'
                    )
                    ->columnSpanFull()
                    ->icon('heroicon-o-document-text')
                    ->columns(3)
                    ->schema([

                        Textarea::make('reason')
                            ->label('মওকুফের বিস্তারিত কারণ')
                            ->placeholder(
                                'কেন এই আবেদনকারীর ভাড়া মওকুফ করা হচ্ছে তা বিস্তারিত লিখুন...'
                            )
                            ->required()
                            ->rows(3)
                            ->maxLength(5000),

                        FileUpload::make('attachment_path')
                            ->label('প্রমাণপত্র / সংযুক্তি')
                            ->disk('public')
                            ->directory('rent-adjustments')
                            ->visibility('public')
                            ->downloadable()
                            ->openable()
                            ->maxSize(5120)
                            ->helperText(
                                'প্রয়োজনীয় ছবি বা ডকুমেন্ট সংযুক্ত করুন। সর্বোচ্চ ফাইল সাইজ ৫ MB।'
                            ),
                        Textarea::make('approval_remarks')
                            ->label('অনুমোদন সংক্রান্ত মন্তব্য')
                            ->placeholder(
                                'প্রয়োজনে অনুমোদন বা প্রশাসনিক সিদ্ধান্তের বিস্তারিত লিখুন...'
                            )
                            ->rows(3)
                            ->maxLength(5000),

                    ]),

            ]);
    }

    /**
     * Hidden cache থেকে Applicant-এর Financial Summary সংগ্রহ।
     */
    private static function getCachedSummary(Get $get): array
    {
        $cache = $get('applicant_summary_cache');

        if (blank($cache)) {
            return [];
        }

        $summary = json_decode($cache, true);

        return is_array($summary) ? $summary : [];
    }


    /**
     * Applicant-এর সব Outstanding Ledger Date-এর Dropdown Options।
     */
    private static function getOutstandingDateOptions(Get $get): array
    {
        $summary = self::getCachedSummary($get);

        $ledgerIds = $summary['outstanding_ledger_ids'] ?? [];

        if (empty($ledgerIds)) {
            return [];
        }

        $dates = DailyRentLedger::query()
            ->whereIn('id', $ledgerIds)
            ->orderBy('rent_date')
            ->get(['rent_date']);

        return $dates
            ->mapWithKeys(function ($ledger): array {

                $date = Carbon::parse($ledger->rent_date)
                    ->toDateString();

                $label = Carbon::parse($date)
                    ->format('d/m/Y');

                return [$date => $label];
            })
            ->unique()
            ->all();
    }


    /**
     * ইংরেজি সংখ্যাকে বাংলা সংখ্যায় রূপান্তর।
     */
    private static function toBanglaNumber(int $number): string
    {
        return strtr(
            (string) $number,
            [
                '0' => '০',
                '1' => '১',
                '2' => '২',
                '3' => '৩',
                '4' => '৪',
                '5' => '৫',
                '6' => '৬',
                '7' => '৭',
                '8' => '৮',
                '9' => '৯',
            ]
        );
    }
}
