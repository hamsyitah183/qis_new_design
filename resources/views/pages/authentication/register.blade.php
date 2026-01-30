@extends('pages.front')

@push('scripts')
    @vite(['resources/js/pages/auth/register.js'])
@endpush

@section('content')
    <div class="row authentication authentication-cover-main mx-0">
        <div class="col-xxl-5 col-xl-6 col-lg-12 d-xl-block d-none px-0">
            <div class="authentication-cover overflow-hidden">
                <div class="authentication-cover-logo">
                    <a href="https://laravelui.spruko.com/xintra/index">
                        <img src="https://laravelui.spruko.com/xintra/build/assets/images/brand-logos/desktop-white.png"
                            alt="" class="authentication-brand desktop-white">
                    </a>
                </div>
                <div class="aunthentication-cover-content d-flex align-items-center justify-content-center">
                    <div>
                        <h3 class="text-fixed-white mb-1 fw-medium">Welcome Henry!</h3>
                        <h6 class="text-fixed-white mb-3 fw-medium">Login to Your Account</h6>
                        <p class="text-fixed-white mb-1 op-6">Welcome to the Admin Dashboard. Please log in to securely
                            manage your administrative tools and oversee platform activities. Your credentials ensure system
                            integrity and functionality.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-7 col-xl-6">
            <div class="row justify-content-center align-items-center h-100">
                <div class="col-xxl-7 col-xl-9 col-lg-6 col-md-6 col-sm-8 col-12 py-4">
                    <h3 class=" mb-2 text-center">Register New</h3>
                    <img src="https://qis-app.sabah.gov.my/assets/QIS-a3dc1042.gif" class="img-fluid mb-2"
                        style="object-fit: contain;">
                    <form id="publicRegisterForm">
                        @csrf
                        <div class="row gy-3">

                            <!-- Account Type (Individual / Company) -->
                            <div class="col-xl-12">
                                <label for="customeCheckbox" class="form-label text-default">User Type</label>
                                <div class="d-flex justify-content-center flex-nowrap gap-3" id="customeCheckbox">
                                    <!-- Individual -->
                                    <div class="xintra-radio-box text-center">
                                        <input type="radio" name="account_type" value="individu" id="planBasic"
                                            class="xintra-radio-input">
                                        <label for="planBasic" class="xintra-radio-label">
                                            <div class="xintra-radio-content">
                                                <i class="bx bx-user fs-2 mb-2 text-primary"></i>
                                                <h6 class="mb-1">Individual</h6>
                                            </div>
                                        </label>
                                    </div>

                                    <!-- Company -->
                                    <div class="xintra-radio-box text-center">
                                        <input type="radio" name="account_type" value="company" id="planStandard"
                                            class="xintra-radio-input">
                                        <label for="planStandard" class="xintra-radio-label">
                                            <div class="xintra-radio-content">
                                                <i class="bx bx-buildings fs-2 mb-2 text-primary"></i>
                                                <h6 class="mb-1">Company</h6>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Name -->
                            <div class="col-xl-12">
                                <label class="form-label text-default">Full Name</label>
                                <input type="text" name="fullname" class="form-control" placeholder="Full Name">
                            </div>

                            <!-- Identity Number -->
                            <div class="col-xl-6">
                                <label class="form-label text-default">Identity Number</label>
                                <input type="text" name="no_ic" class="form-control" placeholder="Identity Number">
                            </div>

                            <!-- Phone -->
                            <div class="col-xl-6">
                                <label class="form-label text-default">Phone Number</label>
                                <input type="text" name="phone_number" class="form-control" placeholder="Phone Number">
                            </div>

                            <!-- Office number (optional) -->
                            <div class="col-xl-6">
                                <label class="form-label text-default">Office Number (Optional)</label>
                                <input type="text" name="office_number" class="form-control" placeholder="Office Number">
                            </div>

                            <!-- Email -->
                            <div class="col-xl-6">
                                <label class="form-label text-default">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="Email">
                            </div>

                            <!-- Password -->
                            <div class="col-xl-12">
                                <label class="form-label text-default">Password</label>
                                <input type="password" name="password" class="form-control" placeholder="Password">
                            </div>

                            <!-- Address 1 -->
                            <div class="col-xl-12">
                                <label class="form-label text-default">Address 1</label>
                                <textarea name="address_1" id="" cols="30" rows="3" class="form-control border"></textarea>
                            </div>

                            <!-- Address 2 (optional) -->
                            <div class="col-xl-12">
                                <label class="form-label text-default">Address 2 (Optional)</label>
                                <textarea name="address_2" id="" cols="30" rows="3" class="form-control border"></textarea>
                            </div>

                            <!-- State -->
                            <div class="col-xl-12">
                                <label class="form-label text-default">State</label>
                                <select name="state" class="form-control state-register">
                                    <option value="">Select State</option>
                                </select>
                            </div>

                            <!-- District -->
                            <div class="col-xl-6">
                                <label class="form-label text-default">District</label>
                                <select name="district" class="form-control district-register">
                                    <option value="">Select District</option>
                                </select>
                            </div>

                            <!-- Postcode -->
                            <div class="col-xl-6">
                                <label class="form-label text-default">Postcode</label>
                                <select name="postcode" class="form-control postcode-register">
                                    <option value="">Select Postcode</option>
                                </select>
                            </div>

                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary">Register</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection
