@extends('pages.front')

@push('scripts')
    {{-- @vite(['resources/js/pages/auth/reset_password.js']) --}}
@endpush


@section('content')
    @php
        $user = $order->order_details['user'];
        $permits = $order->order_details['permits'];
    @endphp

    <div class="container-lg">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 col-xl-5">
                <div class="card">
                    <div class="card-body p-4 text-center">

                        <div class="avatar-md mx-auto mb-3">
                            <div class="avatar-title">
                                @if ($paymentData['transaction_status'] == 'SUCCESSFUL')
                                    <i class="bx bx-check-circle h1 mb-0 text-success"></i>
                                @elseif ($paymentData['transaction_status'] == 'UNSUCCESSFUL')
                                    <i class="bx bx-x-circle h1 mb-0 text-danger"></i>
                                @else
                                    <i class="bi bi-exclamation-circle h1 mb-0 text-danger"></i>
                                @endif
                            </div>
                        </div>

                        <h4 class="mb-2">
                            @if ($paymentData['transaction_status'] == 'SUCCESSFUL')
                                Payment Successful

                                <p class = "fs-12 text-muted mt-2">Your order ({{ $order->order_number }}) payment is
                                    success. </p>
                            @elseif ($paymentData['transaction_status'] == 'UNSUCCESSFUL')
                                Payment Failed

                                <p class = "fs-12 text-muted mt-2">Your order ({{ $order->order_number }}) payment is
                                    failed. Please try again. </p>
                            @else
                                Payment Pending

                                <p class = "fs-12 text-muted mt-2">Your order ({{ $order->order_number }}) is pending for
                                    authorization. </p>
                            @endif
                        </h4>

                        @if (!empty($paymentData['message']))
                            <p class="text-muted mb-0">{{ $paymentData['message'] }}</p>
                        @endif

                        <div class="fs-14">
                            <span class="fw-bold">FPX Reference: </span> {{ $order->fpx_seller_reference }}
                        </div>
                        <div class="fs-14">
                            <span class="fw-bold">Amount: </span> RM {{ $order->payment_amount }}
                        </div>

                        <div class="table-responsive mt-4">
                            <table class="table table-borderless table-sm text-start">
                                <tbody>
                                    <tr>
                                        <th scope="row" class="text-nowrap" style="width: 1%;">Application Type:</th>
                                        <td>{{ $order->application_type }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="text-nowrap">Name:</th>
                                        <td>{{ $user['fullname'] }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="text-nowrap">Email:</th>
                                        <td>{{ $user['email'] }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="text-nowrap">Phone Number:</th>
                                        <td>{{ $user['phone_number'] }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="text-nowrap">Permits Number:</th>
                                        <td>
                                            @foreach ($permits as $item)
                                                {{ $item['permit_number'] }}@if (!$loop->last)
                                                    ,
                                                @endif
                                            @endforeach
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

                <div class="mt-4 text-center">
                    <a href="/order/list" class="btn btn-primary btn-sm fw-medium">Back to order list</a>
                </div>

            </div>
        </div>
    </div>
@endsection

{{-- payment processing --}}
{{-- 1. bayupay klau close page, pemit masih payment processing 7 minutes, 'processing payment' --}}
