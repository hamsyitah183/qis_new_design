@extends('pages.front')

@section('content')
     <div class="row authentication authentication-cover-main mx-0">
        <div class="col-xxl-5 col-xl-6 col-lg-12 d-xl-block d-none px-0">
            <div class="authentication-cover overflow-hidden">
                <div class="authentication-cover-logo">
                    <a href="https://laravelui.spruko.com/xintra/index"> 
                            <img src="https://laravelui.spruko.com/xintra/build/assets/images/brand-logos/desktop-white.png" alt="" class="authentication-brand desktop-white"> 
                        </a>
                </div>
                <div class="aunthentication-cover-content d-flex align-items-center justify-content-center">
                    <div>
                        <h3 class="text-fixed-white mb-1 fw-medium">Welcome Henry!</h3>
                        <h6 class="text-fixed-white mb-3 fw-medium">Login to Your Account</h6>
                        <p class="text-fixed-white mb-1 op-6">Welcome to the Admin Dashboard. Please log in to securely manage your administrative tools and oversee platform activities. Your credentials ensure system integrity and functionality.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-7 col-xl-6">
            <div class="row justify-content-center align-items-center h-100">
                <div class="col-xxl-7 col-xl-9 col-lg-6 col-md-6 col-sm-8 col-12 py-4">
                            <h3 class=" mb-2 text-center">Register New</h3>
                            <img src="https://qis-app.sabah.gov.my/assets/QIS-a3dc1042.gif" class="img-fluid mb-2" style="object-fit: contain;">
                            <form>
                                <div class="row gy-3">
                                    <div class="col-xl-12" >
                                        <label for="customeCheckbox" class="form-label text-default">User Type</label>
                                        <div class="d-flex justify-content-center flex-nowrap gap-3" id="customeCheckbox">
                                            <!-- Option 1 -->
                                            <div class="xintra-radio-box text-center">
                                            <input type="radio" name="regType" value="Individu" id="planBasic" class="xintra-radio-input">
                                            <label for="planBasic" class="xintra-radio-label">
                                                <div class="xintra-radio-content">
                                                <i class="bx bx-user fs-2 mb-2 text-primary"></i>
                                                <h6 class="mb-1">Individual</h6>
                                                </div>
                                            </label>
                                            </div>

                                            <!-- Option 2 -->
                                            <div class="xintra-radio-box text-center">
                                            <input type="radio" name="regType" value="Company" id="planStandard" class="xintra-radio-input">
                                            <label for="planStandard" class="xintra-radio-label">
                                                <div class="xintra-radio-content">
                                                <i class="bx bx-buildings fs-2 mb-2 text-primary"></i>
                                                <h6 class="mb-1">Company</h6>
                                                </div>
                                            </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-12" style="display: none;">
                                        <label for="signin-username" class="form-label text-default">User Type</label>
                                        <select id="planSelect" name="userType" class="form-select xintra-select">
                                            <option value="public">Public</option>
                                            <option value="internal">Internal</option>
                                        </select>
                                    </div>
                                    <div class="col-xl-12">
                                        <label for="signin-username" class="form-label text-default">Name</label>
                                        <input type="text" name="name" class="form-control" id="signin-username" >
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="signin-username" class="form-label text-default">Identity Number</label>
                                        <input type="text" name="identityNo" class="form-control" id="signin-username" >
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="signin-username" class="form-label text-default">Phone No</label>
                                        <input type="text" name="fonno" class="form-control" id="signin-username" >
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="signin-username" class="form-label text-default">Email</label>
                                        <input type="email" name="email" class="form-control" id="signin-username" >
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="signin-username" class="form-label text-default">Password</label>
                                        <input type="text" name="password" class="form-control" id="signin-username" >
                                    </div>
                                    <div class="col-xl-12">
                                        <label for="signin-username" class="form-label text-default">Address 1</label>
                                        <input type="text" name="address1" class="form-control" id="signin-username" >
                                    </div>
                                    <div class="col-xl-12">
                                        <label for="signin-username" class="form-label text-default">Address 2</label>
                                        <input type="text" name="address2" class="form-control" id="signin-username" placeholder="Email">
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="signin-username" class="form-label text-default">Postcode</label>
                                        <input type="text" name="postcode" class="form-control" id="signin-username" placeholder="Email">
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="signin-username" class="form-label text-default">District</label>
                                        <input type="text" name="district" class="form-control" id="signin-username" placeholder="Email">
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