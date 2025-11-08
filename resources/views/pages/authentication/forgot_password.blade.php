@extends('pages.front')

@push('scripts')
    @vite(['resources/js/pages/auth/forgot_password.js'])
@endpush

@section('content')
    <div class="container-lg">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 col-xl-5">
                <div class="card">

                    <div class="card-body">

                        <div class="p-2">
                            <div class="">

                                <div class="avatar-md mx-auto text-center">
                                    <div class="avatar-title ">
                                        <i class="bx bxs-envelope h1 mb-0 text-primary"></i>
                                    </div>
                                </div>
                                <div class="p-2 mt-4">
                                    <h4 class="text-center">Forgot Password</h4>
                                    <p class="text-center">Enter your Email and account type. The instructions will be sent to you!</p>

                                    <div class="alert alert-success text-center mb-4" id="emailSent" style="display: none"></div>

                                    <form class="form" action="#" id="forgotPasswordForm">

                                        <div class="col-xl-12 mb-2">
                                            <label for="planSelect" class="form-label text-default text-start">User Type</label>
                                            <select id="planSelect" name="type" class="form-select xintra-select">
                                                <option value="public" selected>Public</option>
                                                <option value="internal">Internal</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label for="useremail" class="form-label text-start">Email</label>
                                            <input type="email" class="form-control" id="useremail" name="email"
                                                placeholder="Enter email">
                                        </div>

                                        <div class="ms-auto d-flex justify-content-end align-items-end">
                                            <button class="btn btn-primary waves-effect waves-light"
                                                type="submit">Reset</button>
                                        </div>

                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="mt-5 text-center">
                    <p>Remember it ? <a href="/login" class="fw-medium text-primary"> Sign in </a> </p>

                </div>

            </div>
        </div>
    </div>
@endsection
