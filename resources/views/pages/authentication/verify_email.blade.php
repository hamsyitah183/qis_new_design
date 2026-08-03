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
                                    <h4 data-bm="Sahkan e-mel anda" data-en="Verify your email">Verify your email</h4>
                                    <p><span data-bm="Kami telah menghantar e-mel pengesahan kepada" data-en="We have sent you verification email">We have sent you verification email</span> <span
                                            class="fw-semibold">{{ $user->email ?? '' }}</span>,
                                        <span data-bm="Sila semaknya" data-en="Please check it">Please check it</span></p>
                                    <div class="alert alert-success text-center mb-4" id="emailSent" style="display: none">
                                    </div>
                                    <div class="mt-4 d-flex justify-content-center gap-2">
                                        <form method="POST" action="{{ route('verification.send') }}" id="verifyEmailForm">
                                                @csrf
                                                <button type="submit" class="btn btn-primary" data-bm="Hantar Semula E-mel" data-en="Resend Email">Resend Email</button>
                                            </form>

                                            <a type="submit" class="btn btn-secondary" id="logoutBtn"  href="{{ route('logout') }}" data-bm="Log Keluar" data-en="Logout">Logout</a>
                                        
                                        {{-- <a href="{{ route('logout') }}" class="btn btn-secondary">Logout</a> --}}
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="mt-5 text-center">
                    <p><span data-bm="Tidak menerima e-mel ?" data-en="Didn't receive an email ?">Didn't receive an email ?</span> <a href="#" class="fw-medium text-primary" data-bm="Hantar Semula" data-en="Resend"> Resend </a> </p>

                </div>

            </div>
        </div>
    </div>
@endsection
