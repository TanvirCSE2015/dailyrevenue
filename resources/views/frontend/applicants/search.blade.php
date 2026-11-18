
@extends('frontend.layouts.app')

@section('title', 'আবেদনকারী অনুসন্ধান | প্লট ব্যবস্থাপনা সিস্টেম')

@section('content')

<section class="applicant-result-section py-5">
    <div class="container">

        {{-- Page Heading --}}
        <div class="text-center mb-4">
            <div class="result-page-icon">
                <i class="bi bi-person-vcard"></i>
            </div>

            <h2 class="result-page-title mt-3">
                আবেদনকারীর তথ্য অনুসন্ধান
            </h2>

            <p class="text-secondary">
                অনুসন্ধানের ফলাফল নিচে দেখুন।
            </p>
        </div>

        {{-- Search Again: Applicant Number Only --}}
        <div class="search-again-card mb-4">
            <form action="{{ route('frontend.applicants.search') }}"
                  method="GET">

                <div class="search-again-input">
                    <i class="bi bi-search"></i>

                    <input
                        type="text"
                        name="q"
                        value="{{ $search }}"
                        placeholder="আবেদনকারী নম্বর লিখুন"
                        required
                    >

                    <button type="submit">
                        অনুসন্ধান
                        <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                </div>
            </form>

            @error('q')
                <div class="text-danger small mt-2">
                    {{ $message }}
                </div>
            @enderror
        </div>

        @if($applicant)

            {{-- Applicant Result --}}
            <div class="applicant-result-card">

                {{-- Result Header --}}
                <div class="result-card-header">
                    <div>
                        <span class="result-found-label">
                            <i class="bi bi-check-circle-fill me-1"></i>
                            আবেদনকারীর তথ্য পাওয়া গেছে
                        </span>

                        <h4 class="mt-2 mb-0">
                            আবেদনকারীর প্রাথমিক তথ্য
                        </h4>
                    </div>

                    <div class="result-user-icon">
                        <i class="bi bi-person-fill"></i>
                    </div>
                </div>

                {{-- Applicant Information --}}
                <div class="result-card-body">

                    <div class="row g-4">

                        {{-- Applicant Image --}}
                        <div class="col-md-3 text-center">
                            <div class="applicant-photo-wrapper">
                                @if($applicant->image)
                                    <img
                                        src="{{ asset('storage/'.$applicant->image) }}"
                                        alt="Applicant Photo"
                                        width="130px"
                                    >
                                @else
                                    <div class="applicant-photo-placeholder">
                                        <i class="bi bi-person"></i>
                                    </div>
                                @endif
                            </div>

                            <div class="mt-2 text-secondary small">
                                আবেদনকারীর ছবি
                            </div>
                        </div>

                        {{-- Applicant Details --}}
                        <div class="col-md-9">
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <div class="info-item">
                                        <span>আবেদনকারী নম্বর</span>
                                        <strong>
                                            {{ $applicant->applicant_no }}
                                        </strong>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="info-item">
                                        <span>আবেদনকারীর নাম</span>
                                        <strong>
                                            {{ $applicant->name }}
                                        </strong>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="info-item">
                                        <span>মোবাইল নম্বর</span>
                                        <strong>
                                            {{ '********'.substr($applicant->mobile, -3) }}
                                        </strong>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="info-item">
                                        <span>জাতীয় পরিচয়পত্র</span>
                                        <strong>
                                            @if($applicant->nid)
                                                {{ '********'.substr($applicant->nid, -4) }}
                                            @else
                                                তথ্য নেই
                                            @endif
                                        </strong>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="info-item">
                                        <span>পিতার নাম</span>
                                        <strong>
                                            {{ $applicant->father_name ?? 'তথ্য নেই' }}
                                        </strong>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="info-item">
                                        <span>বর্তমান ঠিকানা</span>
                                        <strong>
                                            {{ $applicant->present_address ?? 'তথ্য নেই' }}
                                        </strong>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- Identity Verification --}}
                    @if(!$isVerified)

                        {{-- Next Action --}}
                        <div class="applicant-next-action mt-4"
                             id="applicantNextAction">

                            <div>
                                <i class="bi bi-shield-lock-fill"></i>

                                <div>
                                    <strong>পরিচয় যাচাই প্রয়োজন</strong>

                                    <p class="mb-0">
                                        আবেদন ও লেনদেনের বিস্তারিত দেখতে
                                        আপনার নিবন্ধিত NID ও মোবাইল নম্বর
                                        দিয়ে পরিচয় যাচাই করুন।
                                    </p>
                                </div>
                            </div>

                            <button
                                type="button"
                                class="btn btn-primary-custom"
                                data-bs-toggle="collapse"
                                data-bs-target="#applicantVerification"
                                aria-expanded="false"
                                aria-controls="applicantVerification"
                            >
                                বিস্তারিত দেখুন
                                <i class="bi bi-arrow-right ms-1"></i>
                            </button>

                        </div>

                        {{-- Identity Verification Form --}}
                        <div class="collapse mt-3" id="applicantVerification">

                            <div class="verification-card">

                                <div class="verification-heading">
                                    <i class="bi bi-shield-check"></i>

                                    <div>
                                        <h5>পরিচয় যাচাই</h5>
                                        <p>
                                            আপনার নিবন্ধিত NID ও মোবাইল নম্বর
                                            প্রদান করুন।
                                        </p>
                                    </div>
                                </div>

                                {{-- AJAX Error Message --}}
                                <div
                                    id="verificationError"
                                    class="alert alert-danger d-none"
                                    role="alert"
                                ></div>

                                <form
                                    action="{{ route('frontend.applicants.verify') }}"
                                    method="POST"
                                    id="applicantVerificationForm"
                                >
                                    @csrf

                                    {{-- Applicant Number --}}
                                    <input
                                        type="hidden"
                                        name="applicant_no"
                                        value="{{ $applicant->applicant_no }}"
                                    >

                                    <div class="row g-3">

                                        {{-- NID --}}
                                        <div class="col-md-6">
                                            <label for="nid" class="form-label">
                                                জাতীয় পরিচয়পত্র নম্বর (NID)
                                                <span class="text-danger">*</span>
                                            </label>

                                            <input
                                                type="text"
                                                name="nid"
                                                id="nid"
                                                class="form-control"
                                                placeholder="আপনার NID নম্বর লিখুন"
                                                required
                                                autocomplete="off"
                                            >
                                        </div>

                                        {{-- Mobile --}}
                                        <div class="col-md-6">
                                            <label for="mobile" class="form-label">
                                                নিবন্ধিত মোবাইল নম্বর
                                                <span class="text-danger">*</span>
                                            </label>

                                            <input
                                                type="tel"
                                                name="mobile"
                                                id="mobile"
                                                class="form-control"
                                                placeholder="আপনার মোবাইল নম্বর লিখুন"
                                                required
                                                autocomplete="tel"
                                            >
                                        </div>

                                    </div>

                                    <div class="verification-actions mt-4">

                                        <button
                                            type="button"
                                            class="btn btn-light border"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#applicantVerification"
                                        >
                                            বাতিল
                                        </button>

                                        <button
                                            type="submit"
                                            class="btn btn-primary-custom"
                                            id="verificationSubmitBtn"
                                        >
                                            <i class="bi bi-shield-check me-1"></i>
                                            যাচাই করুন
                                        </button>

                                    </div>

                                </form>

                            </div>
                        </div>

                    @endif

                    {{-- Applicant Details Section --}}
                    <div
                        class="collapse mt-4 {{ $isVerified ? 'show' : '' }}"
                        id="applicantDetailsSection"
                    >

                        {{-- Details Header --}}
                        <div class="verified-heading mb-3">
                            <div>
                                <i class="bi bi-patch-check-fill me-2"></i>
                                পরিচয় সফলভাবে যাচাই হয়েছে
                            </div>
                        </div>

                        {{-- Tabs --}}
                        <ul class="nav nav-tabs applicant-info-tabs"
                            id="applicantInfoTabs"
                            role="tablist">

                            <li class="nav-item" role="presentation">
                                <button
                                    class="nav-link active"
                                    id="details-tab"
                                    data-bs-toggle="tab"
                                    data-bs-target="#details-pane"
                                    type="button"
                                    role="tab"
                                    aria-controls="details-pane"
                                    aria-selected="true"
                                >
                                    <i class="bi bi-person-lines-fill me-1"></i>
                                    বিস্তারিত তথ্য
                                </button>
                            </li>

                            <li class="nav-item" role="presentation">
                                <button
                                    class="nav-link"
                                    id="payments-tab"
                                    data-bs-toggle="tab"
                                    data-bs-target="#payments-pane"
                                    type="button"
                                    role="tab"
                                    aria-controls="payments-pane"
                                    aria-selected="false"
                                >
                                    <i class="bi bi-cash-stack me-1"></i>
                                    পরিশোধের তথ্য
                                </button>
                            </li>

                            <li class="nav-item" role="presentation">
                                <button
                                    class="nav-link"
                                    id="dues-tab"
                                    data-bs-toggle="tab"
                                    data-bs-target="#dues-pane"
                                    type="button"
                                    role="tab"
                                    aria-controls="dues-pane"
                                    aria-selected="false"
                                >
                                    <i class="bi bi-receipt me-1"></i>
                                    বকেয়ার তথ্য
                                </button>
                            </li>

                        </ul>

                        {{-- Tab Content --}}
                        <div class="tab-content applicant-tab-content"
                             id="applicantInfoTabContent">

                            {{-- Details Tab --}}
                            <div
                                class="tab-pane fade show active"
                                id="details-pane"
                                role="tabpanel"
                                aria-labelledby="details-tab"
                                tabindex="0"
                            >

                                <h5 class="tab-section-title">
                                    <i class="bi bi-person-vcard me-2"></i>
                                    আবেদনকারীর বিস্তারিত তথ্য
                                </h5>

                                <div class="row g-3">

                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <span>আবেদনকারী নম্বর</span>
                                            <strong>{{ $applicant->applicant_no }}</strong>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <span>আবেদনকারীর নাম</span>
                                            <strong>{{ $applicant->name }}</strong>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <span>পিতার নাম</span>
                                            <strong>{{ $applicant->father_name ?? 'তথ্য নেই' }}</strong>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <span>মাতার নাম</span>
                                            <strong>{{ $applicant->mother_name ?? 'তথ্য নেই' }}</strong>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <span>মোবাইল নম্বর</span>
                                            <strong>{{ $applicant->mobile ?? 'তথ্য নেই' }}</strong>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <span>জাতীয় পরিচয়পত্র নম্বর</span>
                                            <strong>{{ $applicant->nid ?? 'তথ্য নেই' }}</strong>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <span>বর্তমান ঠিকানা</span>
                                            <strong>{{ $applicant->present_address ?? 'তথ্য নেই' }}</strong>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <span>স্থায়ী ঠিকানা</span>
                                            <strong>{{ $applicant->permanent_address ?? 'তথ্য নেই' }}</strong>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            {{-- Payments Tab --}}
                            <div
                                class="tab-pane fade"
                                id="payments-pane"
                                role="tabpanel"
                                aria-labelledby="payments-tab"
                                tabindex="0"
                            >
                                <h5 class="tab-section-title">
                                    <i class="bi bi-cash-stack me-2"></i>
                                    পরিশোধের তথ্য
                                </h5>

                                {{-- Total Paid --}}
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <div class="payment-summary-card">
                                            <span>মোট ভাড়া পরিশোধ</span>
                                            <strong>
                                                ৳ {{ number_format($paymentTotal, 2) }}
                                            </strong>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="payment-summary-card">
                                            <span>মোট পেমেন্ট লেনদেন</span>
                                            <strong>
                                                {{ $paymentRows->count() }}
                                            </strong>
                                        </div>
                                    </div>
                                </div>

                                @if($paymentRows->isNotEmpty())

                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover align-middle applicant-ledger-table">
                                            <thead>
                                                <tr>
                                                    <th>ক্রমিক</th>
                                                    <th>ভাড়ার মাস</th>
                                                    <th>পেমেন্ট নম্বর</th>
                                                    <th>পেমেন্ট মাধ্যম</th>
                                                    <th>প্লট নম্বর</th>
                                                    <th>পরিশোধের তারিখ</th>
                                                    <th class="text-end">পরিশোধিত টাকা</th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                @foreach($paymentRows as $index => $payment)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>

                                                        <td>{{ $payment['month'] }}</td>

                                                        <td>
                                                            {{ $payment['payment_no'] }}
                                                        </td>

                                                        <td>
                                                            @switch($payment['gateway'])
                                                                @case('bkash')
                                                                    বিকাশ
                                                                    @break

                                                                @case('nagad')
                                                                    নগদ
                                                                    @break

                                                                @case('bangla_qr')
                                                                    বাংলা QR
                                                                    @break

                                                                @case('manual')
                                                                    অফিসে জমা
                                                                    @break

                                                                @default
                                                                    {{ $payment['gateway'] }}
                                                            @endswitch
                                                        </td>

                                                        <td>
                                                            {{ implode(', ', $payment['plots']) }}
                                                        </td>

                                                        <td>
                                                            {{ $payment['paid_at']
                                                                ? \Illuminate\Support\Carbon::parse($payment['paid_at'])->format('d-m-Y h:i A')
                                                                : '—'
                                                            }}
                                                        </td>

                                                        <td class="text-end fw-bold text-success">
                                                            ৳ {{ number_format($payment['amount'], 2) }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>

                                            <tfoot>
                                                <tr>
                                                    <th colspan="6" class="text-end">
                                                        সর্বমোট পরিশোধ
                                                    </th>

                                                    <th class="text-end text-success">
                                                        ৳ {{ number_format($paymentTotal, 2) }}
                                                    </th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                @else

                                    <div class="tab-placeholder">
                                        <i class="bi bi-wallet2"></i>
                                        <p>এখন পর্যন্ত কোনো ভাড়া পরিশোধের তথ্য পাওয়া যায়নি।</p>
                                    </div>

                                @endif
                            </div>

                            {{-- Dues Tab --}}
                           <div
                                class="tab-pane fade"
                                id="dues-pane"
                                role="tabpanel"
                                aria-labelledby="dues-tab"
                                tabindex="0"
                            >
                                <h5 class="tab-section-title">
                                    <i class="bi bi-receipt me-2"></i>
                                    বকেয়ার তথ্য
                                </h5>

                                {{-- Total Due --}}
                                <div class="row g-3 mb-4">

                                    <div class="col-md-6">
                                        <div class="due-summary-card">
                                            <span>সর্বমোট বকেয়া</span>

                                            <strong>
                                                ৳ {{ number_format($dueTotal, 2) }}
                                            </strong>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="due-summary-card">
                                            <span>হিসাবভুক্ত মাস</span>

                                            <strong>
                                                {{ $dueMonths->count() }}
                                            </strong>
                                        </div>
                                    </div>

                                </div>

                                @if($dueMonths->isNotEmpty())

                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover align-middle applicant-ledger-table">

                                            <thead>
                                                <tr>
                                                    <th>ক্রমিক</th>
                                                    <th>মাস</th>
                                                    <th>প্লট নম্বর</th>
                                                    <th class="text-end">মূল ভাড়া</th>
                                                    <th class="text-end">সমন্বয়</th>
                                                    <th class="text-end">নিট ভাড়া</th>
                                                    <th class="text-end">পরিশোধ</th>
                                                    <th class="text-end">বকেয়া</th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                @foreach($dueMonths as $index => $month)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>

                                                        <td>{{ $month['month'] }}</td>

                                                        <td>
                                                            {{ implode(', ', $month['plots']) }}
                                                        </td>

                                                        <td class="text-end">
                                                            ৳ {{ number_format($month['base_amount'], 2) }}
                                                        </td>

                                                        <td class="text-end">
                                                            ৳ {{ number_format($month['adjustment_amount'], 2) }}
                                                        </td>

                                                        <td class="text-end">
                                                            ৳ {{ number_format($month['net_amount'], 2) }}
                                                        </td>

                                                        <td class="text-end text-success">
                                                            ৳ {{ number_format($month['paid_amount'], 2) }}
                                                        </td>

                                                        <td class="text-end fw-bold {{ $month['due_amount'] > 0 ? 'text-danger' : 'text-success' }}">
                                                            ৳ {{ number_format($month['due_amount'], 2) }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>

                                            <tfoot>
                                                <tr>
                                                    <th colspan="3" class="text-end">
                                                        সর্বমোট
                                                    </th>

                                                    <th class="text-end">
                                                        ৳ {{ number_format($dueMonths->sum('base_amount'), 2) }}
                                                    </th>

                                                    <th class="text-end">
                                                        ৳ {{ number_format($dueMonths->sum('adjustment_amount'), 2) }}
                                                    </th>

                                                    <th class="text-end">
                                                        ৳ {{ number_format($dueMonths->sum('net_amount'), 2) }}
                                                    </th>

                                                    <th class="text-end text-success">
                                                        ৳ {{ number_format($dueMonths->sum('paid_amount'), 2) }}
                                                    </th>

                                                    <th class="text-end text-danger">
                                                        ৳ {{ number_format($dueTotal, 2) }}
                                                    </th>
                                                </tr>
                                            </tfoot>

                                        </table>
                                    </div>

                                @else

                                    <div class="tab-placeholder">
                                        <i class="bi bi-receipt"></i>
                                        <p>এখন পর্যন্ত কোনো দৈনিক ভাড়ার হিসাব পাওয়া যায়নি।</p>
                                    </div>

                                @endif
                            </div>

                        </div>
                    </div>

                </div>
            </div>

        @else

            {{-- No Result Found --}}
            <div class="no-result-card">

                <div class="no-result-icon">
                    <i class="bi bi-person-x"></i>
                </div>

                <h4>কোনো আবেদনকারী পাওয়া যায়নি</h4>

                <p>
                    আপনার দেওয়া আবেদনকারী নম্বরের সঙ্গে কোনো আবেদনকারীর
                    তথ্য মেলেনি। অনুগ্রহ করে আবেদনকারী নম্বর সঠিকভাবে লিখুন।
                </p>

                <a href="{{ route('home') }}"
                   class="btn btn-primary-custom mt-2">
                    <i class="bi bi-house me-1"></i>
                    হোম পেজে ফিরে যান
                </a>

            </div>

        @endif

        {{-- Back to Home --}}
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

.applicant-result-section {
    background: #f5f8f7;
    min-height: 65vh;
}

.result-page-icon {
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

.result-page-title {
    color: #075e54;
    font-size: 27px;
    font-weight: 700;
}

.search-again-card,
.applicant-result-card,
.no-result-card {
    background: #fff;
    border: 1px solid #e8eeeb;
    border-radius: 14px;
    box-shadow: 0 7px 25px rgba(0,0,0,.04);
}

.search-again-card {
    max-width: 850px;
    margin: auto;
    padding: 15px;
}

.search-again-input {
    display: flex;
    align-items: center;
    gap: 12px;
}

.search-again-input > i {
    color: #07856f;
    font-size: 19px;
    padding-left: 8px;
}

.search-again-input input {
    flex: 1;
    min-width: 0;
    border: none;
    outline: none;
    padding: 12px 0;
    color: #263238;
}

.search-again-input button {
    border: none;
    background: #075e54;
    color: #fff;
    padding: 12px 20px;
    border-radius: 8px;
    font-weight: 600;
    white-space: nowrap;
}

.search-again-input button:hover {
    background: #064b44;
}

.applicant-result-card {
    max-width: 950px;
    margin: auto;
    overflow: hidden;
}

.result-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #f0f8f6;
    border-bottom: 1px solid #e5eeeb;
    padding: 22px 25px;
}

.result-card-header h4 {
    color: #075e54;
    font-weight: 700;
    font-size: 19px;
}

.result-found-label {
    color: #16834a;
    font-size: 13px;
    font-weight: 600;
}

.result-user-icon {
    width: 43px;
    height: 43px;
    display: grid;
    place-items: center;
    border-radius: 50%;
    background: #dcefe8;
    color: #075e54;
    font-size: 22px;
}

.result-card-body {
    padding: 25px;
}

.applicant-photo-wrapper {
    width: 135px;
    height: 155px;
    margin: auto;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    overflow: hidden;
    background: #f8faf9;
}

.applicant-photo-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.applicant-photo-placeholder {
    height: 100%;
    display: grid;
    place-items: center;
    color: #98a2b3;
    font-size: 65px;
}

.info-item {
    background: #f8faf9;
    border: 1px solid #edf0ee;
    border-radius: 8px;
    padding: 6px 14px;
    min-height: 68px;
}

.info-item span {
    display: block;
    color: #667085;
    font-size: 18px;
    margin-bottom: 5px;
}

.info-item strong {
    display: block;
    color: #263238;
    font-size: 18px;
    font-weight: 600;
    overflow-wrap: anywhere;
}

.applicant-next-action {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    background: #f5faf8;
    border: 1px solid #e0eee8;
    border-radius: 10px;
    padding: 17px;
}

.applicant-next-action > div {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.applicant-next-action > div > i {
    color: #07856f;
    font-size: 22px;
}

.applicant-next-action strong {
    color: #075e54;
    font-size: 18px;
}

.applicant-next-action p {
    color: #667085;
    font-size: 13px;
    margin-top: 4px;
}

.btn-primary-custom {
    background: #075e54;
    color: #fff;
    border: none;
    padding: 11px 18px;
    border-radius: 8px;
    font-weight: 600;
}

.btn-primary-custom:hover {
    background: #064b44;
    color: #fff;
}

/* Verification Form */

.verification-card {
    background: #fbfdfc;
    border: 1px solid #e0eee8;
    border-radius: 10px;
    padding: 22px;
}

.verification-heading {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 20px;
}

.verification-heading > i {
    color: #07856f;
    font-size: 25px;
}

.verification-heading h5 {
    color: #075e54;
    font-weight: 700;
    margin-bottom: 4px;
}

.verification-heading p {
    color: #667085;
    font-size: 14px;
    margin-bottom: 0;
}

.verification-card .form-label {
    color: #344054;
    font-weight: 600;
    font-size: 16px;
}

.verification-card .form-control {
    min-height: 45px;
    border-color: #dce5e1;
    border-radius: 8px;
}

.verification-card .form-control:focus {
    border-color: #07856f;
    box-shadow: 0 0 0 3px rgba(7,133,111,.10);
}

.verification-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

/* Verified Details */

.verified-heading {
    background: #eaf7ef;
    border: 1px solid #ccebd7;
    color: #16834a;
    border-radius: 8px;
    padding: 13px 16px;
    font-weight: 700;
}

.applicant-info-tabs {
    border-bottom: 1px solid #dce5e1;
    gap: 5px;
}

.applicant-info-tabs .nav-link {
    color: #52615b;
    font-weight: 600;
    border-radius: 8px 8px 0 0;
    padding: 12px 17px;
}

.applicant-info-tabs .nav-link:hover {
    color: #075e54;
    background: #f0f8f6;
}

.applicant-info-tabs .nav-link.active {
    color: #075e54;
    background: #fff;
    border-color: #dce5e1 #dce5e1 #fff;
}

.applicant-tab-content {
    background: #fff;
    border: 1px solid #dce5e1;
    border-top: 0;
    border-radius: 0 0 10px 10px;
    padding: 22px;
}

.tab-section-title {
    color: #075e54;
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 18px;
}

.tab-placeholder {
    min-height: 160px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    background: #f8faf9;
    border: 1px dashed #dce5e1;
    border-radius: 9px;
    padding: 25px;
    color: #667085;
}

.tab-placeholder i {
    font-size: 30px;
    color: #07856f;
    margin-bottom: 12px;
}

.tab-placeholder p {
    margin: 0;
    font-size: 14px;
}

/* No Result */

.no-result-card {
    max-width: 650px;
    margin: auto;
    text-align: center;
    padding: 45px 25px;
}

.no-result-icon {
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

.no-result-card h4 {
    color: #344054;
    font-weight: 700;
}

.no-result-card p {
    color: #667085;
    font-size: 14px;
    line-height: 1.8;
    max-width: 500px;
    margin: 12px auto;
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


.payment-summary-card,
.due-summary-card {
    height: 100%;
    padding: 20px;
    border: 1px solid #e5eeeb;
    border-radius: 10px;
    background: #f8faf9;
}

.payment-summary-card span,
.due-summary-card span {
    display: block;
    color: #667085;
    font-size: 14px;
    margin-bottom: 8px;
}

.payment-summary-card strong {
    display: block;
    color: #16834a;
    font-size: 24px;
    font-weight: 700;
}

.due-summary-card strong {
    display: block;
    color: #d92d20;
    font-size: 24px;
    font-weight: 700;
}

.applicant-ledger-table {
    font-size: 13px;
    white-space: nowrap;
}

.applicant-ledger-table thead th {
    background: #f0f8f6;
    color: #075e54;
    font-weight: 700;
    vertical-align: middle;
}

.applicant-ledger-table tfoot th {
    background: #f8faf9;
    font-weight: 700;
}


@media(max-width: 767px) {

    .result-page-title {
        font-size: 22px;
    }

    .result-card-header {
        padding: 17px;
    }

    .result-card-body {
        padding: 17px;
    }

    .search-again-input {
        flex-wrap: wrap;
    }

    .search-again-input input {
        width: calc(100% - 55px);
    }

    .search-again-input button {
        width: 100%;
    }

    .applicant-next-action {
        align-items: stretch;
        flex-direction: column;
    }

    .applicant-next-action button {
        width: 100%;
    }

    .verification-card {
        padding: 16px;
    }

    .verification-actions {
        flex-direction: column-reverse;
    }

    .verification-actions button {
        width: 100%;
    }

    .applicant-info-tabs {
        flex-wrap: nowrap;
        overflow-x: auto;
    }

    .applicant-info-tabs .nav-link {
        white-space: nowrap;
        font-size: 13px;
        padding: 10px 12px;
    }

    .applicant-tab-content {
        padding: 15px;
    }
}

</style>
@endpush


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('applicantVerificationForm');

    if (!form) {
        return;
    }

    const submitButton = document.getElementById('verificationSubmitBtn');
    const errorBox = document.getElementById('verificationError');

    form.addEventListener('submit', async function (event) {

        event.preventDefault();

        errorBox.classList.add('d-none');
        errorBox.textContent = '';

        submitButton.disabled = true;
        submitButton.innerHTML =
            '<span class="spinner-border spinner-border-sm me-1"></span> যাচাই হচ্ছে...';

        try {

            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: new FormData(form)
            });

            const result = await response.json();

            if (!response.ok || !result.success) {

                let errorMessage =
                    result.message || 'পরিচয় যাচাই করা সম্ভব হয়নি।';

                if (result.errors) {
                    const firstError = Object.values(result.errors)[0];

                    if (firstError && firstError.length > 0) {
                        errorMessage = firstError[0];
                    }
                }

                throw new Error(errorMessage);
            }

            // Hide verification button
            const nextAction =
                document.getElementById('applicantNextAction');

            if (nextAction) {
                nextAction.classList.add('d-none');
            }

            // Hide verification form
            const verificationCollapse =
                document.getElementById('applicantVerification');

            const verificationInstance =
                bootstrap.Collapse.getOrCreateInstance(
                    verificationCollapse,
                    { toggle: false }
                );

            verificationInstance.hide();

            // Expand applicant details section
            const detailsSection =
                document.getElementById('applicantDetailsSection');

            const detailsInstance =
                bootstrap.Collapse.getOrCreateInstance(
                    detailsSection,
                    { toggle: false }
                );

            detailsInstance.show();

            // Clear verification inputs
            form.reset();

            // Show success message
            const successMessage = document.createElement('div');

            successMessage.className =
                'alert alert-success mb-3';

            successMessage.textContent = result.message;

            detailsSection.prepend(successMessage);

            // Scroll to details section
            setTimeout(function () {

                detailsSection.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });

            }, 300);

        } catch (error) {

            errorBox.textContent = error.message;
            errorBox.classList.remove('d-none');

        } finally {

            submitButton.disabled = false;

            submitButton.innerHTML =
                '<i class="bi bi-shield-check me-1"></i> যাচাই করুন';

        }

    });

});
</script>
@endpush
