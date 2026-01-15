@extends('errors.layout')

@section('title', '500 Internal Server Error')

@section('content')
<div class="error-container">
    <div class="halftone-bg"></div>
    <div class="error-code">500</div>
    <div class="error-message">Internal Server Error</div>
    <p class="error-description">
        The server encountered an unexpected condition.
    </p>
    <a href="{{ url('/') }}" class="home-btn">Go to Homepage</a>
</div>
@endsection
