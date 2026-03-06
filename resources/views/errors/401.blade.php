@extends('errors.layout')

@section('title', '401 Unauthorized')

@section('content')
<div class="error-container">
    <div class="halftone-bg"></div>
    <div class="error-code">401</div>
    <div class="error-message">Unauthorized</div>
    <p class="error-description">
        You are not authorized to access this page.
    </p>
    <a href="{{ url('/') }}" class="home-btn">Go to Homepage</a>
</div>
@endsection
