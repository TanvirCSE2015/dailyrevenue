
@extends('frontend.layouts.app')

@section('title', 'নতুন আবেদন | প্লট ব্যবস্থাপনা সিস্টেম')

@section('content')

<section class="py-5">
    <div class="container">

        <div class="text-center mb-4">
            <h2 class="section-title">নতুন আবেদন</h2>
            <p class="text-secondary">
                আবেদন করার জন্য প্রয়োজনীয় তথ্য পূরণ করুন।
            </p>
        </div>

        <div class="application-wrapper">

            <form id="applicationForm"
                method="POST"
                enctype="multipart/form-data"
                novalidate>
                {{-- Applicant Type --}}
                <div class="application-section">
                    <h5 class="section-heading">আবেদনকারীর ধরন নির্বাচন করুন</h5>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="applicant-type-card active" for="newApplicant">
                                <input type="radio" name="applicant_type"
                                    id="newApplicant" value="new" checked>
                                <span>
                                    <strong>প্রথমবার আবেদন</strong>
                                    <small>নতুন আবেদনকারী হিসেবে নিবন্ধন করুন</small>
                                </span>
                            </label>
                        </div>

                        <div class="col-md-6">
                            <label class="applicant-type-card" for="existingApplicant">
                                <input type="radio" name="applicant_type"
                                    id="existingApplicant" value="existing">
                                <span>
                                    <strong>পূর্বে নিবন্ধিত আবেদনকারী</strong>
                                    <small>Applicant Number দিয়ে নতুন আবেদন করুন</small>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>


                {{-- Existing Applicant --}}
                <div class="application-section d-none" id="existingApplicantSection">
                    <h5 class="section-heading">আবেদনকারীর পরিচয়</h5>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Applicant Number</label>
                            <input type="text" class="form-control"
                                name="applicant_no"
                                placeholder="যেমন: APP-202611-001"
                                disabled>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">নিবন্ধিত মোবাইল নম্বর</label>
                            <input type="tel" class="form-control"
                                name="existing_mobile"
                                placeholder="01XXXXXXXXX"
                                disabled>
                        </div>
                    </div>

                    <div class="alert alert-info mt-3 mb-0">
                        Applicant Number ও নিবন্ধিত মোবাইল নম্বর মিলিয়ে
                        আবেদনকারীর পরিচয় যাচাই করা হবে।
                    </div>
                </div>


                {{-- New Applicant Information --}}
                <div class="application-section" id="newApplicantSection">

                    <h5 class="section-heading">ব্যক্তিগত তথ্য</h5>

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">আবেদনকারীর নাম <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control"
                                placeholder="সম্পূর্ণ নাম" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">পিতার নাম <span class="text-danger">*</span></label>
                            <input type="text" name="father_name" class="form-control"
                                required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">মাতার নাম <span class="text-danger">*</span></label>
                            <input type="text" name="mother_name" class="form-control"
                                required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">মোবাইল নম্বর <span class="text-danger">*</span></label>
                            <input type="tel" name="mobile" class="form-control"
                                placeholder="01XXXXXXXXX" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">জাতীয় পরিচয়পত্র নম্বর <span class="text-danger">*</span></label>
                            <input type="text" name="nid" class="form-control"
                                required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">আবেদনকারীর ছবি <span class="text-danger">*</span></label>
                            <input type="file" name="image" class="form-control"
                                accept="image/jpeg,image/png,image/webp" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">NID-এর সামনের অংশ <span class="text-danger">*</span></label>
                            <input type="file" name="nid_front" class="form-control"
                                accept="image/jpeg,image/png,image/webp" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">NID-এর পেছনের অংশ <span class="text-danger">*</span></label>
                            <input type="file" name="nid_back" class="form-control"
                                accept="image/jpeg,image/png,image/webp" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">বর্তমান ঠিকানা <span class="text-danger">*</span></label>
                            <textarea name="present_address" class="form-control"
                                    rows="3" required></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">স্থায়ী ঠিকানা <span class="text-danger">*</span></label>
                            <textarea name="permanent_address" class="form-control"
                                    rows="3" required></textarea>
                        </div>

                    </div>
                </div>


                {{-- Plot Selection --}}
                <div class="application-section">
                    <h5 class="section-heading">প্লট নির্বাচন</h5>

                    <div class="row g-3">

                        <div class="col-md-8">
                            <label class="form-label">
                                পছন্দের খালি প্লট <span class="text-danger">*</span>
                            </label>

                            <select name="plot_id" class="form-select" required>
                                <option value="">প্লট নির্বাচন করুন</option>

                                @foreach($plots as $plot)
                                    <option value="{{ $plot->id }}">
                                        {{ $plot->plot_number }}
                                        — {{ $plot->name }}
                                        ({{ $plot->length }} × {{ $plot->width }} ফুট)
                                    </option>
                                @endforeach
                            </select>

                            @if($plots->isEmpty())
                                <div class="text-danger small mt-2">
                                    বর্তমানে কোনো খালি প্লট নেই।
                                </div>
                            @endif
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">আবেদন ফি</label>
                            <input type="text" class="form-control"
                                value="৳ ১০০.০০" readonly>
                        </div>

                    </div>
                </div>


                {{-- Application Fee Information --}}
                <div class="application-section fee-section">
                    <h5 class="section-heading">আবেদন ফি সংক্রান্ত ঘোষণা</h5>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox"
                            id="feeAgreement" required>

                        <label class="form-check-label" for="feeAgreement">
                            আমি অবগত আছি যে আবেদন ফি ১০০ টাকা এবং এই ফি
                            কোনো অবস্থাতেই ফেরতযোগ্য নয়।
                        </label>
                    </div>
                </div>


                <div class="text-center">
                    <button type="button" class="btn btn-primary-custom btn-lg"
                            disabled>
                        আবেদন জমা দেওয়ার ব্যবস্থা পরবর্তী ধাপে চালু হবে
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

@endsection


@push('styles')
<style>
    .application-wrapper {
        max-width: 950px;
        margin: auto;
    }

    .application-section {
        background: #fff;
        border: 1px solid #e9eef2;
        border-radius: 14px;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,.025);
    }

    .section-heading {
        color: #075e54;
        font-weight: bold;
        padding-bottom: 14px;
        margin-bottom: 20px;
        border-bottom: 1px solid #edf0f3;
    }

    .form-label {
        font-weight: 600;
        color: #344054;
    }

    .form-control,
    .form-select {
        min-height: 46px;
        border-color: #d0d5dd;
        border-radius: 8px;
    }

    textarea.form-control {
        min-height: 90px;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #075e54;
        box-shadow: 0 0 0 .2rem rgba(7,94,84,.1);
    }

    .applicant-type-card {
        display: flex;
        align-items: center;
        gap: 12px;
        border: 1px solid #d0d5dd;
        padding: 18px;
        border-radius: 10px;
        cursor: pointer;
        height: 100%;
    }

    .applicant-type-card.active {
        border-color: #075e54;
        background: #f0f8f6;
    }

    .applicant-type-card strong,
    .applicant-type-card small {
        display: block;
    }

    .applicant-type-card strong {
        color: #075e54;
    }

    .applicant-type-card small {
        color: #667085;
        margin-top: 4px;
    }

    .fee-section {
        background: #f0f8f6;
    }

    @media(max-width: 767px) {
        .application-section {
            padding: 18px;
        }
    }
</style>
@endpush


@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {

        const newRadio = document.getElementById('newApplicant');
        const existingRadio = document.getElementById('existingApplicant');

        const newSection = document.getElementById('newApplicantSection');
        const existingSection = document.getElementById('existingApplicantSection');

        const newCard = newRadio.closest('.applicant-type-card');
        const existingCard = existingRadio.closest('.applicant-type-card');

        function updateApplicantType() {
            const isNew = newRadio.checked;

            newSection.classList.toggle('d-none', !isNew);
            existingSection.classList.toggle('d-none', isNew);

            newCard.classList.toggle('active', isNew);
            existingCard.classList.toggle('active', !isNew);

            newSection.querySelectorAll('input, textarea').forEach(field => {
                field.disabled = !isNew;
            });

            existingSection.querySelectorAll('input').forEach(field => {
                field.disabled = isNew;
            });

            newSection.querySelectorAll('[required]').forEach(field => {
                field.required = isNew;
            });

            existingSection.querySelectorAll('input').forEach(field => {
                field.required = !isNew;
            });
        }

        newRadio.addEventListener('change', updateApplicantType);
        existingRadio.addEventListener('change', updateApplicantType);

        updateApplicantType();
    });
</script>
@endpush
