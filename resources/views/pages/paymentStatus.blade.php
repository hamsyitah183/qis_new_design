@extends('pages.front')

@push('scripts')
    {{-- @vite(['resources/js/pages/auth/reset_password.js']) --}}
@endpush


@section('content')
    <div class="container-lg">
        <div class="row justify-content-center authentication authentication-basic align-items-center h-100">
            <div class="col-xxl-4 col-xl-5 col-lg-5 col-md-6 col-sm-8 col-12">
                <p> {{ $kodTransaksi }} </p>
                <div>
                    {{ json_encode($paymentData, JSON_PRETTY_PRINT) }}
                </div>

                <div class="mt-2">
                    order number <br>

                  
                </div>

            </div>
        </div>
    </div>
@endsection
