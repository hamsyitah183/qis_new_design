@extends('pages.app')

@section('pageName', 'Wait for DOA Verification')


@section('breadcrumb')
    <x-breadcrumb :items="[['label' => ' ', 'url' => '#']]">

    </x-breadcrumb>
@endsection

@section('content')


    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-body d-flex flex-column align-items-center text-center">

                    <h3 class="mt-2 mb-3">Wait for DOA verification</h3>
                    <small class="text-muted mb-2">Your account is still not verified</small>

                    <span class="avatar avatar-xl avatar-rounded bg-warning-transparent svg-warning mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256">
                            <circle cx="128" cy="128" r="96" opacity="0.2"></circle>
                            <line x1="128" y1="80" x2="128" y2="136" stroke="currentColor"
                                stroke-linecap="round" stroke-width="16"></line>
                            <circle cx="128" cy="172" r="12" fill="currentColor"></circle>
                            <circle cx="128" cy="128" r="96" fill="none" stroke="currentColor"
                                stroke-width="16"></circle>
                        </svg>
                    </span>


                    <div class="d-flex gap-3 mt-3">
                        <a href="/" class="btn btn-sm btn-primary">
                            Return to Home
                        </a>

                        <a href="/profile" class="btn btn-sm btn-info">
                            {{ authUser()['user']->approved?->verification_attachment
                                ? 'Go To Verification Page'
                                : 'Upload Your Verification Attachment' }}
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
@endpush
