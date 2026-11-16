@extends('frontend.layouts.app')

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

@endsection