@extends('errors.layout')

@section('title', '402 Payment Required')

@section('content')
<div class="error-container">
    <div class="halftone-bg"></div>
    <div class="error-code">402</div>
    <div class="error-message">Payment Required</div>
    <p class="error-description">
        Payment is required to access this page.
    </p>
    <a href="{{ url('/') }}" class="home-btn">Go to Homepage</a>
</div>
@endsection
