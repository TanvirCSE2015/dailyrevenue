
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Common Labels
    |--------------------------------------------------------------------------
    */

    'common' => [
        'id' => 'আইডি',
        'name' => 'নাম',
        'title' => 'শিরোনাম',
        'description' => 'বিবরণ',
        'remarks' => 'মন্তব্য',
        'reason' => 'কারণ',
        'status' => 'অবস্থা',
        'type' => 'ধরন',
        'amount' => 'পরিমাণ',
        'date' => 'তারিখ',
        'start_date' => 'শুরুর তারিখ',
        'end_date' => 'শেষ তারিখ',
        'created_at' => 'তৈরির তারিখ',
        'updated_at' => 'সর্বশেষ আপডেট',
        'deleted_at' => 'মুছে ফেলার তারিখ',
        'created_by' => 'তৈরিকারী',
        'updated_by' => 'আপডেটকারী',
        'user' => 'ব্যবহারকারী',
        'actions' => 'কার্যক্রম',
        'details' => 'বিস্তারিত',
        'history' => 'ইতিহাস',
        'attachment' => 'সংযুক্তি',
        'image' => 'ছবি',
        'document' => 'ডকুমেন্ট',
        'active' => 'সক্রিয়',
        'inactive' => 'নিষ্ক্রিয়',
        'yes' => 'হ্যাঁ',
        'no' => 'না',
        'all' => 'সকল',
        'none' => 'কোনোটিই নয়',
        'select' => 'নির্বাচন করুন',
        'search' => 'অনুসন্ধান করুন',
        'filter' => 'ফিল্টার',
        'reset' => 'রিসেট',
        'save' => 'সংরক্ষণ করুন',
        'cancel' => 'বাতিল করুন',
        'delete' => 'মুছে ফেলুন',
        'edit' => 'সম্পাদনা',
        'view' => 'দেখুন',
        'create' => 'নতুন তৈরি করুন',
        'approve' => 'অনুমোদন',
        'reject' => 'প্রত্যাখ্যান',
        'pending' => 'অপেক্ষমাণ',
        'verified' => 'যাচাইকৃত',
        'approved' => 'অনুমোদিত',
        'rejected' => 'প্রত্যাখ্যাত',
        'not_available' => 'তথ্য পাওয়া যায়নি',
    ],

    /*
    |--------------------------------------------------------------------------
    | 01. Applicant - applicants
    |--------------------------------------------------------------------------
    */

    'applicant' => [
        'navigation' => 'আবেদনকারী',
        'model' => 'আবেদনকারী',
        'plural_model' => 'আবেদনকারীগণ',

        'applicant_no' => 'আবেদনকারী নম্বর',
        'name' => 'আবেদনকারীর নাম',
        'father_name' => 'পিতার নাম',
        'mother_name' => 'মাতার নাম',
        'present_address' => 'বর্তমান ঠিকানা',
        'permanent_address' => 'স্থায়ী ঠিকানা',
        'mobile' => 'মোবাইল নম্বর',
        'nid' => 'জাতীয় পরিচয়পত্র নম্বর',
        'is_active' => 'সক্রিয় অবস্থা',
        'created_by' => 'তথ্য এন্ট্রিকারী',

        'sections' => [
            'personal' => 'আবেদনকারীর ব্যক্তিগত তথ্য',
            'address' => 'ঠিকানার তথ্য',
            'contact' => 'যোগাযোগের তথ্য',
            'status' => 'আবেদনকারীর অবস্থা',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 02. Plot - plots
    |--------------------------------------------------------------------------
    */

    'plot' => [
        'navigation' => 'প্লট',
        'model' => 'প্লট',
        'plural_model' => 'প্লটসমূহ',

        'plot_number' => 'প্লট নম্বর',
        'name' => 'প্লটের নাম',
        'location' => 'প্লটের অবস্থান',
        'length' => 'দৈর্ঘ্য',
        'width' => 'প্রস্থ',
        'status' => 'প্লটের অবস্থা',
        'description' => 'প্লটের বিবরণ',
        'created_by' => 'তৈরিকারী',

        'sections' => [
            'information' => 'প্লটের তথ্য',
            'measurement' => 'প্লটের পরিমাপ',
            'status' => 'প্লটের বর্তমান অবস্থা',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 03. PlotApplication - plot_applications
    |--------------------------------------------------------------------------
    */

    'plot_application' => [
        'navigation' => 'প্লট আবেদন',
        'model' => 'প্লট আবেদন',
        'plural_model' => 'প্লটের আবেদনসমূহ',

        'application_no' => 'আবেদন নম্বর',
        'applicant_id' => 'আবেদনকারী',
        'plot_id' => 'প্লট',
        'applied_at' => 'আবেদনের তারিখ',
        'application_fee_amount' => 'আবেদন ফি',
        'status' => 'আবেদনের অবস্থা',
        'notes' => 'অতিরিক্ত তথ্য',
        'applicant' => 'আবেদনকারী',
        'plot' => 'আবেদনকৃত প্লট',

        'sections' => [
            'information' => 'আবেদনের তথ্য',
            'applicant' => 'আবেদনকারীর তথ্য',
            'plot' => 'প্লটের তথ্য',
            'fee' => 'আবেদন ফি',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 04. ApplicationReview - application_reviews
    |--------------------------------------------------------------------------
    */

    'application_review' => [
        'navigation' => 'আবেদন যাচাই',
        'model' => 'আবেদন যাচাই',
        'plural_model' => 'আবেদন যাচাইসমূহ',

        'application_id' => 'আবেদন নম্বর',
        'reviewer_id' => 'যাচাইকারী কর্মকর্তা',
        'decision' => 'সিদ্ধান্ত',
        'remarks' => 'যাচাই সংক্রান্ত মন্তব্য',
        'reviewed_at' => 'যাচাইয়ের তারিখ ও সময়',

        'sections' => [
            'review' => 'আবেদন যাচাইয়ের তথ্য',
            'decision' => 'যাচাইয়ের সিদ্ধান্ত',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 05. PlotAllocation - plot_allocations
    |--------------------------------------------------------------------------
    */

    'plot_allocation' => [
        'navigation' => 'প্লট বরাদ্দ',
        'model' => 'প্লট বরাদ্দ',
        'plural_model' => 'প্লট বরাদ্দসমূহ',

        'application_id' => 'আবেদন',
        'applicant_id' => 'আবেদনকারী',
        'plot_id' => 'বরাদ্দকৃত প্লট',
        'active_applicant_id' => 'বর্তমান বরাদ্দপ্রাপ্ত আবেদনকারী',
        'active_plot_id' => 'বর্তমান বরাদ্দকৃত প্লট',
        'allocated_by' => 'বরাদ্দ প্রদানকারী',
        'allocated_at' => 'বরাদ্দের তারিখ ও সময়',
        'start_date' => 'বরাদ্দ শুরুর তারিখ',
        'end_date' => 'বরাদ্দ শেষের তারিখ',
        'status' => 'বরাদ্দের অবস্থা',
        'remarks' => 'বরাদ্দ সংক্রান্ত মন্তব্য',

        'sections' => [
            'information' => 'বরাদ্দের তথ্য',
            'applicant' => 'আবেদনকারীর তথ্য',
            'plot' => 'প্লটের তথ্য',
            'period' => 'বরাদ্দের মেয়াদ',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 06. RateHistory - rate_histories
    |--------------------------------------------------------------------------
    */

    'rate_history' => [
        'navigation' => 'রেট ও চার্জ',
        'model' => 'রেট',
        'plural_model' => 'রেটসমূহ',

        'rate_type' => 'রেটের ধরন',
        'amount' => 'নির্ধারিত পরিমাণ',
        'effective_from' => 'কার্যকর হওয়ার তারিখ',
        'effective_until' => 'কার্যকারিতার শেষ তারিখ',
        'description' => 'বিবরণ',
        'created_by' => 'নির্ধারণকারী',

        'sections' => [
            'information' => 'রেটের তথ্য',
            'validity' => 'কার্যকারিতার মেয়াদ',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 07. Payment - payments
    |--------------------------------------------------------------------------
    */

    'payment' => [
        'navigation' => 'পেমেন্ট',
        'model' => 'পেমেন্ট',
        'plural_model' => 'পেমেন্টসমূহ',

        'payment_no' => 'পেমেন্ট নম্বর',
        'applicant_id' => 'আবেদনকারী',
        'application_id' => 'আবেদন',
        'allocation_id' => 'প্লট বরাদ্দ',
        'payment_for' => 'পেমেন্টের উদ্দেশ্য',
        'gateway' => 'পেমেন্ট মাধ্যম',
        'merchant_invoice_id' => 'মার্চেন্ট ইনভয়েস নম্বর',
        'gateway_transaction_id' => 'গেটওয়ে ট্রানজেকশন নম্বর',
        'amount' => 'পেমেন্টের পরিমাণ',
        'currency' => 'মুদ্রা',
        'status' => 'পেমেন্টের অবস্থা',
        'paid_at' => 'পেমেন্টের তারিখ ও সময়',
        'verified_at' => 'যাচাইয়ের তারিখ ও সময়',
        'verified_by' => 'যাচাইকারী',
        'gateway_response' => 'গেটওয়ে থেকে প্রাপ্ত তথ্য',
        'remarks' => 'মন্তব্য',

        'sections' => [
            'information' => 'পেমেন্টের তথ্য',
            'applicant' => 'আবেদনকারীর তথ্য',
            'gateway' => 'পেমেন্ট গেটওয়ের তথ্য',
            'verification' => 'পেমেন্ট যাচাই',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 08. PaymentGatewayEvent - payment_gateway_events
    |--------------------------------------------------------------------------
    */

    'payment_gateway_event' => [
        'navigation' => 'পেমেন্ট গেটওয়ে লগ',
        'model' => 'গেটওয়ে ইভেন্ট',
        'plural_model' => 'গেটওয়ে ইভেন্টসমূহ',

        'payment_id' => 'পেমেন্ট',
        'gateway_event_id' => 'গেটওয়ে ইভেন্ট আইডি',
        'event_type' => 'ইভেন্টের ধরন',
        'payload' => 'প্রাপ্ত তথ্য',
        'signature_valid' => 'স্বাক্ষর যাচাই',
        'is_processed' => 'প্রক্রিয়াকরণ সম্পন্ন',
        'processed_at' => 'প্রক্রিয়াকরণের তারিখ ও সময়',
        'processing_error' => 'প্রক্রিয়াকরণে ত্রুটি',
    ],

    /*
    |--------------------------------------------------------------------------
    | 09. PaymentAllocation - payment_allocations
    |--------------------------------------------------------------------------
    */

    'payment_allocation' => [
        'navigation' => 'পেমেন্ট সমন্বয়',
        'model' => 'পেমেন্ট সমন্বয়',
        'plural_model' => 'পেমেন্ট সমন্বয়সমূহ',

        'payment_id' => 'পেমেন্ট',
        'payable_type' => 'সমন্বয়ের রেকর্ডের ধরন',
        'payable_id' => 'সমন্বয়ের রেকর্ড',
        'amount' => 'সমন্বয়কৃত পরিমাণ',
        'remarks' => 'মন্তব্য',
    ],

    /*
    |--------------------------------------------------------------------------
    | 10. DepositTransaction - deposit_transactions
    |--------------------------------------------------------------------------
    */

    'deposit_transaction' => [
        'navigation' => 'জামানত লেনদেন',
        'model' => 'জামানত লেনদেন',
        'plural_model' => 'জামানত লেনদেনসমূহ',

        'allocation_id' => 'প্লট বরাদ্দ',
        'payment_id' => 'পেমেন্ট',
        'transaction_type' => 'লেনদেনের ধরন',
        'amount' => 'লেনদেনের পরিমাণ',
        'reference_no' => 'রেফারেন্স নম্বর',
        'transaction_date' => 'লেনদেনের তারিখ',
        'description' => 'লেনদেনের বিবরণ',
        'created_by' => 'এন্ট্রিকারী',

        'sections' => [
            'information' => 'জামানত লেনদেনের তথ্য',
            'balance' => 'জামানতের হিসাব',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 11. DailyAttendance - daily_attendances
    |--------------------------------------------------------------------------
    */

    'daily_attendance' => [
        'navigation' => 'দৈনিক উপস্থিতি',
        'model' => 'দৈনিক উপস্থিতি',
        'plural_model' => 'দৈনিক উপস্থিতিসমূহ',

        'allocation_id' => 'প্লট বরাদ্দ',
        'attendance_date' => 'উপস্থিতির তারিখ',
        'attendance_status' => 'উপস্থিতির অবস্থা',
        'marked_by' => 'উপস্থিতি গ্রহণকারী',
        'marked_at' => 'উপস্থিতি গ্রহণের সময়',
        'remarks' => 'মন্তব্য',

        'sections' => [
            'information' => 'উপস্থিতির তথ্য',
            'applicant' => 'বরাদ্দপ্রাপ্ত ব্যক্তির তথ্য',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 12. DailyRentLedger - daily_rent_ledgers
    |--------------------------------------------------------------------------
    */

    'daily_rent_ledger' => [
        'navigation' => 'দৈনিক ভাড়া',
        'model' => 'দৈনিক ভাড়া',
        'plural_model' => 'দৈনিক ভাড়ার হিসাব',

        'allocation_id' => 'প্লট বরাদ্দ',
        'attendance_id' => 'উপস্থিতির তথ্য',
        'rate_history_id' => 'প্রযোজ্য রেট',
        'rent_date' => 'ভাড়ার তারিখ',
        'base_amount' => 'মূল ভাড়া',
        'adjustment_amount' => 'সমন্বয়ের পরিমাণ',
        'net_amount' => 'সর্বমোট ভাড়া',
        'status' => 'ভাড়া পরিশোধের অবস্থা',
        'remarks' => 'মন্তব্য',
        'paid_amount' => 'পরিশোধিত পরিমাণ',
        'due_amount' => 'বকেয়া পরিমাণ',

        'sections' => [
            'information' => 'দৈনিক ভাড়ার তথ্য',
            'calculation' => 'ভাড়ার হিসাব',
            'payment' => 'পেমেন্টের তথ্য',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 13. RentAdjustment - rent_adjustments
    |--------------------------------------------------------------------------
    */

    'rent_adjustment' => [
        'navigation' => 'ভাড়া সমন্বয়',
        'model' => 'ভাড়া সমন্বয়',
        'plural_model' => 'ভাড়া সমন্বয়সমূহ',

        'daily_rent_ledger_id' => 'দৈনিক ভাড়ার হিসাব',
        'adjustment_type' => 'সমন্বয়ের কারণ',
        'adjustment_mode' => 'সমন্বয়ের ধরন',
        'requested_amount' => 'আবেদিত ছাড়ের পরিমাণ',
        'approved_amount' => 'অনুমোদিত ছাড়ের পরিমাণ',
        'status' => 'আবেদনের অবস্থা',
        'reason' => 'ছাড়ের কারণ',
        'attachment_path' => 'প্রমাণপত্র',
        'requested_by' => 'আবেদনকারী',
        'approved_by' => 'অনুমোদনকারী',
        'approved_at' => 'অনুমোদনের তারিখ ও সময়',
        'approval_remarks' => 'অনুমোদন সংক্রান্ত মন্তব্য',

        'sections' => [
            'request' => 'ছাড়ের আবেদন',
            'approval' => 'ছাড় অনুমোদন',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 14. OfficeClosure - office_closures
    |--------------------------------------------------------------------------
    */

    'office_closure' => [
        'navigation' => 'অফিস বন্ধের দিন',
        'model' => 'অফিস বন্ধ',
        'plural_model' => 'অফিস বন্ধের তালিকা',

        'closure_no' => 'বন্ধের নম্বর',
        'title' => 'বন্ধের শিরোনাম',
        'closure_type' => 'বন্ধের ধরন',
        'start_date' => 'বন্ধ শুরুর তারিখ',
        'end_date' => 'বন্ধ শেষের তারিখ',
        'is_global' => 'সকল প্লটের জন্য প্রযোজ্য',
        'reason' => 'বন্ধের কারণ',
        'created_by' => 'তৈরিকারী',

        'sections' => [
            'information' => 'বন্ধের তথ্য',
            'period' => 'বন্ধের মেয়াদ',
            'applicable_plots' => 'প্রযোজ্য প্লটসমূহ',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 15. ClosurePlot - closure_plots
    |--------------------------------------------------------------------------
    */

    'closure_plot' => [
        'navigation' => 'বন্ধের আওতাধীন প্লট',
        'model' => 'বন্ধের আওতাধীন প্লট',
        'plural_model' => 'বন্ধের আওতাধীন প্লটসমূহ',

        'office_closure_id' => 'অফিস বন্ধের তথ্য',
        'plot_id' => 'প্লট',
    ],

    /*
    |--------------------------------------------------------------------------
    | 16. AbsenceNotice - absence_notices
    |--------------------------------------------------------------------------
    */

    'absence_notice' => [
        'navigation' => 'অনুপস্থিতির নোটিশ',
        'model' => 'অনুপস্থিতির নোটিশ',
        'plural_model' => 'অনুপস্থিতির নোটিশসমূহ',

        'notice_no' => 'নোটিশ নম্বর',
        'allocation_id' => 'প্লট বরাদ্দ',
        'consecutive_absent_days' => 'পরপর অনুপস্থিতির দিন',
        'absence_start_date' => 'অনুপস্থিতি শুরুর তারিখ',
        'notice_date' => 'নোটিশ প্রদানের তারিখ',
        'response_deadline' => 'জবাব প্রদানের শেষ তারিখ',
        'status' => 'নোটিশের অবস্থা',
        'notice_content' => 'নোটিশের বিষয়বস্তু',
        'applicant_response' => 'আবেদনকারীর জবাব',
        'responded_at' => 'জবাব প্রদানের তারিখ',
        'issued_by' => 'নোটিশ প্রদানকারী',
        'remarks' => 'মন্তব্য',

        'sections' => [
            'information' => 'নোটিশের তথ্য',
            'response' => 'আবেদনকারীর জবাব',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 17. AllocationCancellation - allocation_cancellations
    |--------------------------------------------------------------------------
    */

    'allocation_cancellation' => [
        'navigation' => 'বরাদ্দ বাতিল',
        'model' => 'বরাদ্দ বাতিল',
        'plural_model' => 'বরাদ্দ বাতিলের আবেদনসমূহ',

        'cancellation_no' => 'বাতিলের নম্বর',
        'allocation_id' => 'প্লট বরাদ্দ',
        'absence_notice_id' => 'অনুপস্থিতির নোটিশ',
        'cancellation_type' => 'বাতিলের ধরন',
        'requested_date' => 'আবেদনের তারিখ',
        'effective_date' => 'বাতিল কার্যকরের তারিখ',
        'status' => 'বাতিলের অবস্থা',
        'reason' => 'বাতিলের কারণ',
        'applicant_statement' => 'আবেদনকারীর বক্তব্য',
        'requested_by' => 'আবেদনকারী',
        'approved_by' => 'অনুমোদনকারী',
        'approved_at' => 'অনুমোদনের তারিখ',
        'approval_remarks' => 'অনুমোদন সংক্রান্ত মন্তব্য',
        'completed_at' => 'সম্পন্নের তারিখ',
        'completed_by' => 'সম্পন্নকারী',
        'completion_remarks' => 'সম্পন্ন সংক্রান্ত মন্তব্য',

        'sections' => [
            'information' => 'বাতিলের আবেদন',
            'approval' => 'বাতিল অনুমোদন',
            'completion' => 'বাতিল সম্পন্ন',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 18. Refund - refunds
    |--------------------------------------------------------------------------
    */

    'refund' => [
        'navigation' => 'টাকা ফেরত',
        'model' => 'টাকা ফেরত',
        'plural_model' => 'টাকা ফেরতের তালিকা',

        'refund_no' => 'রিফান্ড নম্বর',
        'allocation_id' => 'প্লট বরাদ্দ',
        'cancellation_id' => 'বরাদ্দ বাতিলের তথ্য',
        'payment_id' => 'মূল পেমেন্ট',
        'refund_type' => 'রিফান্ডের ধরন',
        'refund_method' => 'রিফান্ডের মাধ্যম',
        'amount' => 'ফেরতের পরিমাণ',
        'transaction_reference' => 'লেনদেনের রেফারেন্স',
        'status' => 'রিফান্ডের অবস্থা',
        'refund_date' => 'ফেরতের তারিখ',
        'requested_by' => 'আবেদনকারী',
        'approved_by' => 'অনুমোদনকারী',
        'approved_at' => 'অনুমোদনের তারিখ',
        'processed_by' => 'প্রক্রিয়াকারী',
        'processed_at' => 'প্রক্রিয়াকরণের তারিখ',
        'reason' => 'ফেরতের কারণ',
        'remarks' => 'মন্তব্য',

        'sections' => [
            'information' => 'রিফান্ডের তথ্য',
            'approval' => 'রিফান্ড অনুমোদন',
            'processing' => 'রিফান্ড প্রক্রিয়াকরণ',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 19. SystemSetting - system_settings
    |--------------------------------------------------------------------------
    */

    'system_setting' => [
        'navigation' => 'সিস্টেম সেটিংস',
        'model' => 'সিস্টেম সেটিংস',
        'plural_model' => 'সিস্টেম সেটিংসসমূহ',

        'group' => 'সেটিংসের গ্রুপ',
        'key' => 'সেটিংস কী',
        'value' => 'সেটিংসের মান',
        'type' => 'ডেটার ধরন',
        'description' => 'বিবরণ',
        'is_public' => 'সবার জন্য উন্মুক্ত',
        'updated_by' => 'সর্বশেষ আপডেটকারী',

        'sections' => [
            'information' => 'সেটিংসের তথ্য',
            'value' => 'সেটিংসের মান',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 20. ActivityLog - activity_logs
    |--------------------------------------------------------------------------
    */

    'activity_log' => [
        'navigation' => 'কার্যক্রমের ইতিহাস',
        'model' => 'কার্যক্রমের ইতিহাস',
        'plural_model' => 'কার্যক্রমের ইতিহাসসমূহ',

        'user_id' => 'ব্যবহারকারী',
        'log_name' => 'লগের নাম',
        'event' => 'কার্যক্রমের ধরন',
        'subject_type' => 'সংশ্লিষ্ট রেকর্ডের ধরন',
        'subject_id' => 'সংশ্লিষ্ট রেকর্ডের আইডি',
        'description' => 'কার্যক্রমের বিবরণ',
        'properties' => 'অতিরিক্ত তথ্য',
        'ip_address' => 'আইপি ঠিকানা',
        'user_agent' => 'ব্রাউজার ও ডিভাইসের তথ্য',
        'created_at' => 'কার্যক্রমের সময়',

        'sections' => [
            'information' => 'কার্যক্রমের তথ্য',
            'user' => 'ব্যবহারকারীর তথ্য',
            'technical' => 'প্রযুক্তিগত তথ্য',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Enum / Status Labels
    |--------------------------------------------------------------------------
    */

    'options' => [

        // Plot Status
        'plot_status' => [
            'available' => 'খালি',
            'reserved' => 'সংরক্ষিত',
            'allocated' => 'বরাদ্দকৃত',
            'maintenance' => 'রক্ষণাবেক্ষণাধীন',
            'inactive' => 'নিষ্ক্রিয়',
        ],

        // Plot Application Status
        'application_status' => [
            'pending_payment' => 'ফি পরিশোধের অপেক্ষায়',
            'payment_submitted' => 'পেমেন্ট জমা হয়েছে',
            'submitted' => 'আবেদন জমা হয়েছে',
            'under_review' => 'যাচাইাধীন',
            'approved' => 'অনুমোদিত',
            'rejected' => 'প্রত্যাখ্যাত',
            'cancelled' => 'বাতিল',
            'allocated' => 'প্লট বরাদ্দ হয়েছে',
        ],

        // Application Review Decision
        'review_decision' => [
            'approved' => 'অনুমোদন',
            'rejected' => 'প্রত্যাখ্যান',
            'returned' => 'সংশোধনের জন্য ফেরত',
        ],

        // Allocation Status
        'allocation_status' => [
            'active' => 'চলমান',
            'surrendered' => 'স্বেচ্ছায় ছেড়ে দেওয়া',
            'cancelled' => 'বাতিল',
            'expired' => 'মেয়াদ শেষ',
        ],

        // Rate Type
        'rate_type' => [
            'application_fee' => 'আবেদন ফি',
            'security_deposit' => 'জামানত',
            'daily_rent' => 'দৈনিক ভাড়া',
        ],

        // Payment For
        'payment_for' => [
            'application_fee' => 'আবেদন ফি',
            'security_deposit' => 'জামানত',
            'daily_rent' => 'দৈনিক ভাড়া',
            'security_deposit_refund' => 'জামানত ফেরত',
            'other' => 'অন্যান্য',
        ],

        // Payment Gateway
        'payment_gateway' => [
            'bkash' => 'বিকাশ',
            'nagad' => 'নগদ',
            'bangla_qr' => 'বাংলা QR',
            'manual' => 'অফিসে সরাসরি',
        ],

        // Payment Status
        'payment_status' => [
            'initiated' => 'শুরু হয়েছে',
            'pending' => 'অপেক্ষমাণ',
            'success' => 'সফল',
            'failed' => 'ব্যর্থ',
            'cancelled' => 'বাতিল',
            'refunded' => 'ফেরত দেওয়া হয়েছে',
        ],

        // Deposit Transaction Type
        'deposit_transaction_type' => [
            'deposit' => 'জামানত জমা',
            'deduction' => 'জামানত থেকে কর্তন',
            'refund' => 'জামানত ফেরত',
            'adjustment_credit' => 'জামানত বৃদ্ধি',
            'adjustment_debit' => 'জামানত হ্রাস',
        ],

        // Attendance Status
        'attendance_status' => [
            'present' => 'উপস্থিত',
            'absent' => 'অনুপস্থিত',
            'excused' => 'অনুপস্থিতি মঞ্জুর',
        ],

        // Daily Rent Status
        'daily_rent_status' => [
            'pending' => 'অপরিশোধিত',
            'partial' => 'আংশিক পরিশোধিত',
            'paid' => 'সম্পূর্ণ পরিশোধিত',
            'waived' => 'সম্পূর্ণ মওকুফ',
        ],

        // Rent Adjustment Type
        'adjustment_type' => [
            'illness' => 'অসুস্থতা',
            'absence' => 'অনুপস্থিতি',
            'rain' => 'বৃষ্টি',
            'other' => 'অন্যান্য',
        ],

        // Rent Adjustment Mode
        'adjustment_mode' => [
            'full' => 'সম্পূর্ণ ছাড়',
            'partial' => 'আংশিক ছাড়',
        ],

        // Rent Adjustment Status
        'adjustment_status' => [
            'pending' => 'অনুমোদনের অপেক্ষায়',
            'approved' => 'অনুমোদিত',
            'rejected' => 'প্রত্যাখ্যাত',
        ],

        // Office Closure Type
        'closure_type' => [
            'official_holiday' => 'সরকারি ছুটি',
            'emergency' => 'জরুরি বন্ধ',
            'weather' => 'আবহাওয়াজনিত বন্ধ',
            'other' => 'অন্যান্য',
        ],

        // Absence Notice Status
        'absence_notice_status' => [
            'draft' => 'খসড়া',
            'issued' => 'প্রদান করা হয়েছে',
            'acknowledged' => 'প্রাপ্তি স্বীকার করা হয়েছে',
            'responded' => 'জবাব প্রদান করা হয়েছে',
            'closed' => 'নিষ্পত্তি হয়েছে',
            'cancelled' => 'বাতিল',
        ],

        // Cancellation Type
        'cancellation_type' => [
            'voluntary' => 'স্বেচ্ছায় ছেড়ে দেওয়া',
            'absence' => 'দীর্ঘদিন অনুপস্থিতি',
            'authority_decision' => 'কর্তৃপক্ষের সিদ্ধান্ত',
            'other' => 'অন্যান্য',
        ],

        // Cancellation Status
        'cancellation_status' => [
            'pending' => 'অপেক্ষমাণ',
            'under_review' => 'যাচাইাধীন',
            'approved' => 'অনুমোদিত',
            'rejected' => 'প্রত্যাখ্যাত',
            'completed' => 'সম্পন্ন',
        ],

        // Refund Type
        'refund_type' => [
            'security_deposit' => 'জামানত ফেরত',
            'payment_refund' => 'পেমেন্ট ফেরত',
            'other' => 'অন্যান্য',
        ],

        // Refund Method
        'refund_method' => [
            'bkash' => 'বিকাশ',
            'nagad' => 'নগদ',
            'bank_transfer' => 'ব্যাংক ট্রান্সফার',
            'cash' => 'নগদ অর্থ',
            'other' => 'অন্যান্য',
        ],

        // Refund Status
        'refund_status' => [
            'pending' => 'অপেক্ষমাণ',
            'processing' => 'প্রক্রিয়াধীন',
            'completed' => 'সম্পন্ন',
            'failed' => 'ব্যর্থ',
            'cancelled' => 'বাতিল',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Navigation Groups
    |--------------------------------------------------------------------------
    */

    'navigation' => [
        'dashboard' => 'ড্যাশবোর্ড',
        'application_management' => 'আবেদন ব্যবস্থাপনা',
        'plot_management' => 'প্লট ব্যবস্থাপনা',
        'daily_operations' => 'দৈনিক কার্যক্রম',
        'financial_management' => 'আর্থিক ব্যবস্থাপনা',
        'reports' => 'রিপোর্ট ও হিসাব',
        'system_configuration' => 'সিস্টেম কনফিগারেশন',
        'user_management' => 'ব্যবহারকারী ব্যবস্থাপনা',
    ],

];
