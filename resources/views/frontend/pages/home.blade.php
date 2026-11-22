
@extends('frontend.layouts.app')

@section('title', 'হোম | প্লট ব্যবস্থাপনা সিস্টেম')

@section('content')

{{-- Hero Section with Notice Board --}}
<section class="hero">
    <div class="container">
        <div class="row align-items-center g-4">

            {{-- Left: Hero Content --}}
            <div class="col-lg-7">

                <span class="badge bg-light text-success mb-3 px-3 py-2">
                    <i class="bi bi-globe2 me-1"></i>
                    অনলাইন আবেদন ও সেবা পোর্টাল
                </span>

                <h1>
                    আপনার পছন্দের প্লটের জন্য
                    অনলাইনে আবেদন করুন
                </h1>

                <p class="mt-3">
                    নতুন আবেদন, আবেদন অনুসন্ধান, প্লট বরাদ্দের
                    তথ্য এবং মাসিক লেনদেনের হিসাব দেখুন
                    সহজেই।
                </p>

                <div class="d-flex flex-wrap gap-3 mt-4 justify-content-center justify-content-lg-start">

                    <a href="{{ route('frontend.applications.create') }}"
                       class="btn btn-light btn-lg text-success px-4">
                        <i class="bi bi-file-earmark-plus me-1"></i>
                        নতুন আবেদন করুন
                    </a>

                    <a href="#applicant-search"
                       class="btn btn-outline-light btn-lg px-4">
                        <i class="bi bi-search me-1"></i>
                        আবেদন অনুসন্ধান
                    </a>

                </div>
            </div>


            {{-- Right: Dynamic Notice Board --}}
            <div class="col-lg-5">

                <div class="hero-notice-card">

                    {{-- Notice Header --}}
                    <div class="hero-notice-header">

                        <div class="d-flex align-items-center gap-2">
                            <div class="notice-icon">
                                <i class="bi bi-megaphone-fill"></i>
                            </div>

                            <div>
                                <h4 class="mb-0">নোটিশ বোর্ড</h4>
                                <!-- <small>সর্বশেষ বিজ্ঞপ্তি ও ঘোষণা</small> -->
                            </div>
                        </div>

                        <span class="notice-live-badge">
                            <span class="live-dot"></span>
                            Live
                        </span>

                    </div>


                    {{-- Notice List --}}
                    <div class="hero-notice-list">

                        @forelse($notices as $notice)

                            <div class="hero-notice-item">

                                <div class="d-flex align-items-start gap-3">

                                    <div class="notice-date">
                                        <span>
                                            {{ $notice->published_at?->format('d') ?? '--' }}
                                        </span>

                                        <small>
                                            {{ $notice->published_at?->translatedFormat('M') ?? '' }}
                                        </small>
                                    </div>

                                    <div class="flex-grow-1">

                                        <div class="d-flex flex-wrap gap-2 align-items-center mb-1">

                                            @if($notice->is_important)
                                                <span class="notice-important">
                                                    গুরুত্বপূর্ণ
                                                </span>
                                            @endif

                                            <small class="text-secondary">
                                                {{ $notice->published_at?->format('d-m-Y') ?? '' }}
                                            </small>

                                        </div>

                                        <h6 class="notice-title">
                                            {{ $notice->title }}
                                        </h6>

                                        <p class="notice-description">
                                            {{ \Illuminate\Support\Str::limit(strip_tags($notice->body), 100) }}
                                        </p>

                                        @if($notice->attachment)
                                            <a href="{{ asset('storage/'.$notice->attachment) }}"
                                               target="_blank"
                                               rel="noopener"
                                               class="notice-attachment">
                                                <i class="bi bi-paperclip"></i>
                                                সংযুক্তি দেখুন
                                            </a>
                                        @endif

                                    </div>

                                </div>

                            </div>

                        @empty

                            <div class="notice-empty">
                                <i class="bi bi-bell-slash"></i>

                                <h6>কোনো নোটিশ নেই</h6>

                                <p class="mb-0">
                                    বর্তমানে কোনো বিজ্ঞপ্তি প্রকাশিত হয়নি।
                                </p>
                            </div>

                        @endforelse

                    </div>


                    {{-- Notice Footer --}}
                    <div class="hero-notice-footer">
                        <span>
                            <i class="bi bi-info-circle me-1"></i>
                            সকল গুরুত্বপূর্ণ তথ্য এখানে প্রকাশিত হবে।
                        </span>
                    </div>

                </div>

            </div>

        </div>
    </div>
</section>


{{-- Statistics Section --}}
<!-- <section class="py-5">
    <div class="container">

        <div class="text-center mb-4">
            <h2 class="section-title">প্লটের বর্তমান অবস্থা</h2>

            <p class="text-secondary">
                প্লট ব্যবস্থাপনার সার্বিক পরিসংখ্যান
            </p>
        </div>

        <div class="row g-3">

            <div class="col-6 col-lg-3">
                <div class="stat-card text-center">
                    <div class="text-secondary">মোট প্লট</div>

                    <div class="stat-number">
                        {{ number_format($statistics['total_plots']) }}
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="stat-card text-center">
                    <div class="text-secondary">খালি প্লট</div>

                    <div class="stat-number">
                        {{ number_format($statistics['available_plots']) }}
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="stat-card text-center">
                    <div class="text-secondary">বরাদ্দকৃত প্লট</div>

                    <div class="stat-number">
                        {{ number_format($statistics['allocated_plots']) }}
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="stat-card text-center">
                    <div class="text-secondary">মোট আবেদন</div>

                    <div class="stat-number">
                        {{ number_format($statistics['total_applications']) }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</section> -->


{{-- Applicant Search Section --}}
<!-- <section class="pb-5" id="applicant-search">
    <div class="container">

        <div class="content-card text-center">

            <h2 class="section-title">
                আবেদনকারীর তথ্য অনুসন্ধান
            </h2>

            <p class="text-secondary">
                আপনার আবেদন নম্বর, মোবাইল নম্বর অথবা
                জাতীয় পরিচয়পত্র নম্বর দিয়ে আবেদন খুঁজুন।
            </p>

            <a href="#"
               class="btn btn-primary-custom">
                <i class="bi bi-search me-1"></i>
                আবেদন অনুসন্ধান করুন
            </a>

        </div>

    </div>
</section> -->


{{-- Smart Applicant Search Section --}}
<!-- <section class="applicant-search-section py-5" id="applicant-search">
    <div class="container">

        <div class="applicant-search-card">

            <div class="row align-items-center g-4">

                {{-- Left Information --}}
                <div class="col-lg-5">

                    <div class="search-icon-wrapper mb-3">
                        <i class="bi bi-person-vcard"></i>
                    </div>

                    <h2 class="search-heading">
                        আবেদনকারীর তথ্য অনুসন্ধান
                    </h2>

                    <p class="search-subtitle">
                        আপনার আবেদনকারী নম্বর, মোবাইল নম্বর অথবা
                        জাতীয় পরিচয়পত্র নম্বর দিয়ে সহজেই আবেদন
                        ও লেনদেনের তথ্য খুঁজে নিন।
                    </p>

                    <div class="search-features">
                        <div>
                            <i class="bi bi-check-circle-fill"></i>
                            দ্রুত তথ্য অনুসন্ধান
                        </div>
                        <div>
                            <i class="bi bi-check-circle-fill"></i>
                            আবেদন ও বরাদ্দের তথ্য
                        </div>
                        <div>
                            <i class="bi bi-check-circle-fill"></i>
                            মাসিক ভাড়া ও বকেয়ার হিসাব
                        </div>
                    </div>

                </div>

                {{-- Right Search Form --}}
                <div class="col-lg-7">
                    
                    <form action="{{ route('frontend.applicants.search') }}"
                          method="GET"
                          id="applicantSearchForm">

                        <label for="applicantSearchInput" class="search-label">
                            অনুসন্ধানের তথ্য লিখুন
                        </label>

                        <div class="smart-search-box">

                            <div class="smart-search-icon">
                                <i class="bi bi-search" id="searchTypeIcon"></i>
                            </div>

                            <input
                                type="text"
                                name="q"
                                id="applicantSearchInput"
                                class="smart-search-input"
                                placeholder="আবেদন নম্বর / মোবাইল / NID নম্বর"
                                autocomplete="off"
                                required
                            >

                            <button type="submit" class="smart-search-button">
                                <span>অনুসন্ধান</span>
                                <i class="bi bi-arrow-right"></i>
                            </button>

                        </div>

                        <div class="search-detection mt-2">
                            <span id="searchTypeText">
                                <i class="bi bi-info-circle me-1"></i>
                                যেকোনো একটি তথ্য লিখে অনুসন্ধান করুন।
                            </span>
                        </div>

                        <div class="search-examples mt-3">

                            <span>উদাহরণ:</span>

                            <button type="button"
                                    class="search-example"
                                    data-value="APP-202611-001">
                                APP-202611-001
                            </button>

                            <button type="button"
                                    class="search-example"
                                    data-value="01712345678">
                                01712345678
                            </button>

                        </div>

                    </form>

                    <div class="search-security-note mt-4">
                        <i class="bi bi-shield-lock-fill"></i>
                        আপনার ব্যক্তিগত তথ্য নিরাপদ রাখতে
                        পরিচয় যাচাইয়ের ব্যবস্থা রাখা হবে।
                    </div>

                </div>

            </div>

        </div>

    </div>
</section> -->

{{-- Smart Applicant Search Section --}}
<section class="applicant-search-section py-5" id="applicant-search">
    <div class="container">

        <div class="applicant-search-card">

            <div class="row align-items-center g-4">

                {{-- Left Information --}}
                <div class="col-lg-5">

                    <div class="search-icon-wrapper mb-3">
                        <i class="bi bi-person-vcard"></i>
                    </div>

                    <h2 class="search-heading">
                        আবেদনকারীর তথ্য অনুসন্ধান
                    </h2>

                    <p class="search-subtitle">
                        আপনার আবেদনকারী নম্বর, মোবাইল নম্বর অথবা
                        জাতীয় পরিচয়পত্র নম্বর দিয়ে সহজেই আবেদন
                        ও লেনদেনের তথ্য খুঁজে নিন।
                    </p>

                    <div class="search-features">
                        <div>
                            <i class="bi bi-check-circle-fill"></i>
                            দ্রুত তথ্য অনুসন্ধান
                        </div>

                        <div>
                            <i class="bi bi-check-circle-fill"></i>
                            আবেদন ও বরাদ্দের তথ্য
                        </div>

                        <div>
                            <i class="bi bi-check-circle-fill"></i>
                            মাসিক ভাড়া ও বকেয়ার হিসাব
                        </div>
                    </div>

                </div>

                {{-- Right: Dynamic Search Form --}}
                <div class="col-lg-7">

                    <form
                        action="{{ route('frontend.applications.search') }}"
                        method="GET"
                        id="smartSearchForm"
                        data-application-url="{{ route('frontend.applications.search') }}"
                        data-applicant-url="{{ route('frontend.applicants.search') }}"
                        data-rent-url="{{ route('frontend.daily-rent.search') }}"
                        
                    >

                        {{-- Search Type Radio Buttons --}}
                        <label class="search-label mb-3">
                            অনুসন্ধানের ধরন নির্বাচন করুন
                        </label>

                        <div class="search-type-options mb-4">

                            <label class="search-type-option active"
                                   for="searchApplication">

                                <input
                                    type="radio"
                                    name="search_type"
                                    id="searchApplication"
                                    value="application"
                                    checked
                                >

                                <span>
                                    <i class="bi bi-file-earmark-text"></i>
                                    আবেদন
                                </span>

                            </label>

                            <label class="search-type-option"
                                   for="searchApplicant">

                                <input
                                    type="radio"
                                    name="search_type"
                                    id="searchApplicant"
                                    value="applicant"
                                >

                                <span>
                                    <i class="bi bi-person-vcard"></i>
                                    আবেদনকারী অনুসন্ধান
                                </span>

                            </label>

                            <label class="search-type-option"
                                   for="searchRent">

                                <input
                                    type="radio"
                                    name="search_type"
                                    id="searchRent"
                                    value="rent"
                                >

                                <span>
                                    <i class="bi bi-wallet2"></i>
                                    ডেইলি রেন্ট
                                </span>

                            </label>

                        </div>

                        {{-- Dynamic Search Input --}}
                        <label for="smartSearchInput" class="search-label">
                            <span id="searchInputLabel">
                                আবেদন নম্বর লিখুন
                            </span>
                        </label>

                        <div class="smart-search-box">

                            <div class="smart-search-icon">
                                <i class="bi bi-search"
                                   id="smartSearchIcon"></i>
                            </div>

                            <input
                                type="text"
                                name="application_no"
                                id="smartSearchInput"
                                class="smart-search-input"
                                placeholder="যেমন: PLA-202611-0001"
                                autocomplete="off"
                                required
                            >

                            <button type="submit"
                                    class="smart-search-button">

                                <span>অনুসন্ধান</span>
                                <i class="bi bi-arrow-right"></i>

                            </button>

                        </div>

                        <div class="search-detection mt-2"
                             id="searchHelpText">

                            <i class="bi bi-info-circle me-1"></i>
                            আপনার আবেদন নম্বর দিয়ে অনুসন্ধান করুন।

                        </div>

                    </form>

                    <div class="search-security-note mt-4">
                        <i class="bi bi-shield-lock-fill"></i>

                        আপনার ব্যক্তিগত তথ্য নিরাপদ রাখতে
                        পরিচয় যাচাইয়ের ব্যবস্থা রাখা হবে।
                    </div>

                </div>

            </div>

        </div>

    </div>
</section>


@endsection


{{-- Hero Notice Board Styles --}}
@push('styles')
<style>

    .hero-notice-card {
        background: #ffffff;
        color: #263238;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 15px 40px rgba(0,0,0,.15);
    }

    .hero-notice-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px;
        background: #f0f8f6;
        border-bottom: 1px solid #e5eeeb;
    }

    .hero-notice-header h4 {
        color: #075e54;
        font-weight: bold;
        font-size: 22px;
    }

    .hero-notice-header small {
        color: #667085;
    }

    .notice-icon {
        width: 34px;
        height: 34px;
        display: grid;
        place-items: center;
        background: #075e54;
        color: #ffffff;
        border-radius: 5px;
        /* border-color: #075e54; */
        /* border: 2px solid; */
        font-size: 20px;
    }

    .notice-live-badge {
        display: flex;
        align-items: center;
        gap: 6px;
        background: #e3f6ec;
        color: #16834a;
        border-radius: 30px;
        padding: 5px 10px;
        font-size: 12px;
        font-weight: bold;
    }

    .live-dot {
        width: 7px;
        height: 7px;
        background: #16a34a;
        border-radius: 50%;
    }

    .hero-notice-list {
        padding: 5px 20px;
        max-height: 355px;
        overflow-y: auto;
    }

    .hero-notice-item {
        padding: 17px 0;
        border-bottom: 1px solid #edf0f3;
    }

    .hero-notice-item:last-child {
        border-bottom: none;
    }

    .notice-date {
        min-width: 48px;
        height: 55px;
        background: #eaf5f2;
        color: #075e54;
        border-radius: 9px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .notice-date span {
        font-size: 21px;
        line-height: 1.1;
        font-weight: bold;
    }

    .notice-date small {
        font-size: 11px;
    }

    .notice-important {
        background: #fff0f0;
        color: #d92d20;
        padding: 2px 8px;
        border-radius: 5px;
        font-size: 11px;
    }

    .notice-title {
        color: #263238;
        font-weight: bold;
        font-size: 16px;
        line-height: 1.5;
    }

    .notice-description {
        color: #0e0f0f !important;
        font-size: 14px !important;
        margin-bottom: 5px;
        line-height: 1.6;
    }

    .notice-attachment {
        color: #075e54;
        font-size: 13px;
        text-decoration: none;
        font-weight: bold;
    }

    .notice-attachment:hover {
        text-decoration: underline;
    }

    .notice-empty {
        text-align: center;
        padding: 45px 10px;
        color: #98a2b3;
    }

    .notice-empty i {
        font-size: 35px;
    }

    .notice-empty h6 {
        color: #475467;
        margin-top: 12px;
    }

    .notice-empty p {
        font-size: 14px;
    }

    .hero-notice-footer {
        background: #f8faf9;
        border-top: 1px solid #edf0f3;
        padding: 12px 20px;
        color: #667085;
        font-size: 13px;
    }

    /* Applicant Search Section */

    /* .applicant-search-section {
        background: #f5f8f7;
    }

    .applicant-search-card {
        background: #ffffff;
        border: 1px solid #e8eeeb;
        border-radius: 18px;
        padding: 35px;
        box-shadow: 0 10px 35px rgba(0, 0, 0, .05);
    }

    .search-icon-wrapper {
        width: 55px;
        height: 55px;
        display: grid;
        place-items: center;
        background: #e8f5f1;
        color: #075e54;
        border-radius: 13px;
        font-size: 27px;
    }

    .search-heading {
        color: #075e54;
        font-size: 25px;
        font-weight: 700;
        margin-bottom: 12px;
    }

    .search-subtitle {
        color: #667085;
        font-size: 15px;
        line-height: 1.8;
    }

    .search-features {
        display: grid;
        gap: 11px;
        margin-top: 20px;
        color: #344054;
        font-size: 14px;
    }

    .search-features i {
        color: #16a36a;
        margin-right: 7px;
    }

    .search-label {
        display: block;
        color: #344054;
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 10px;
    }

    .smart-search-box {
        display: flex;
        align-items: center;
        background: #ffffff;
        border: 1px solid #d0d9d5;
        border-radius: 11px;
        padding: 6px;
        transition: .2s ease;
    }

    .smart-search-box:focus-within {
        border-color: #07856f;
        box-shadow: 0 0 0 4px rgba(7, 133, 111, .10);
    }

    .smart-search-icon {
        color: #07856f;
        font-size: 20px;
        padding: 0 12px;
    }

    .smart-search-input {
        flex: 1;
        min-width: 0;
        border: none;
        outline: none;
        padding: 12px 5px;
        font-size: 15px;
        color: #263238;
    }

    .smart-search-input::placeholder {
        color: #98a2b3;
        font-size: 14px;
    }

    .smart-search-button {
        display: flex;
        align-items: center;
        gap: 9px;
        border: none;
        border-radius: 8px;
        background: #075e54;
        color: #ffffff;
        padding: 12px 20px;
        font-weight: 600;
        white-space: nowrap;
        transition: .2s ease;
    }

    .smart-search-button:hover {
        background: #064b44;
    }

    .search-detection {
        color: #667085;
        font-size: 13px;
    }

    .search-examples {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        color: #667085;
        font-size: 12px;
    }

    .search-example {
        border: 1px solid #dce8e3;
        background: #f5faf8;
        color: #075e54;
        border-radius: 5px;
        padding: 5px 9px;
        font-size: 12px;
    }

    .search-example:hover {
        background: #e5f3ee;
    }

    .search-security-note {
        display: flex;
        align-items: center;
        gap: 9px;
        color: #667085;
        font-size: 13px;
    }

    .search-security-note i {
        color: #07856f;
        font-size: 17px;
    } */

/* Smart Applicant Search Section */

.applicant-search-section {
    background: #f5f8f7;
}

.applicant-search-card {
    background: #ffffff;
    border: 1px solid #e8eeeb;
    border-radius: 18px;
    padding: 35px;
    box-shadow: 0 10px 35px rgba(0, 0, 0, .05);
}

.search-icon-wrapper {
    width: 55px;
    height: 55px;
    display: grid;
    place-items: center;
    background: #e8f5f1;
    color: #075e54;
    border-radius: 13px;
    font-size: 27px;
}

.search-heading {
    color: #075e54;
    font-size: 25px;
    font-weight: 700;
    margin-bottom: 12px;
}

.search-subtitle {
    color: #667085;
    font-size: 15px;
    line-height: 1.8;
}

.search-features {
    display: grid;
    gap: 11px;
    margin-top: 20px;
    color: #344054;
    font-size: 14px;
}

.search-features i {
    color: #16a36a;
    margin-right: 7px;
}

.search-label {
    display: block;
    color: #344054;
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 10px;
}

/* Search Type Radio Options */

.search-type-options {
    display: flex;
    flex-wrap: wrap;
    gap: 9px;
}

.search-type-option {
    position: relative;
    cursor: pointer;
    margin: 0;
}

.search-type-option input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

.search-type-option span {
    display: flex;
    align-items: center;
    gap: 7px;
    padding: 11px 13px;
    border: 1px solid #dce5e1;
    border-radius: 8px;
    background: #ffffff;
    color: #475467;
    font-size: 13px;
    font-weight: 600;
    transition: .2s ease;
    white-space: nowrap;
}

.search-type-option span i {
    font-size: 16px;
}

.search-type-option.active span {
    background: #e8f5f1;
    border-color: #07856f;
    color: #075e54;
}

.search-type-option:hover span {
    border-color: #07856f;
}

.search-type-option input:focus-visible + span {
    outline: 3px solid rgba(7, 133, 111, .2);
}

/* Smart Search Box */

.smart-search-box {
    display: flex;
    align-items: center;
    background: #ffffff;
    border: 1px solid #d0d9d5;
    border-radius: 11px;
    padding: 6px;
    transition: .2s ease;
}

.smart-search-box:focus-within {
    border-color: #07856f;
    box-shadow: 0 0 0 4px rgba(7, 133, 111, .10);
}

.smart-search-icon {
    color: #07856f;
    font-size: 20px;
    padding: 0 12px;
}

.smart-search-input {
    flex: 1;
    min-width: 0;
    border: none;
    outline: none;
    padding: 12px 5px;
    font-size: 15px;
    color: #263238;
}

.smart-search-input::placeholder {
    color: #98a2b3;
    font-size: 14px;
}

.smart-search-button {
    display: flex;
    align-items: center;
    gap: 9px;
    border: none;
    border-radius: 8px;
    background: #075e54;
    color: #ffffff;
    padding: 12px 20px;
    font-weight: 600;
    white-space: nowrap;
    transition: .2s ease;
}

.smart-search-button:hover {
    background: #064b44;
}

.search-detection {
    color: #667085;
    font-size: 13px;
}

.search-security-note {
    display: flex;
    align-items: center;
    gap: 9px;
    color: #667085;
    font-size: 13px;
}

.search-security-note i {
    color: #07856f;
    font-size: 17px;
}

/* Responsive */

@media(max-width: 991px) {
    .search-type-options {
        gap: 8px;
    }
}

@media(max-width: 767px) {
    .applicant-search-card {
        padding: 22px 17px;
    }

    .search-heading {
        font-size: 22px;
    }

    .search-type-options {
        display: grid;
        grid-template-columns: 1fr;
    }

    .search-type-option span {
        width: 100%;
    }

    .smart-search-box {
        flex-wrap: wrap;
    }

    .smart-search-input {
        width: calc(100% - 55px);
    }

    .smart-search-button {
        width: 100%;
        justify-content: center;
        margin-top: 5px;
    }
}

    

    @media(max-width: 767px) {
        .applicant-search-card {
            padding: 22px 17px;
        }

        .search-heading {
            font-size: 22px;
        }

        .smart-search-box {
            flex-wrap: wrap;
        }

        .smart-search-input {
            width: calc(100% - 55px);
        }

        .smart-search-button {
            width: 100%;
            justify-content: center;
            margin-top: 5px;
        }
    }

    @media(max-width: 991px) {
        .hero-notice-card {
            margin-top: 10px;
        }
    }

</style>
@endpush
@push('scripts')
<!-- <script>
    document.addEventListener('DOMContentLoaded', function () {

        const input = document.getElementById('applicantSearchInput');
        const typeText = document.getElementById('searchTypeText');
        const typeIcon = document.getElementById('searchTypeIcon');

        if (!input || !typeText || !typeIcon) return;

        input.addEventListener('input', function () {

            const value = this.value.trim();

            if (!value) {
                typeText.innerHTML =
                    '<i class="bi bi-info-circle me-1"></i> যেকোনো একটি তথ্য লিখে অনুসন্ধান করুন।';
                typeIcon.className = 'bi bi-search';
                return;
            }

            if (/^APP-/i.test(value)) {

                typeText.innerHTML =
                    '<i class="bi bi-check-circle-fill text-success me-1"></i> আবেদনকারী নম্বর দিয়ে অনুসন্ধান করা হচ্ছে।';

                typeIcon.className = 'bi bi-person-badge';

            } else if (/^01\d{9}$/.test(value)) {

                typeText.innerHTML =
                    '<i class="bi bi-check-circle-fill text-success me-1"></i> মোবাইল নম্বর দিয়ে অনুসন্ধান করা হচ্ছে।';

                typeIcon.className = 'bi bi-phone';

            } else if (/^\d{10}$|^\d{13}$|^\d{17}$/.test(value)) {

                typeText.innerHTML =
                    '<i class="bi bi-check-circle-fill text-success me-1"></i> জাতীয় পরিচয়পত্র নম্বর দিয়ে অনুসন্ধান করা হচ্ছে।';

                typeIcon.className = 'bi bi-person-vcard';

            } else {

                typeText.innerHTML =
                    '<i class="bi bi-info-circle me-1"></i> আবেদন নম্বর, মোবাইল অথবা NID নম্বর লিখুন।';

                typeIcon.className = 'bi bi-search';
            }
        });

        document.querySelectorAll('.search-example').forEach(function (button) {

            button.addEventListener('click', function () {
                input.value = this.dataset.value;
                input.dispatchEvent(new Event('input'));
                input.focus();
            });

        });

    });
</script> -->
<script>

document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('smartSearchForm');
    const input = document.getElementById('smartSearchInput');
    const inputLabel = document.getElementById('searchInputLabel');
    const searchIcon = document.getElementById('smartSearchIcon');
    const helpText = document.getElementById('searchHelpText');

    const options = document.querySelectorAll(
        'input[name="search_type"]'
    );

    if (!form || !input) return;

    const configurations = {
        application: {
            url: form.dataset.applicationUrl,
            name: 'application_no',
            label: 'আবেদন নম্বর লিখুন',
            placeholder: 'যেমন: PLA-202611-0001',
            help: 'আপনার আবেদন নম্বর দিয়ে অনুসন্ধান করুন।',
            icon: 'bi-file-earmark-text'
        },

        applicant: {
            url: form.dataset.applicantUrl,
            name: 'q',
            label: 'আবেদনকারী নম্বর / মোবাইল / NID লিখুন',
            placeholder: 'আবেদনকারী নম্বর / মোবাইল / NID',
            help: 'আবেদনকারী নম্বর, মোবাইল অথবা NID দিয়ে অনুসন্ধান করুন।',
            icon: 'bi-person-vcard'
        },

        rent: {
            url: form.dataset.rentUrl,
            name: 'plot_number',
            label: 'প্লট নম্বর লিখুন',
            placeholder: 'প্লট নম্বর লিখুন',
            help: 'প্লট নম্বর দিয়ে দৈনিক ভাড়া ও বকেয়ার তথ্য অনুসন্ধান করুন।',
            icon: 'bi-wallet2'
        }
    };

    function updateSearchType(type) {

        const config = configurations[type];

        if (!config) return;

        form.action = config.url;

        input.name = config.name;
        input.placeholder = config.placeholder;

        inputLabel.textContent = config.label;
        searchIcon.className = 'bi ' + config.icon;

        helpText.innerHTML =
            '<i class="bi bi-info-circle me-1"></i> ' +
            config.help;

        options.forEach(function (radio) {

            radio.closest('.search-type-option')
                .classList.toggle('active', radio.checked);

        });

        input.value = '';
    }

    options.forEach(function (radio) {

        radio.addEventListener('change', function () {

            if (this.checked) {
                updateSearchType(this.value);
            }

        });

    });

    updateSearchType('application');

});

</script>
@endpush
