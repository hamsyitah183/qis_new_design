@extends('errors.layout')

@section('title', '429 Too Many Requests')

@section('content')
<div class="error-container">
    <div class="halftone-bg"></div>
    <div class="error-code">429</div>
    <div class="error-message">Too Many Requests</div>
    <p class="error-description">
        You have sent too many requests to the server. Please wait and try again later.
    </p>
    <a href="{{ url('/') }}" class="home-btn">Go to Homepage</a>
</div>
@endsection
