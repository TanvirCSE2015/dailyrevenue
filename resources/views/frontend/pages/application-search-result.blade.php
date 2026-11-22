
@extends('frontend.layouts.app')

@section('title', 'আবেদন অনুসন্ধান | প্লট ব্যবস্থাপনা সিস্টেম')

@section('content')

<section class="application-result-section py-5">
    <div class="container">

        {{-- Page Heading --}}
        <div class="text-center mb-4">
            <div class="application-page-icon">
                <i class="bi bi-file-earmark-text"></i>
            </div>

            <h2 class="application-page-title mt-3">
                আবেদন অনুসন্ধান
            </h2>

            <p class="text-secondary">
                আবেদন নম্বর দিয়ে আবেদনের বর্তমান অবস্থা ও বিস্তারিত তথ্য দেখুন।
            </p>
        </div>

        {{-- Search Again --}}
        <div class="application-search-card mb-4">
            <form action="{{ route('frontend.applications.search') }}"
                  method="GET">

                <div class="application-search-input">
                    <i class="bi bi-search"></i>

                    <input
                        type="text"
                        name="application_no"
                        value="{{ $applicationNo ?? request('application_no') }}"
                        placeholder="আপনার আবেদন নম্বর লিখুন"
                        required
                    >

                    <button type="submit">
                        অনুসন্ধান
                        <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                </div>
            </form>

            @error('application_no')
                <div class="text-danger small mt-2">
                    {{ $message }}
                </div>
            @enderror
        </div>

        @if($application)

            @php
                $statusLabels = [
                    'pending_payment' => 'আবেদন ফি পরিশোধ অপেক্ষমাণ',
                    'payment_submitted' => 'পেমেন্ট যাচাই অপেক্ষমাণ',
                    'submitted' => 'আবেদন জমা হয়েছে',
                    'under_review' => 'আবেদন পর্যালোচনাধীন',
                    'approved' => 'আবেদন অনুমোদিত',
                    'rejected' => 'আবেদন বাতিল',
                    'returned' => 'আবেদন সংশোধনের জন্য ফেরত',
                    'cancelled' => 'আবেদন বাতিল করা হয়েছে',
                    'allocated' => 'প্লট বরাদ্দ সম্পন্ন',
                ];

                $status = $application->status;

                $statusClass = match($status) {
                    'approved', 'allocated' => 'status-success',
                    'rejected', 'cancelled' => 'status-danger',
                    'returned' => 'status-warning',
                    default => 'status-pending',
                };
            @endphp

            <div class="application-result-card">

                {{-- Header --}}
                <div class="application-result-header">
                    <div>
                        <span class="result-found-label">
                            <i class="bi bi-check-circle-fill me-1"></i>
                            আবেদন পাওয়া গেছে
                        </span>

                        <h4 class="mt-2 mb-0">
                            আবেদনের বিস্তারিত তথ্য
                        </h4>
                    </div>

                    <div class="application-header-icon">
                        <i class="bi bi-file-earmark-check"></i>
                    </div>
                </div>

                <div class="application-result-body">
                    @if($errors->has('payment'))
                        <div class="alert alert-danger mb-4" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            {{ $errors->first('payment') }}
                        </div>
                    @endif
                    {{-- Application Status --}}
                    <div class="application-status-box">
                        <div>
                            <span>আবেদনের বর্তমান অবস্থা</span>
                            <strong class="{{ $statusClass }}">
                                {{ $statusLabels[$status] ?? $status }}
                            </strong>
                        </div>

                        <i class="bi bi-info-circle"></i>
                    </div>

                    {{-- Application Information --}}
                    <h5 class="result-section-title">
                        <i class="bi bi-file-earmark-text me-2"></i>
                        আবেদন সংক্রান্ত তথ্য
                    </h5>

                    <div class="row g-3 mb-4">

                        <div class="col-md-6">
                            <div class="info-item">
                                <span>আবেদন নম্বর</span>
                                <strong>{{ $application->application_no }}</strong>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-item">
                                <span>আবেদনের তারিখ</span>
                                <strong>
                                    {{ $application->applied_at
                                        ? \Illuminate\Support\Carbon::parse($application->applied_at)->format('d-m-Y')
                                        : 'তথ্য নেই'
                                    }}
                                </strong>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-item">
                                <span>আবেদন ফি</span>
                                <strong>
                                    ৳ {{ number_format((float) $application->application_fee_amount, 2) }}
                                </strong>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-item">
                                <span>প্লট নম্বর</span>
                                <strong>
                                    {{ $application->plot?->plot_number ?? 'তথ্য নেই' }}
                                </strong>
                            </div>
                        </div>

                    </div>

                    {{-- Applicant Information --}}
                    <h5 class="result-section-title">
                        <i class="bi bi-person-vcard me-2"></i>
                        আবেদনকারীর তথ্য
                    </h5>

                    <div class="row g-3 mb-4">

                        <div class="col-md-6">
                            <div class="info-item">
                                <span>আবেদনকারী নম্বর</span>
                                <strong>
                                    {{ $application->applicant?->applicant_no ?? 'তথ্য নেই' }}
                                </strong>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-item">
                                <span>আবেদনকারীর নাম</span>
                                <strong>
                                    {{ $application->applicant?->name ?? 'তথ্য নেই' }}
                                </strong>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-item">
                                <span>মোবাইল নম্বর</span>
                                <strong>
                                    @if($application->applicant?->mobile)
                                        {{ '********' . substr($application->applicant->mobile, -3) }}
                                    @else
                                        তথ্য নেই
                                    @endif
                                </strong>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-item">
                                <span>জাতীয় পরিচয়পত্র</span>
                                <strong>
                                    @if($application->applicant?->nid)
                                        {{ '********' . substr($application->applicant->nid, -4) }}
                                    @else
                                        তথ্য নেই
                                    @endif
                                </strong>
                            </div>
                        </div>

                    </div>

                    {{-- Security Deposit --}}
                    @if($status === 'approved')

                        <div class="deposit-section">

                            <div class="deposit-heading">
                                <div class="deposit-icon">
                                    <i class="bi bi-shield-check"></i>
                                </div>

                                <div>
                                    <h5>সিকিউরিটি ডিপোজিট</h5>
                                    <p>
                                        আবেদন অনুমোদিত হয়েছে। প্লট বরাদ্দের
                                        পরবর্তী ধাপ সম্পন্ন করতে সিকিউরিটি
                                        ডিপোজিট পরিশোধ করুন।
                                    </p>
                                </div>
                            </div>

                            <div class="deposit-amount">
                                <span>নির্ধারিত সিকিউরিটি ডিপোজিট</span>
                                <strong>৳ ১,০০০.০০</strong>
                            </div>

                            @if($depositPaid)

                                <div class="deposit-paid-message">
                                    <i class="bi bi-check-circle-fill"></i>

                                    <div>
                                        <strong>সিকিউরিটি ডিপোজিট পরিশোধিত</strong>
                                        <p class="mb-0">
                                            আপনার সিকিউরিটি ডিপোজিট সফলভাবে
                                            পরিশোধিত হয়েছে।
                                        </p>
                                    </div>
                                </div>

                            @else

                                <div class="deposit-payment-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    পেমেন্ট সম্পন্ন করতে জনতা পে (JanataPay)
                                    ব্যবহার করুন।
                                </div>

                                <form
                                    action="{{ route('frontend.applications.security-deposit', $application) }}"
                                    method="POST"
                                >
                                    @csrf

                                    <button type="submit"
                                            class="deposit-pay-button">
                                        <i class="bi bi-credit-card me-2"></i>
                                        সিকিউরিটি ডিপোজিট পরিশোধ করুন
                                        <i class="bi bi-arrow-right ms-2"></i>
                                    </button>
                                </form>

                            @endif

                        </div>

                    @elseif($status === 'rejected' || $status === 'cancelled')

                        <div class="application-notice notice-danger">
                            <i class="bi bi-exclamation-circle-fill"></i>
                            <div>
                                <strong>আবেদনটি অনুমোদিত হয়নি</strong>
                                <p class="mb-0">
                                    এই আবেদনের বিপরীতে সিকিউরিটি ডিপোজিট
                                    পরিশোধের সুযোগ নেই।
                                </p>
                            </div>
                        </div>

                    @else

                        <div class="application-notice notice-info">
                            <i class="bi bi-hourglass-split"></i>
                            <div>
                                <strong>আবেদন প্রক্রিয়াধীন</strong>
                                <p class="mb-0">
                                    আবেদন অনুমোদিত হলে সিকিউরিটি ডিপোজিট
                                    পরিশোধের অপশন এখানে প্রদর্শিত হবে।
                                </p>
                            </div>
                        </div>

                    @endif

                </div>
            </div>

        @else

            {{-- No Result --}}
            <div class="no-application-result">

                <div class="no-application-icon">
                    <i class="bi bi-file-earmark-x"></i>
                </div>

                <h4>কোনো আবেদন পাওয়া যায়নি</h4>

                <p>
                    আপনার দেওয়া আবেদন নম্বরের সঙ্গে কোনো আবেদন পাওয়া যায়নি।
                    অনুগ্রহ করে সঠিক আবেদন নম্বর দিয়ে আবার অনুসন্ধান করুন।
                </p>

                <a href="{{ route('home') }}"
                   class="btn application-home-button mt-2">
                    <i class="bi bi-house me-1"></i>
                    হোম পেজে ফিরে যান
                </a>

            </div>

        @endif

        <div class="text-center mt-4">
            <a href="{{ route('home') }}" class="back-home-link">
                <i class="bi bi-arrow-left me-1"></i>
                হোম পেজে ফিরে যান
            </a>
        </div>

    </div>
</section>

@endsection

@push('styles')
<style>
.application-result-section {
    background: #f5f8f7;
    min-height: 65vh;
}

.application-page-icon {
    width: 65px;
    height: 65px;
    display: grid;
    place-items: center;
    margin: auto;
    border-radius: 16px;
    background: #e6f3ef;
    color: #075e54;
    font-size: 30px;
}

.application-page-title {
    color: #075e54;
    font-size: 27px;
    font-weight: 700;
}

.application-search-card,
.application-result-card,
.no-application-result {
    background: #fff;
    border: 1px solid #e8eeeb;
    border-radius: 14px;
    box-shadow: 0 7px 25px rgba(0,0,0,.04);
}

.application-search-card {
    max-width: 850px;
    margin: auto;
    padding: 15px;
}

.application-search-input {
    display: flex;
    align-items: center;
    gap: 12px;
}

.application-search-input > i {
    color: #07856f;
    font-size: 19px;
    padding-left: 8px;
}

.application-search-input input {
    flex: 1;
    min-width: 0;
    border: none;
    outline: none;
    padding: 12px 0;
}

.application-search-input button {
    border: none;
    background: #075e54;
    color: #fff;
    padding: 12px 20px;
    border-radius: 8px;
    font-weight: 600;
}

.application-result-card {
    max-width: 950px;
    margin: auto;
    overflow: hidden;
}

.application-result-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #f0f8f6;
    border-bottom: 1px solid #e5eeeb;
    padding: 22px 25px;
}

.application-result-header h4 {
    color: #075e54;
    font-weight: 700;
    font-size: 19px;
}

.result-found-label {
    color: #16834a;
    font-size: 13px;
    font-weight: 600;
}

.application-header-icon {
    width: 43px;
    height: 43px;
    display: grid;
    place-items: center;
    border-radius: 50%;
    background: #dcefe8;
    color: #075e54;
    font-size: 22px;
}

.application-result-body {
    padding: 25px;
}

.application-status-box {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    background: #f8faf9;
    border: 1px solid #e5eeeb;
    border-radius: 10px;
    padding: 16px;
    margin-bottom: 25px;
}

.application-status-box span {
    display: block;
    color: #667085;
    font-size: 14px;
    margin-bottom: 6px;
}

.application-status-box strong {
    font-size: 17px;
}

.application-status-box > i {
    color: #07856f;
    font-size: 23px;
}

.status-success { color: #16834a; }
.status-danger { color: #d92d20; }
.status-warning { color: #b54708; }
.status-pending { color: #667085; }

.result-section-title {
    color: #075e54;
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 15px;
}

.info-item {
    background: #f8faf9;
    border: 1px solid #edf0ee;
    border-radius: 8px;
    padding: 12px 14px;
    min-height: 70px;
}

.info-item span {
    display: block;
    color: #667085;
    font-size: 14px;
    margin-bottom: 5px;
}

.info-item strong {
    display: block;
    color: #263238;
    font-size: 16px;
    font-weight: 600;
    overflow-wrap: anywhere;
}

/* Security Deposit */

.deposit-section {
    margin-top: 15px;
    padding: 22px;
    background: #f5faf8;
    border: 1px solid #dceee6;
    border-radius: 12px;
}

.deposit-heading {
    display: flex;
    align-items: flex-start;
    gap: 13px;
}

.deposit-icon {
    width: 43px;
    height: 43px;
    flex-shrink: 0;
    display: grid;
    place-items: center;
    border-radius: 10px;
    background: #dcefe8;
    color: #075e54;
    font-size: 22px;
}

.deposit-heading h5 {
    color: #075e54;
    font-weight: 700;
    margin-bottom: 5px;
}

.deposit-heading p {
    color: #667085;
    font-size: 14px;
    line-height: 1.7;
    margin-bottom: 0;
}

.deposit-amount {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    background: #fff;
    border: 1px solid #e5eeeb;
    border-radius: 9px;
    padding: 16px;
    margin: 20px 0;
}

.deposit-amount span {
    color: #667085;
    font-size: 14px;
}

.deposit-amount strong {
    color: #075e54;
    font-size: 23px;
    font-weight: 700;
    white-space: nowrap;
}

.deposit-payment-info {
    color: #667085;
    font-size: 13px;
    margin-bottom: 15px;
}

.deposit-pay-button {
    width: 100%;
    border: none;
    border-radius: 9px;
    background: #075e54;
    color: #fff;
    padding: 14px 20px;
    font-weight: 700;
    font-size: 15px;
    transition: .2s;
}

.deposit-pay-button:hover {
    background: #064b44;
}

.deposit-paid-message,
.application-notice {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 16px;
    border-radius: 9px;
}

.deposit-paid-message {
    background: #eaf7ef;
    border: 1px solid #ccebd7;
    color: #16834a;
}

.deposit-paid-message > i,
.application-notice > i {
    font-size: 22px;
    flex-shrink: 0;
}

.deposit-paid-message strong,
.application-notice strong {
    display: block;
    margin-bottom: 4px;
}

.deposit-paid-message p,
.application-notice p {
    font-size: 13px;
    line-height: 1.6;
}

.notice-info {
    background: #eff8ff;
    border: 1px solid #b2ddff;
    color: #175cd3;
    margin-top: 20px;
}

.notice-danger {
    background: #fff1f0;
    border: 1px solid #fecdca;
    color: #d92d20;
    margin-top: 20px;
}

/* No Result */

.no-application-result {
    max-width: 650px;
    margin: auto;
    text-align: center;
    padding: 45px 25px;
}

.no-application-icon {
    width: 75px;
    height: 75px;
    display: grid;
    place-items: center;
    margin: 0 auto 20px;
    border-radius: 50%;
    background: #fff1f0;
    color: #d92d20;
    font-size: 34px;
}

.no-application-result h4 {
    color: #344054;
    font-weight: 700;
}

.no-application-result p {
    color: #667085;
    font-size: 14px;
    line-height: 1.8;
    max-width: 500px;
    margin: 12px auto;
}

.application-home-button {
    background: #075e54;
    color: #fff;
}

.application-home-button:hover {
    background: #064b44;
    color: #fff;
}

.back-home-link {
    color: #075e54;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
}

.back-home-link:hover {
    text-decoration: underline;
}

@media(max-width: 767px) {
    .application-page-title {
        font-size: 22px;
    }

    .application-result-header,
    .application-result-body {
        padding: 17px;
    }

    .application-search-input {
        flex-wrap: wrap;
    }

    .application-search-input input {
        width: calc(100% - 55px);
    }

    .application-search-input button {
        width: 100%;
    }

    .deposit-section {
        padding: 16px;
    }

    .deposit-amount {
        align-items: flex-start;
        flex-direction: column;
    }
}
</style>
@endpush
