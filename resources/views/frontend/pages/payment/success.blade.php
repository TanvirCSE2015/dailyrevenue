<!-- @extends('frontend.layouts.app')

@section('title', 'পেমেন্ট | প্লট ব্যবস্থাপনা সিস্টেম')

@section('content')
<div class="container py-5">
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
</div>

@endsection -->


@extends('frontend.layouts.app')

@section('title', 'পেমেন্ট সফল | প্লট ব্যবস্থাপনা সিস্টেম')

@section('content')

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

            <p class="text-muted mb-2">
                আপনার আবেদন ফি সফলভাবে গ্রহণ করা হয়েছে।
            </p>

            <span class="badge bg-success px-3 py-2">
                আবেদন জমা হয়েছে
            </span>

        </div>
    </div>

    {{-- Application Information --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-file-earmark-text me-2"></i>
                আবেদনের তথ্য
            </h5>
        </div>

        <div class="card-body">

            <div class="row g-3">

                <div class="col-md-6">
                    <small class="text-muted">আবেদন নম্বর</small>
                    <div class="fw-bold">
                        {{ $application->application_no }}
                    </div>
                </div>

                <div class="col-md-6">
                    <small class="text-muted">আবেদনকারীর নম্বর</small>
                    <div class="fw-bold">
                        {{ $application->applicant->applicant_no }}
                    </div>
                </div>

                <div class="col-md-6">
                    <small class="text-muted">আবেদনের তারিখ</small>
                    <div class="fw-bold">
                        {{ $application->applied_at?->format('d-m-Y') ?? $application->applied_at }}
                    </div>
                </div>

                <div class="col-md-6">
                    <small class="text-muted">আবেদন ফি</small>
                    <div class="fw-bold">
                        ৳{{ number_format((float) $payment->amount, 2) }}
                    </div>
                </div>

                <div class="col-md-6">
                    <small class="text-muted">পেমেন্ট নম্বর</small>
                    <div class="fw-bold">
                        {{ $payment->payment_no }}
                    </div>
                </div>

                <div class="col-md-6">
                    <small class="text-muted">পেমেন্ট স্ট্যাটাস</small>
                    <div>
                        <span class="badge bg-success">সফল</span>
                    </div>
                </div>

            </div>

        </div>
    </div>

    {{-- Applicant Information --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-person-vcard me-2"></i>
                আবেদনকারীর তথ্য
            </h5>
        </div>

        <div class="card-body">

            <div class="row g-3">

                <div class="col-md-6">
                    <small class="text-muted">আবেদনকারীর নাম</small>
                    <div class="fw-bold">
                        {{ $application->applicant->name }}
                    </div>
                </div>

                <div class="col-md-6">
                    <small class="text-muted">পিতার নাম</small>
                    <div class="fw-bold">
                        {{ $application->applicant->father_name }}
                    </div>
                </div>

                <div class="col-md-6">
                    <small class="text-muted">মাতার নাম</small>
                    <div class="fw-bold">
                        {{ $application->applicant->mother_name }}
                    </div>
                </div>

                <div class="col-md-6">
                    <small class="text-muted">মোবাইল নম্বর</small>
                    <div class="fw-bold">
                        {{ $application->applicant->mobile }}
                    </div>
                </div>

                <div class="col-md-6">
                    <small class="text-muted">NID নম্বর</small>
                    <div class="fw-bold">
                        {{ $application->applicant->nid }}
                    </div>
                </div>

                <div class="col-md-6">
                    <small class="text-muted">বর্তমান ঠিকানা</small>
                    <div class="fw-bold">
                        {{ $application->applicant->present_address }}
                    </div>
                </div>

                <div class="col-md-12">
                    <small class="text-muted">স্থায়ী ঠিকানা</small>
                    <div class="fw-bold">
                        {{ $application->applicant->permanent_address }}
                    </div>
                </div>

            </div>

        </div>
    </div>

    {{-- Plot Information --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-geo-alt me-2"></i>
                নির্বাচিত প্লটের তথ্য
            </h5>
        </div>

        <div class="card-body">

            <div class="row g-3">

                <div class="col-md-4">
                    <small class="text-muted">প্লট নম্বর</small>
                    <div class="fw-bold">
                        {{ $application->plot->plot_number }}
                    </div>
                </div>

                <div class="col-md-4">
                    <small class="text-muted">অবস্থান</small>
                    <div class="fw-bold">
                        {{ $application->plot->location }}
                    </div>
                </div>

                <div class="col-md-4">
                    <small class="text-muted">প্লটের আয়তন</small>
                    <div class="fw-bold">
                        {{ $application->plot->length }} × {{ $application->plot->width }}
                    </div>
                </div>

            </div>

        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="d-flex flex-wrap justify-content-center gap-2 mb-4">

        <button type="button"
            onclick="window.print()"
            class="btn btn-primary px-4">
            <i class="bi bi-printer me-2"></i>
            প্রিন্ট আবেদন
        </button>

        <a href="{{ route('home') }}"
            class="btn btn-outline-secondary px-4">
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
    }
</style>
@endpush
