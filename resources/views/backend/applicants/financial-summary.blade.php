
@php
    $formatMoney = fn ($amount) => '৳ ' . number_format((float) $amount, 2);

    $cardStyle = 'border:1px solid #e5e7eb; border-radius:10px; overflow:hidden; background:#ffffff; min-width:0;';
    $headingStyle = 'padding:15px 16px; background:#f3f4f6; color:#111827; font-size:15px; font-weight:700; border-bottom:1px solid #e5e7eb;';
    $tableStyle = 'width:100%; border-collapse:collapse; font-size:13px;';
    $cellStyle = 'padding:12px 14px; border-bottom:1px solid #e5e7eb; color:#4b5563;';
    $amountStyle = $cellStyle . 'text-align:right; font-weight:600; color:#111827; white-space:nowrap;';
@endphp

@if ($summary)

<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(min(100%, 280px), 1fr)); gap:18px; align-items:start;">

    {{-- দৈনিক ভাড়া --}}
    <div style="{{ $cardStyle }}">
        <div style="{{ $headingStyle }}">
            <span style="margin-right:7px;">📅</span>
            দৈনিক ভাড়ার হিসাব
        </div>

        <table style="{{ $tableStyle }}">
            <tbody>
                @foreach ([
                    'মোট ভাড়া' => $summary['daily_rent']['total_rent'],
                    'মওকুফ' => $summary['daily_rent']['waived_amount'],
                    'পরিশোধযোগ্য ভাড়া' => $summary['daily_rent']['payable_amount'],
                    'পরিশোধিত ভাড়া' => $summary['daily_rent']['paid_amount'],
                ] as $label => $amount)
                    <tr>
                        <td style="{{ $cellStyle }}">{{ $label }}</td>
                        <td style="{{ $amountStyle }}">{{ $formatMoney($amount) }}</td>
                    </tr>
                @endforeach

                <tr style="background:#fef2f2;">
                    <td style="{{ $cellStyle }} font-weight:700; color:#b91c1c;">
                        বর্তমান বকেয়া
                    </td>
                    <td style="{{ $amountStyle }} color:#b91c1c;">
                        {{ $formatMoney($summary['daily_rent']['due_amount']) }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- আবেদন ফি --}}
    <div style="{{ $cardStyle }}">
        <div style="{{ $headingStyle }}">
            <span style="margin-right:7px;">📄</span>
            আবেদন ফি
        </div>

        <table style="{{ $tableStyle }}">
            <tbody>
                <tr>
                    <td style="{{ $cellStyle }}">
                        মোট পরিশোধিত আবেদন ফি
                    </td>
                    <td style="{{ $amountStyle }} color:#15803d;">
                        {{ $formatMoney($summary['application']['application_fee_paid']) }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- জামানত --}}
    <div style="{{ $cardStyle }}">
        <div style="{{ $headingStyle }}">
            <span style="margin-right:7px;">💰</span>
            জামানতের হিসাব
        </div>

        <table style="{{ $tableStyle }}">
            <tbody>
                <!-- 'সমন্বয় ক্রেডিট' => $summary['security_deposit']['adjustment_credit'],
                    'সমন্বয় ডেবিট' => $summary['security_deposit']['adjustment_debit'], -->
                @foreach ([
                    'মোট জমাকৃত জামানত' => $summary['security_deposit']['total_deposit'],
                    'মোট কর্তন' => $summary['security_deposit']['total_deduction'],
                    'মোট ফেরত' => $summary['security_deposit']['total_refund'],
                    
                ] as $label => $amount)
                    <tr>
                        <td style="{{ $cellStyle }}">{{ $label }}</td>
                        <td style="{{ $amountStyle }}">{{ $formatMoney($amount) }}</td>
                    </tr>
                @endforeach

                <tr style="background:#f0fdf4;">
                    <td style="{{ $cellStyle }} font-weight:700; color:#15803d;">
                        বর্তমান জামানত স্থিতি
                    </td>
                    <td style="{{ $amountStyle }} color:#15803d;">
                        {{ $formatMoney($summary['security_deposit']['current_balance']) }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

@else
    <div style="padding:24px; text-align:center; border:1px solid #e5e7eb; border-radius:8px; color:#6b7280;">
        আর্থিক তথ্য পাওয়া যায়নি।
    </div>
@endif
