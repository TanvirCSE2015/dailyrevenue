
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
<section class="py-5">
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
</section>


{{-- Applicant Search Section --}}
<section class="pb-5" id="applicant-search">
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

    @media(max-width: 991px) {
        .hero-notice-card {
            margin-top: 10px;
        }
    }

</style>
@endpush
