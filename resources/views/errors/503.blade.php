@extends('errors.layout')

@section('title', '503 Service Unavailable')

@section('content')
<div class="error-container">
    <div class="halftone-bg"></div>
    <div class="error-code">503</div>
    <div class="error-message">Service Unavailable</div>
    <p class="error-description">
        The server is currently unavailable. Please try again later.
    </p>
    <a href="{{ url('/') }}" class="home-btn">Go to Homepage</a>
</div>
@endsection
