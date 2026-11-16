@extends('frontend.layouts.app')

@section('title', 'পেমেন্ট | প্লট ব্যবস্থাপনা সিস্টেম')

@section('content')
<div class="container py-5">
    <div class="text-center">
        <div class="text-danger mb-3">
            <i class="bi bi-x-circle-fill" style="font-size: 70px;"></i>
        </div>

        <h2 class="text-danger">পেমেন্ট সম্পন্ন হয়নি!</h2>

        <p class="text-muted">
            দুঃখিত, আপনার পেমেন্ট সফল হয়নি।
            অনুগ্রহ করে পেমেন্টের অবস্থা যাচাই করে পুনরায় চেষ্টা করুন।
        </p>

        <a href="{{ route('home') }}" class="btn btn-primary mt-3">
            হোম পেজে ফিরে যান
        </a>
    </div>
</div>

@endsection