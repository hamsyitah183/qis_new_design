@extends('errors.layout')

@section('title', '404 Not Found')

@section('content')
<div class="error-container">
    <div class="halftone-bg"></div>
    <div class="error-code">404</div>
    <div class="error-message">Not Found</div>
    <p class="error-description">
        The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.
    </p>
    <a href="{{ url('/') }}" class="home-btn">Go to Homepage</a>
</div>
@endsection
