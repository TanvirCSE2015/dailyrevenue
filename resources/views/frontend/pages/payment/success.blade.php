
@extends('frontend.layouts.app')

@section('title', 'পেমেন্ট সফল | প্লট ব্যবস্থাপনা সিস্টেম')

@section('content')

@php
    $isDailyRent = $payment->payment_for === 'daily_rent';

    $isApplicationFee = $payment->payment_for === 'application_fee';

    $isSecurityDeposit = $payment->payment_for === 'security_deposit';

    $applicant = $application?->applicant ?? $allocation?->applicant ?? null;

    $plot = $application?->plot ?? $allocation?->plot ?? null;

    $paymentDate = $payment->paid_at
        ?? $payment->verified_at
        ?? $payment->created_at;

    $rentAllocations = collect();

    if ($isDailyRent) {
        $rentAllocations = $payment->paymentAllocations
            ->filter(function ($item) {
                return $item->payable instanceof \App\Models\DailyRentLedger;
            })
            ->sortBy(function ($item) {
                return $item->payable->rent_date;
            })
            ->values();
    }
@endphp

<div class="container py-5">

    {{-- Payment Success Message --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body text-center py-5">

            <div class="text-success mb-3">
                <i class="bi bi-check-circle-fill" style="font-size: 70px;"></i>
            </div>

            <h2 class="text-success fw-bold">
                পেমেন্ট সফল হয়েছে!
            </h2>

            @if ($payment->reconciliation_status === 'completed')

                @if ($isApplicationFee)

                    <p class="text-muted mb-2">
                        আপনার আবেদন ফি সফলভাবে গ্রহণ করা হয়েছে।
                    </p>

                    <span class="badge bg-success px-3 py-2">
                        আবেদন জমা হয়েছে
                    </span>

                @elseif ($isSecurityDeposit)

                    <p class="text-muted mb-2">
                        আপনার সিকিউরিটি ডিপোজিট সফলভাবে গ্রহণ করা হয়েছে।
                    </p>

                    <span class="badge bg-success px-3 py-2">
                        প্লট বরাদ্দ সম্পন্ন হয়েছে
                    </span>

                @elseif ($isDailyRent)

                    <p class="text-muted mb-2">
                        আপনার দৈনিক ভাড়া সফলভাবে গ্রহণ ও হিসাবভুক্ত করা হয়েছে।
                    </p>

                    <span class="badge bg-success px-3 py-2">
                        দৈনিক ভাড়া পরিশোধ সম্পন্ন
                    </span>

                @endif

            @elseif ($payment->reconciliation_status === 'failed')

                <p class="text-muted mb-2">
                    আপনার পেমেন্ট Gateway থেকে সফলভাবে যাচাই করা হয়েছে।
                    তবে সফটওয়্যারে হিসাব সমন্বয় সম্পন্ন হয়নি।
                </p>

                <span class="badge bg-warning text-dark px-3 py-2">
                    হিসাব সমন্বয়ের জন্য অফিসে যোগাযোগ করুন
                </span>

            @else

                <p class="text-muted mb-2">
                    আপনার পেমেন্ট সফলভাবে গ্রহণ করা হয়েছে।
                    হিসাব সমন্বয়ের কাজ চলছে।
                </p>

                <span class="badge bg-warning text-dark px-3 py-2">
                    হিসাব সমন্বয় প্রক্রিয়াধীন
                </span>

            @endif

        </div>
    </div>


    {{-- Common Payment Information --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-receipt me-2"></i>
                পেমেন্টের তথ্য
            </h5>
        </div>

        <div class="card-body">

            <div class="row g-3">

                <div class="col-md-4">
                    <small class="text-muted">পেমেন্ট নম্বর</small>
                    <div class="fw-bold">
                        {{ $payment->payment_no }}
                    </div>
                </div>

                <div class="col-md-4">
                    <small class="text-muted">পেমেন্টের ধরন</small>
                    <div class="fw-bold">
                        @if($isApplicationFee)
                            আবেদন ফি
                        @elseif($isSecurityDeposit)
                            সিকিউরিটি ডিপোজিট
                        @elseif($isDailyRent)
                            দৈনিক ভাড়া
                        @else
                            অন্যান্য
                        @endif
                    </div>
                </div>

                <div class="col-md-4">
                    <small class="text-muted">পেমেন্টের তারিখ</small>
                    <div class="fw-bold">
                        {{ $paymentDate?->format('d-m-Y h:i A') ?? '—' }}
                    </div>
                </div>

                <div class="col-md-4">
                    <small class="text-muted">পরিশোধিত টাকা</small>
                    <div class="fw-bold text-success">
                        ৳{{ number_format((float) $payment->amount, 2) }}
                    </div>
                </div>

                <div class="col-md-4">
                    <small class="text-muted">পেমেন্ট মাধ্যম</small>
                    <div class="fw-bold">
                        JanataPay
                    </div>
                </div>

                <div class="col-md-4">
                    <small class="text-muted">পেমেন্ট স্ট্যাটাস</small>
                    <div>
                        <span class="badge bg-success">
                            সফল ও যাচাইকৃত
                        </span>
                    </div>
                </div>

                {{-- Payment Reconciliation Status --}}
                <div class="col-md-12">
                    <small class="text-muted">হিসাব সমন্বয়ের অবস্থা</small>
                    <div class="mt-1">

                        @if ($payment->reconciliation_status === 'completed')

                            <span class="badge bg-success">
                                হিসাব সমন্বয় সম্পন্ন
                            </span>

                        @elseif ($payment->reconciliation_status === 'pending')

                            <span class="badge bg-warning text-dark">
                                হিসাব সমন্বয়ের অপেক্ষায়
                            </span>

                        @elseif ($payment->reconciliation_status === 'failed')

                            <span class="badge bg-danger">
                                অফিসে যোগাযোগ করুন
                            </span>

                        @else

                            <span class="badge bg-secondary">
                                যাচাই করা হচ্ছে
                            </span>

                        @endif

                    </div>
                </div>

                @if ($payment->reconciliation_status === 'failed')

                    <div class="col-md-12">
                        <div class="alert alert-warning mb-0">
                            আপনার পেমেন্ট সফলভাবে গ্রহণ করা হয়েছে।
                            তবে হিসাব সমন্বয়ে সমস্যা হয়েছে।
                            অনুগ্রহ করে পেমেন্টটি পুনরায় করবেন না।
                            অফিসে যোগাযোগ করুন।
                        </div>
                    </div>

                @elseif ($payment->reconciliation_status === 'pending')

                    <div class="col-md-12">
                        <div class="alert alert-info mb-0">
                            আপনার পেমেন্ট সফলভাবে গ্রহণ করা হয়েছে।
                            হিসাব সমন্বয়ের কাজ প্রক্রিয়াধীন রয়েছে।
                            অনুগ্রহ করে পুনরায় পেমেন্ট করবেন না।
                        </div>
                    </div>

                @endif

                <div class="col-md-6">
                    <small class="text-muted">JanataPay Transaction ID</small>
                    <div class="fw-bold">
                        {{ $payment->gateway_transaction_id ?? '—' }}
                    </div>
                </div>

                <div class="col-md-6">
                    <small class="text-muted">Merchant Invoice ID</small>
                    <div class="fw-bold">
                        {{ $payment->merchant_invoice_id }}
                    </div>
                </div>

            </div>

        </div>
    </div>


    {{-- Application Information --}}
    @if(!$isDailyRent && $application)

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-file-earmark-text me-2"></i>
                    আবেদনের তথ্য
                </h5>
            </div>

            <div class="card-body">

                <div class="row g-3">

                    <div class="col-md-4">
                        <small class="text-muted">আবেদন নম্বর</small>
                        <div class="fw-bold">
                            {{ $application->application_no }}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <small class="text-muted">আবেদনকারীর নম্বর</small>
                        <div class="fw-bold">
                            {{ $applicant?->applicant_no ?? '—' }}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <small class="text-muted">আবেদনের তারিখ</small>
                        <div class="fw-bold">
                            {{ $application->applied_at?->format('d-m-Y') ?? '—' }}
                        </div>
                    </div>

                </div>

            </div>
        </div>

    @endif


    {{-- Applicant Information --}}
    @if($applicant)

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-person-vcard me-2"></i>
                    আবেদনকারীর তথ্য
                </h5>
            </div>

            <div class="card-body">

                <div class="row g-3">

                    <div class="col-md-3">
                        <small class="text-muted">আবেদনকারীর নম্বর</small>
                        <div class="fw-bold">
                            {{ $applicant->applicant_no ?? '—' }}
                        </div>
                    </div>

                    <div class="col-md-3">
                        <small class="text-muted">আবেদনকারীর নাম</small>
                        <div class="fw-bold">
                            {{ $applicant->name ?? '—' }}
                        </div>
                    </div>

                    <div class="col-md-3">
                        <small class="text-muted">পিতার নাম</small>
                        <div class="fw-bold">
                            {{ $applicant->father_name ?? '—' }}
                        </div>
                    </div>

                    <div class="col-md-3">
                        <small class="text-muted">মাতার নাম</small>
                        <div class="fw-bold">
                            {{ $applicant->mother_name ?? '—' }}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <small class="text-muted">মোবাইল নম্বর</small>
                        <div class="fw-bold">
                            {{ $applicant->mobile ?? '—' }}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <small class="text-muted">NID নম্বর</small>
                        <div class="fw-bold">
                            {{ $applicant->nid ?? '—' }}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <small class="text-muted">বর্তমান ঠিকানা</small>
                        <div class="fw-bold">
                            {{ $applicant->present_address ?? '—' }}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <small class="text-muted">স্থায়ী ঠিকানা</small>
                        <div class="fw-bold">
                            {{ $applicant->permanent_address ?? '—' }}
                        </div>
                    </div>

                </div>

            </div>
        </div>

    @endif


    {{-- Plot Information --}}
    @if($plot)

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-geo-alt me-2"></i>
                    প্লটের তথ্য
                </h5>
            </div>

            <div class="card-body">

                <div class="row g-3">

                    <div class="col-md-4">
                        <small class="text-muted">প্লট নম্বর</small>
                        <div class="fw-bold">
                            {{ $plot->plot_number }}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <small class="text-muted">অবস্থান</small>
                        <div class="fw-bold">
                            {{ $plot->location }}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <small class="text-muted">প্লটের আয়তন</small>
                        <div class="fw-bold">
                            {{ $plot->length }} × {{ $plot->width }}
                        </div>
                    </div>

                </div>

            </div>
        </div>

    @endif


    {{-- Daily Rent Payment Details --}}
    @if($isDailyRent)

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-calendar-check me-2"></i>
                    পরিশোধিত দৈনিক ভাড়ার বিবরণ
                </h5>
            </div>

            <div class="card-body">

                <div class="row g-3 mb-4">

                    <div class="col-md-4">
                        <small class="text-muted">পরিশোধিত দিনের সংখ্যা</small>
                        <div class="fw-bold">
                            {{ $rentAllocations->count() }} দিন
                        </div>
                    </div>

                    <div class="col-md-4">
                        <small class="text-muted">প্লট নম্বর</small>
                        <div class="fw-bold">
                            {{ $plot?->plot_number ?? '—' }}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <small class="text-muted">মোট পরিশোধিত টাকা</small>
                        <div class="fw-bold text-success">
                            ৳{{ number_format((float) $payment->amount, 2) }}
                        </div>
                    </div>

                </div>

                <div class="table-responsive">

                    <table class="table table-bordered table-striped align-middle">

                        <thead class="table-light">
                            <tr>
                                <th style="width: 70px;">ক্রমিক</th>
                                <th>ভাড়ার তারিখ</th>
                                <th class="text-end">এই দিনের পরিশোধিত টাকা</th>
                                <th>অবস্থা</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse($rentAllocations as $index => $item)

                                <tr>
                                    <td>{{ $index + 1 }}</td>

                                    <td>
                                        {{ $item->payable->rent_date?->format('d-m-Y') ?? '—' }}
                                    </td>

                                    <td class="text-end fw-bold">
                                        ৳{{ number_format((float) $item->amount, 2) }}
                                    </td>

                                    <td>
                                        <span class="badge bg-success">
                                            পরিশোধিত
                                        </span>
                                    </td>
                                </tr>

                            @empty

                                <tr>
                                    <td colspan="4" class="text-center text-muted">
                                        পরিশোধিত দিনের বিস্তারিত পাওয়া যায়নি।
                                    </td>
                                </tr>

                            @endforelse

                        </tbody>

                        @if($rentAllocations->isNotEmpty())
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="2" class="text-end">
                                        সর্বমোট
                                    </th>
                                    <th class="text-end">
                                        ৳{{ number_format((float) $payment->amount, 2) }}
                                    </th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        @endif

                    </table>

                </div>

                <small class="text-muted">
                    এই তালিকায় শুধু এই পেমেন্টের মাধ্যমে পরিশোধিত দিনের হিসাব দেখানো হয়েছে।
                </small>

            </div>
        </div>

    @endif


    {{-- Action Buttons --}}
    <div class="d-flex flex-wrap justify-content-center gap-2 mb-4">

        <button
            type="button"
            onclick="window.print()"
            class="btn btn-primary px-4"
        >
            <i class="bi bi-printer me-2"></i>
            পেমেন্ট রসিদ প্রিন্ট
        </button>

        <a
            href="{{ route('home') }}"
            class="btn btn-outline-secondary px-4"
        >
            <i class="bi bi-house me-2"></i>
            হোম পেজ
        </a>

    </div>

</div>

@endsection


@push('styles')
<style>
    @media print {

        header,
        footer,
        nav,
        .btn,
        .navbar {
            display: none !important;
        }

        body {
            background: #fff !important;
        }

        .container {
            max-width: 100% !important;
            width: 100% !important;
        }

        .card {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
            break-inside: avoid;
        }

        .card-header {
            background: #f8f9fa !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .table {
            font-size: 12px;
        }
    }
</style>
@endpush






<!-- <div class="container py-5">
    <div class="text-center">
        <div class="text-success mb-3">
            <i class="bi bi-check-circle-fill" style="font-size: 70px;"></i>
        </div>

        <h2 class="text-success">পেমেন্ট সফল হয়েছে!</h2>

        <p class="text-muted">
            আপনার পেমেন্ট গ্রহণ করা হয়েছে।
            যাচাই সম্পন্ন হলে আবেদনটি নিশ্চিত করা হবে।
        </p>

        <a href="{{ route('home') }}" class="btn btn-primary mt-3">
            হোম পেজে ফিরে যান
        </a>
    </div>
</div> -->

