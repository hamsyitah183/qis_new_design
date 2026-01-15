@extends('errors.layout')

@section('title', '403 Forbidden')

@section('content')
<div class="error-container">
    <div class="halftone-bg"></div>
    <div class="error-code">403</div>
    <div class="error-message">Access Denied</div>
    <p class="error-description">
        You do not have permission to access this. If you believe this is a mistake, contact your administrator.
    </p>
    <a href="{{ url('/') }}" class="home-btn">Go to Homepage</a>
</div>
@endsection
