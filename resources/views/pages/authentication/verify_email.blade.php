@extends('pages.front')

@push('scripts')
    @vite(['resources/js/pages/auth/verify.js'])
@endpush

@section('content')
    <div class="container-lg">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 col-xl-5">
                <div class="card">

                    <div class="card-body">

                        <div class="p-2">
                            <div class="text-center">

                                <div class="avatar-md mx-auto">
                                    <div class="avatar-title ">
                                        <i class="bx bxs-envelope h1 mb-0 text-primary"></i>
                                    </div>
                                </div>
                                <div class="p-2 mt-4">
                                    <h4>Verify your email</h4>
                                    <p>We have sent you verification email <span
                                            class="fw-semibold">{{ $user->email ?? '' }}</span>,
                                        Please check it</p>
                                    <div class="alert alert-success text-center mb-4" id="emailSent" style="display: none">
                                    </div>
                                    <div class="mt-4 d-flex justify-content-center gap-2">
                                        <form method="POST" action="{{ route('verification.send') }}" id="verifyEmailForm">
                                            @csrf
                                            <button type="submit" class="btn btn-primary">Resend Email</button>
                                        </form>
                                        
                                            <a type="submit" class="btn btn-secondary" id="logoutBtn"  href="{{ route('logout') }}" >Logout</a>
                                        
                                        {{-- <a href="{{ route('logout') }}" class="btn btn-secondary">Logout</a> --}}
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="mt-5 text-center">
                    <p>Didn't receive an email ? <a href="#" class="fw-medium text-primary"> Resend </a> </p>

                </div>

            </div>
        </div>
    </div>
@endsection
