@extends('frontend.layouts.app')

@section('title', 'পেমেন্ট | প্লট ব্যবস্থাপনা সিস্টেম')



@section('title', 'পেমেন্ট বাতিল')

@section('content')
<div class="container py-5">
    <div class="text-center">
        <div class="text-warning mb-3">
            <i class="bi bi-exclamation-circle-fill" style="font-size: 70px;"></i>
        </div>

        <h2 class="text-warning">পেমেন্ট বাতিল করা হয়েছে!</h2>

        <p class="text-muted">
            আপনি পেমেন্ট প্রক্রিয়া বাতিল করেছেন।
            আপনার আবেদন এখনো পেমেন্টের অপেক্ষায় রয়েছে।
        </p>

        <a href="{{ route('home') }}" class="btn btn-primary mt-3">
            হোম পেজে ফিরে যান
        </a>
    </div>
</div>
@endsection
