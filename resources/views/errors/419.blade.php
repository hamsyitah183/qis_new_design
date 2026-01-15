@extends('errors.layout')

@section('title', '419 Page Expired')

@section('content')
<div class="error-container">
    <div class="halftone-bg"></div>
    <div class="error-code">419</div>
    <div class="error-message">Page Expired</div>
    <p class="error-description">
        The page you are looking for has expired.
    </p>
    <a href="{{ url('/') }}" class="home-btn">Go to Homepage</a>
</div>
@endsection
