
@extends('frontend.layouts.app')

@section('title', 'দৈনিক ভাড়া অনুসন্ধান | প্লট ব্যবস্থাপনা সিস্টেম')

@section('content')

<section class="rent-result-section py-5">
    <div class="container">

        {{-- Page Heading --}}
        <div class="text-center mb-4">
            <div class="rent-page-icon">
                <i class="bi bi-wallet2"></i>
            </div>

            <h2 class="rent-page-title mt-3">
                দৈনিক ভাড়া অনুসন্ধান
            </h2>

            <p class="text-secondary">
                প্লট নম্বর দিয়ে দৈনিক ভাড়া, মওকুফ, পরিশোধিত অর্থ ও বকেয়ার তথ্য দেখুন।
            </p>
        </div>

        {{-- Search Again --}}
        <div class="rent-search-card mb-4">
            <form action="{{ route('frontend.daily-rent.search') }}" method="GET">
                <div class="rent-search-input">
                    <i class="bi bi-search"></i>

                    <input
                        type="text"
                        name="plot_number"
                        value="{{ $plotNumber ?? request('plot_number') }}"
                        placeholder="আপনার প্লট নম্বর লিখুন"
                        required
                    >

                    <button type="submit">
                        অনুসন্ধান
                        <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                </div>
            </form>

            @error('plot_number')
                <div class="text-danger small mt-2">
                    {{ $message }}
                </div>
            @enderror
        </div>

        @if($plot && $allocation)

            @php
                $applicant = $allocation->applicant;

                $allocationStatus = [
                    'active' => 'সক্রিয়',
                    'surrendered' => 'সমর্পণ করা হয়েছে',
                    'cancelled' => 'বাতিল',
                    'expired' => 'মেয়াদ শেষ',
                ];
            @endphp

            <div class="rent-result-card">

                {{-- Header --}}
                <div class="rent-result-header">
                    <div>
                        <span class="rent-found-label">
                            <i class="bi bi-check-circle-fill me-1"></i>
                            প্লট ও বরাদ্দের তথ্য পাওয়া গেছে
                        </span>

                        <h4 class="mt-2 mb-0">
                            দৈনিক ভাড়ার বিস্তারিত হিসাব
                        </h4>
                    </div>

                    <div class="rent-header-icon">
                        <i class="bi bi-receipt"></i>
                    </div>
                </div>

                <div class="rent-result-body">

                    {{-- Incomplete Synchronization Warning --}}
                    @if($syncIncomplete)
                        <div class="rent-notice notice-warning mb-4">
                            <i class="bi bi-exclamation-triangle-fill"></i>

                            <div>
                                <strong>ভাড়ার হিসাব সম্পূর্ণ নয়</strong>

                                <p class="mb-0">
                                    {{ $syncBlockedDate }} তারিখের জন্য কার্যকর দৈনিক
                                    ভাড়ার হার পাওয়া যায়নি। তাই ওই তারিখ থেকে পরবর্তী
                                    হিসাব তৈরি করা সম্ভব হয়নি। নিচের মোট বকেয়া
                                    চূড়ান্ত হিসাব নয়। অনুগ্রহ করে অফিসে যোগাযোগ করুন।
                                </p>
                            </div>
                        </div>
                    @endif

                    {{-- Plot Summary --}}
                    <div class="rent-summary-box">
                        <div>
                            <span>প্লট নম্বর</span>
                            <strong>{{ $plot->plot_number }}</strong>
                        </div>

                        <div>
                            <span>বরাদ্দের অবস্থা</span>
                            <strong class="rent-status-active">
                                {{ $allocationStatus[$allocation->status] ?? $allocation->status }}
                            </strong>
                        </div>
                    </div>

                    {{-- Applicant Information --}}
                    <h5 class="rent-section-title">
                        <i class="bi bi-person-vcard me-2"></i>
                        আবেদনকারীর তথ্য
                    </h5>

                    <div class="row g-3 mb-4">

                        <div class="col-md-6">
                            <div class="rent-info-item">
                                <span>আবেদনকারী নম্বর</span>
                                <strong>
                                    {{ $applicant?->applicant_no ?? 'তথ্য নেই' }}
                                </strong>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="rent-info-item">
                                <span>আবেদনকারীর নাম</span>
                                <strong>
                                    {{ $applicant?->name ?? 'তথ্য নেই' }}
                                </strong>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="rent-info-item">
                                <span>মোবাইল নম্বর</span>
                                <strong>
                                    @if($applicant?->mobile)
                                        {{ '********' . substr($applicant->mobile, -3) }}
                                    @else
                                        তথ্য নেই
                                    @endif
                                </strong>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="rent-info-item">
                                <span>জাতীয় পরিচয়পত্র</span>
                                <strong>
                                    @if($applicant?->nid)
                                        {{ '********' . substr($applicant->nid, -4) }}
                                    @else
                                        তথ্য নেই
                                    @endif
                                </strong>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="rent-info-item">
                                <span>বরাদ্দ শুরুর তারিখ</span>
                                <strong>
                                    {{ $allocation->start_date
                                        ? $allocation->start_date->format('d-m-Y')
                                        : 'তথ্য নেই' }}
                                </strong>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="rent-info-item">
                                <span>ভাড়া গণনা শুরুর তারিখ</span>
                                <strong>
                                    {{ $allocation->start_date
                                        ? $allocation->start_date->copy()->addDay()->format('d-m-Y')
                                        : 'তথ্য নেই' }}
                                </strong>
                            </div>
                        </div>

                    </div>

                    {{-- Rent Summary --}}
                    <h5 class="rent-section-title">
                        <i class="bi bi-calculator me-2"></i>
                        ভাড়া ও বকেয়ার সারসংক্ষেপ
                    </h5>

                    <div class="row g-3 mb-4">

                        <div class="col-md-3 col-6">
                            <div class="rent-stat-card">
                                <span>মোট মূল ভাড়া</span>

                                <strong>
                                    ৳ {{ number_format($totalRent, 2) }}
                                </strong>

                                <small>সর্বমোট দৈনিক ভাড়া</small>
                            </div>
                        </div>

                        <div class="col-md-3 col-6">
                            <div class="rent-stat-card rent-stat-waived">
                                <span>মোট মওকুফ</span>

                                <strong>
                                    ৳ {{ number_format($totalWaived, 2) }}
                                </strong>

                                <small>অনুমোদিত ভাড়া মওকুফ</small>
                            </div>
                        </div>

                        <div class="col-md-3 col-6">
                            <div class="rent-stat-card">
                                <span>মোট পরিশোধিত</span>

                                <strong>
                                    ৳ {{ number_format($totalPaid, 2) }}
                                </strong>

                                <small>যাচাইকৃত পেমেন্ট</small>
                            </div>
                        </div>

                        <div class="col-md-3 col-6">
                            <div class="rent-stat-card rent-stat-due">
                                <span>মোট বকেয়া</span>

                                @if($syncIncomplete)
                                    <strong>অসম্পূর্ণ</strong>
                                    <small>চূড়ান্ত হিসাব পাওয়া যায়নি</small>
                                @else
                                    <strong>
                                        ৳ {{ number_format($totalDue, 2) }}
                                    </strong>
                                    <small>পরিশোধযোগ্য বকেয়া</small>
                                @endif
                            </div>
                        </div>

                    </div>

                    {{-- Daily Ledger Details --}}
                    <h5 class="rent-section-title">
                        <i class="bi bi-calendar2-week me-2"></i>
                        তারিখভিত্তিক ভাড়ার বিস্তারিত বিবরণ
                    </h5>

                    @if($dueLedgers->isNotEmpty())

                        <div class="table-responsive rent-table-wrapper">
                            <table class="table rent-table align-middle mb-0">

                                <thead>
                                    <tr>
                                        <th>ক্রমিক</th>
                                        <th>তারিখ</th>
                                        <th>মূল ভাড়া</th>
                                        <th>মওকুফ</th>
                                        <th>পরিশোধযোগ্য ভাড়া</th>
                                        <th>পরিশোধিত</th>
                                        <th>বকেয়া</th>
                                        <th>অবস্থা</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach($dueLedgers as $index => $ledger)

                                        <tr>
                                            <td>{{ $index + 1 }}</td>

                                            <td>
                                                {{ $ledger->rent_date->format('d-m-Y') }}
                                            </td>

                                            <td>
                                                ৳ {{ number_format((float) $ledger->base_amount, 2) }}
                                            </td>

                                            <td>
                                                @if($ledger->calculated_waived_amount > 0)
                                                    <span class="text-success fw-semibold">
                                                        ৳ {{ number_format($ledger->calculated_waived_amount, 2) }}
                                                    </span>
                                                @else
                                                    ৳ 0.00
                                                @endif
                                            </td>

                                            <td>
                                                ৳ {{ number_format($ledger->calculated_payable_amount, 2) }}
                                            </td>

                                            <td>
                                                ৳ {{ number_format($ledger->calculated_paid_amount, 2) }}
                                            </td>

                                            <td class="rent-due-amount">
                                                ৳ {{ number_format($ledger->calculated_due_amount, 2) }}
                                            </td>

                                            <td>
                                                @if($ledger->calculated_display_status === 'waived')
                                                    <span class="badge bg-success">
                                                        সম্পূর্ণ মওকুফ
                                                    </span>

                                                @elseif($ledger->calculated_display_status === 'partially_waived')
                                                    <span class="badge bg-info text-dark">
                                                        আংশিক মওকুফ
                                                    </span>

                                                @elseif($ledger->calculated_display_status === 'paid')
                                                    <span class="badge bg-success">
                                                        পরিশোধিত
                                                    </span>

                                                @else
                                                    <span class="badge bg-warning text-dark">
                                                        বকেয়া
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>

                                    @endforeach
                                </tbody>

                            </table>
                        </div>

                    @else

                        <div class="rent-empty-state">
                            <i class="bi bi-check-circle"></i>

                            <strong>কোনো ভাড়ার হিসাব পাওয়া যায়নি</strong>

                            <p class="mb-0">
                                বর্তমানে প্রদর্শনের জন্য কোনো দৈনিক ভাড়ার Ledger নেই।
                            </p>
                        </div>

                    @endif

                    {{-- Rent Payment Section --}}
                    @if(!$syncIncomplete && $dueDays > 0)

                        <div class="rent-payment-section mt-4">

                            <div class="rent-payment-heading">
                                <div class="rent-payment-icon">
                                    <i class="bi bi-credit-card"></i>
                                </div>

                                <div>
                                    <h5>বকেয়া ভাড়া পরিশোধ</h5>

                                    <p>
                                        আপনার বকেয়া ভাড়া জনতা পে (JanataPay)
                                        এর মাধ্যমে পরিশোধ করুন।
                                    </p>
                                </div>
                            </div>

                            <div class="rent-payment-summary">
                                <div>
                                    <span>মোট বকেয়া দিন</span>

                                    <strong>
                                        {{ number_format($dueDays) }} দিন
                                    </strong>
                                </div>

                                <div>
                                    <span>সর্বমোট বকেয়া</span>

                                    <strong>
                                        ৳ {{ number_format($totalDue, 2) }}
                                    </strong>
                                </div>
                            </div>

                            <div class="rent-payment-form">

                                <label for="payment_days">
                                    কত দিনের ভাড়া পরিশোধ করবেন?
                                </label>

                                <select
                                    id="payment_days"
                                    class="form-select"
                                    disabled
                                >
                                    <option value="">
                                        পেমেন্ট ব্যবস্থা সংযুক্ত হলে নির্বাচন করতে পারবেন
                                    </option>
                                </select>

                                <div class="rent-payment-amount mt-3">
                                    <span>পরিশোধযোগ্য অর্থ</span>

                                    <strong>
                                        পেমেন্ট ব্যবস্থা সংযুক্তির অপেক্ষায়
                                    </strong>
                                </div>

                                <button
                                    type="button"
                                    class="rent-pay-button"
                                    disabled
                                >
                                    <i class="bi bi-shield-lock me-2"></i>
                                    JanataPay দিয়ে ভাড়া পরিশোধ
                                </button>

                                <small class="rent-payment-help">
                                    পেমেন্ট অপশনটি পরবর্তী ধাপে PaymentController
                                    ও JanataPay-এর সঙ্গে সংযুক্ত করা হবে।
                                </small>

                            </div>

                        </div>

                    @elseif($syncIncomplete)

                        <div class="rent-payment-disabled mt-4">
                            <i class="bi bi-lock-fill me-2"></i>

                            হিসাব সম্পূর্ণ না হওয়ায় আপাতত অনলাইন পেমেন্ট বন্ধ রয়েছে।
                        </div>

                    @endif

                </div>
            </div>

        @elseif($plot)

            {{-- Plot Found, No Active Allocation --}}
            <div class="rent-empty-result">
                <div class="rent-empty-icon">
                    <i class="bi bi-person-x"></i>
                </div>

                <h4>এই প্লটে সক্রিয় বরাদ্দ পাওয়া যায়নি</h4>

                <p>
                    প্লট নম্বর পাওয়া গেছে, তবে বর্তমানে এই প্লটের বিপরীতে
                    কোনো সক্রিয় বরাদ্দ নেই।
                </p>
            </div>

        @else

            {{-- Plot Not Found --}}
            <div class="rent-empty-result">
                <div class="rent-empty-icon">
                    <i class="bi bi-search"></i>
                </div>

                <h4>কোনো প্লট পাওয়া যায়নি</h4>

                <p>
                    আপনার দেওয়া প্লট নম্বরের সঙ্গে কোনো প্লট পাওয়া যায়নি।
                    অনুগ্রহ করে সঠিক প্লট নম্বর দিয়ে আবার অনুসন্ধান করুন।
                </p>
            </div>

        @endif

        <div class="text-center mt-4">
            <a href="{{ route('home') }}" class="rent-back-link">
                <i class="bi bi-arrow-left me-1"></i>
                হোম পেজে ফিরে যান
            </a>
        </div>

    </div>
</section>

@endsection

@push('styles')
<style>
.rent-result-section {
    background: #f5f8f7;
    min-height: 65vh;
}

.rent-page-icon {
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

.rent-page-title {
    color: #075e54;
    font-size: 27px;
    font-weight: 700;
}

.rent-search-card,
.rent-result-card,
.rent-empty-result {
    background: #fff;
    border: 1px solid #e8eeeb;
    border-radius: 14px;
    box-shadow: 0 7px 25px rgba(0,0,0,.04);
}

.rent-search-card {
    max-width: 850px;
    margin: auto;
    padding: 15px;
}

.rent-search-input {
    display: flex;
    align-items: center;
    gap: 12px;
}

.rent-search-input > i {
    color: #07856f;
    font-size: 19px;
    padding-left: 8px;
}

.rent-search-input input {
    flex: 1;
    min-width: 0;
    border: none;
    outline: none;
    padding: 12px 0;
}

.rent-search-input button {
    border: none;
    background: #075e54;
    color: #fff;
    padding: 12px 20px;
    border-radius: 8px;
    font-weight: 600;
}

.rent-result-card {
    max-width: 1100px;
    margin: auto;
    overflow: hidden;
}

.rent-result-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #f0f8f6;
    border-bottom: 1px solid #e5eeeb;
    padding: 22px 25px;
}

.rent-result-header h4 {
    color: #075e54;
    font-weight: 700;
    font-size: 19px;
}

.rent-found-label {
    color: #16834a;
    font-size: 13px;
    font-weight: 600;
}

.rent-header-icon {
    width: 43px;
    height: 43px;
    display: grid;
    place-items: center;
    border-radius: 50%;
    background: #dcefe8;
    color: #075e54;
    font-size: 22px;
}

.rent-result-body {
    padding: 25px;
}

.rent-summary-box {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    gap: 15px;
    background: #f8faf9;
    border: 1px solid #e5eeeb;
    border-radius: 10px;
    padding: 17px;
    margin-bottom: 25px;
}

.rent-summary-box span,
.rent-info-item span {
    display: block;
    color: #667085;
    font-size: 14px;
    margin-bottom: 5px;
}

.rent-summary-box strong {
    color: #075e54;
    font-size: 18px;
}

.rent-status-active {
    color: #16834a !important;
}

.rent-section-title {
    color: #075e54;
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 15px;
}

.rent-info-item {
    background: #f8faf9;
    border: 1px solid #edf0ee;
    border-radius: 8px;
    padding: 12px 14px;
    min-height: 70px;
}

.rent-info-item strong {
    display: block;
    color: #263238;
    font-size: 16px;
    font-weight: 600;
    overflow-wrap: anywhere;
}

.rent-stat-card {
    height: 100%;
    min-height: 120px;
    padding: 18px;
    background: #f0f8f6;
    border: 1px solid #dceee6;
    border-radius: 10px;
}

.rent-stat-card span,
.rent-stat-card small {
    display: block;
    color: #667085;
    font-size: 13px;
}

.rent-stat-card strong {
    display: block;
    color: #075e54;
    font-size: 21px;
    font-weight: 700;
    margin: 8px 0 3px;
    overflow-wrap: anywhere;
}

.rent-stat-waived {
    background: #effaf3;
    border-color: #ccebd7;
}

.rent-stat-waived strong {
    color: #16834a;
}

.rent-stat-due {
    background: #fff8ed;
    border-color: #f5dfbd;
}

.rent-stat-due strong {
    color: #b54708;
}

.rent-table-wrapper {
    border: 1px solid #e8eeeb;
    border-radius: 10px;
}

.rent-table {
    min-width: 1050px;
}

.rent-table thead th {
    background: #f0f8f6;
    color: #075e54;
    font-size: 16px;
    font-weight: 700;
    white-space: nowrap;
    padding: 10px;
}

.rent-table tbody td {
    color: #344054;
    font-size: 16px;
    padding: 10px;
    white-space: nowrap;
}

.rent-due-amount {
    color: #b54708 !important;
    font-weight: 700;
}

.rent-notice {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 16px;
    border-radius: 9px;
}

.rent-notice > i {
    font-size: 22px;
    flex-shrink: 0;
}

.rent-notice strong {
    display: block;
    margin-bottom: 5px;
}

.rent-notice p {
    font-size: 13px;
    line-height: 1.7;
}

.notice-warning {
    background: #fffaeb;
    border: 1px solid #fedf89;
    color: #b54708;
}

.rent-empty-state {
    text-align: center;
    background: #eaf7ef;
    border: 1px solid #ccebd7;
    color: #16834a;
    border-radius: 10px;
    padding: 25px;
}

.rent-empty-state i {
    display: block;
    font-size: 30px;
    margin-bottom: 8px;
}

.rent-empty-state strong {
    display: block;
    font-size: 17px;
    margin-bottom: 5px;
}

.rent-empty-state p {
    font-size: 13px;
}

/* Rent Payment */

.rent-payment-section {
    padding: 22px;
    background: #f5faf8;
    border: 1px solid #dceee6;
    border-radius: 12px;
}

.rent-payment-heading {
    display: flex;
    align-items: flex-start;
    gap: 13px;
}

.rent-payment-icon {
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

.rent-payment-heading h5 {
    color: #075e54;
    font-weight: 700;
    margin-bottom: 5px;
}

.rent-payment-heading p {
    color: #667085;
    font-size: 14px;
    line-height: 1.7;
    margin-bottom: 0;
}

.rent-payment-summary {
    display: flex;
    justify-content: space-between;
    gap: 15px;
    background: #fff;
    border: 1px solid #e5eeeb;
    border-radius: 9px;
    padding: 16px;
    margin: 20px 0;
}

.rent-payment-summary span {
    display: block;
    color: #667085;
    font-size: 13px;
    margin-bottom: 5px;
}

.rent-payment-summary strong {
    color: #075e54;
    font-size: 18px;
}

.rent-payment-form label {
    display: block;
    color: #344054;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 8px;
}

.rent-payment-form .form-select {
    min-height: 46px;
    border-color: #d0d5dd;
}

.rent-payment-amount {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    background: #fff;
    border: 1px solid #e5eeeb;
    border-radius: 9px;
    padding: 15px;
}

.rent-payment-amount span {
    color: #667085;
    font-size: 14px;
}

.rent-payment-amount strong {
    color: #075e54;
    font-size: 17px;
}

.rent-pay-button {
    width: 100%;
    border: none;
    border-radius: 9px;
    background: #075e54;
    color: #fff;
    padding: 14px 20px;
    font-weight: 700;
    font-size: 15px;
    margin-top: 18px;
    opacity: .55;
    cursor: not-allowed;
}

.rent-payment-help {
    display: block;
    color: #667085;
    font-size: 12px;
    margin-top: 10px;
}

.rent-payment-disabled {
    padding: 14px;
    border-radius: 9px;
    background: #fff8ed;
    border: 1px solid #f5dfbd;
    color: #b54708;
    font-size: 13px;
}

.rent-empty-result {
    max-width: 650px;
    margin: auto;
    text-align: center;
    padding: 45px 25px;
}

.rent-empty-icon {
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

.rent-empty-result h4 {
    color: #344054;
    font-weight: 700;
}

.rent-empty-result p {
    color: #667085;
    font-size: 14px;
    line-height: 1.8;
    max-width: 500px;
    margin: 12px auto;
}

.rent-back-link {
    color: #075e54;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
}

.rent-back-link:hover {
    text-decoration: underline;
}

@media(max-width: 767px) {
    .rent-page-title {
        font-size: 22px;
    }

    .rent-result-header,
    .rent-result-body {
        padding: 17px;
    }

    .rent-search-input {
        flex-wrap: wrap;
    }

    .rent-search-input input {
        width: calc(100% - 55px);
    }

    .rent-search-input button {
        width: 100%;
    }

    .rent-summary-box,
    .rent-payment-summary {
        flex-direction: column;
    }

    .rent-payment-section {
        padding: 16px;
    }

    .rent-payment-amount {
        align-items: flex-start;
        flex-direction: column;
    }

    .rent-stat-card {
        padding: 13px;
    }

    .rent-stat-card strong {
        font-size: 18px;
    }
}
</style>
@endpush
