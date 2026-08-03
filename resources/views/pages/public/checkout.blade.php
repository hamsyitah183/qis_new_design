@extends('pages.app')

@push('style')
    
@endpush

@push('scripts')
    <!-- vite -->
@endpush

@section('pageName', 'Checkout')


@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Home', 'url' => '#']]" title="My Checkout">

    </x-breadcrumb>
@endsection

@section('content')
                    <div class="row">
                        <div class="col-xxl-9">
                            <div class="card custom-card">
                                <div class="card-body product-checkout">
                                    <ul class="nav nav-tabs tab-style-8 scaleX d-sm-flex d-block justify-content-around border border-dashed border-bottom-0 bg-light rounded-top" id="myTab1" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link p-3 active" id="order-tab" data-bs-toggle="tab" data-bs-target="#order-tab-pane" type="button" role="tab" aria-controls="order-tab" aria-selected="true"><i class="ri-truck-line me-2 align-middle"></i><span data-bm="Butiran Produk" data-en="Product Details">Product Details</span></button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link p-3" id="confirmed-tab" data-bs-toggle="tab" data-bs-target="#confirm-tab-pane" type="button" role="tab" aria-controls="confirmed-tab" aria-selected="false" tabindex="-1"><i class="ri-user-3-line me-2 align-middle"></i><span data-bm="Butiran Peribadi" data-en="Personal Details">Personal Details</span></button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link p-3" id="shipped-tab" data-bs-toggle="tab" data-bs-target="#shipped-tab-pane" type="button" role="tab" aria-controls="shipped-tab" aria-selected="false" tabindex="-1"><i class="ri-bank-card-line me-2 align-middle"></i>Payment</button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link p-3" id="delivered-tab" data-bs-toggle="tab" data-bs-target="#delivery-tab-pane" type="button" role="tab" aria-controls="delivered-tab" aria-selected="false" tabindex="-1"><i class="ri-checkbox-circle-line me-2 align-middle"></i><span data-bm="Status Pesanan" data-en="Order Status">Order Status</span></button>
                                        </li>
                                    </ul>
                                    <div class="tab-content border border-dashed" id="myTabContent">
                                        <div class="tab-pane fade show active border-0 p-0" id="order-tab-pane" role="tabpanel" aria-labelledby="order-tab-pane" tabindex="0">
                                            <div class="p-3">
                                                <p class="mb-1 fw-semibold text-muted op-5 fs-20">01</p>
                                                <div class="row gy-3 mb-4">
                                                    <p class="fs-15 fw-semibold mb-1"><span data-bm="Butiran Produk" data-en="Product Details">Product Details</span> :</p>
                                                    <div class="col-xl-6">
                                                        <div class="form-check shipping-method-container mb-0">
                                                            <input id="shipping-method1" name="shipping-methods" type="radio" class="form-check-input" checked="">
                                                            <div class="form-check-label">
                                                            <div class="d-sm-flex align-items-center justify-content-between">
                                                                <div class="me-2">
                                                                    <span class="avatar avatar-md">
                                                                        <img src="https://laravelui.spruko.com/xintra/build/assets/images/ecommerce/png/21.png" alt="">
                                                                    </span>
                                                                </div>
                                                                <div class="shipping-partner-details me-sm-5 me-0">
                                                                    <p class="mb-0 fw-semibold">UPS</p>
                                                                    <p class="text-muted fs-11 mb-0">Delivered By 11,May 2024</p>
                                                                </div>
                                                                <div class="fw-semibold me-sm-5 me-0">
                                                                    $9.99
                                                                </div>
                                                            </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-6">
                                                        <div class="form-check shipping-method-container mb-0">
                                                            <input id="shipping-method2" name="shipping-methods" type="radio" class="form-check-input">
                                                            <div class="form-check-label">
                                                            <div class="d-sm-flex align-items-center justify-content-between">
                                                                <div class="me-2">
                                                                    <span class="avatar avatar-md">
                                                                        <img src="https://laravelui.spruko.com/xintra/build/assets/images/ecommerce/png/22.png" alt="">
                                                                    </span>
                                                                </div>
                                                                <div class="shipping-partner-details me-sm-5 me-0">
                                                                    <p class="mb-0 fw-semibold">USPS</p>
                                                                    <p class="text-muted fs-11 mb-0">Delivered By 22,Nov 2022</p>
                                                                </div>
                                                                <div class="fw-semibold me-sm-5 me-0">
                                                                    $10.49
                                                                </div>
                                                            </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="fs-15 fw-semibold d-sm-flex d-block align-items-center justify-content-between mb-3">
                                                    <div><span data-bm="Alamat Penghantaran :" data-en="Shipping Address :">Shipping Address :</span></div>
                                                    <div class="mt-sm-0 mt-2">
                                                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modal-new-address"><i class="ri-add-line me-1 align-middle fs-14 fw-semibold"></i><span data-bm="Tambah Alamat Baru" data-en="Add New Address">Add New Address</span></button>
                                                        <div class="modal fade" id="modal-new-address" tabindex="-1" aria-labelledby="modal-new-address" aria-hidden="true">
                                                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h6 class="modal-title" id="staticBackdropLabel" data-bm="Alamat Baru" data-en="New Address">New Address
                                                                        </h6>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <div class="row gy-3">
                                                                            <div class="col-xl-6">
                                                                                <label for="fullname-new" class="form-label" data-bm="Nama Penuh" data-en="Full Name">Full Name</label>
                                                                                <input type="text" class="form-control" id="fullname-new" placeholder="Full Name">
                                                                            </div>
                                                                            <div class="col-xl-6">
                                                                                <label for="email-new" class="form-label" data-bm="E-mel" data-en="Email">Email</label>
                                                                                <input type="email" class="form-control" id="email-new" placeholder="email">
                                                                            </div>
                                                                            <div class="col-xl-6">
                                                                                <label for="phonenumber-new" class="form-label" data-bm="Nombor Telefon" data-en="Phone Number">Phone Number</label>
                                                                                <input type="number" class="form-control" id="phonenumber-new" placeholder="Phone">
                                                                            </div>
                                                                            <div class="col-xl-6">
                                                                                <label for="address-new" class="form-label" data-bm="Alamat" data-en="Address">Address</label>
                                                                                <input type="text" class="form-control" id="address-new" placeholder="Address">
                                                                            </div>
                                                                            <div class="col-xl-12">
                                                                                <div class="row">
                                                                                    <div class="col-xl-3">
                                                                                        <label for="pincode-new" class="form-label" data-bm="Poskod" data-en="Pincode">Pincode</label>
                                                                                        <input type="number" class="form-control" id="pincode-new" placeholder="Pincode">
                                                                                    </div>
                                                                                    <div class="col-xl-3">
                                                                                        <label for="city-new" class="form-label" data-bm="Bandar" data-en="City">City</label>
                                                                                        <input type="text" class="form-control" id="city-new" placeholder="City">
                                                                                    </div>
                                                                                    <div class="col-xl-3">
                                                                                        <label for="state-new" class="form-label" data-bm="Negeri" data-en="State">State</label>
                                                                                        <input type="text" class="form-control" id="state-new" placeholder="State">
                                                                                    </div>
                                                                                    <div class="col-xl-3">
                                                                                        <label for="country-new" class="form-label" data-bm="Negara" data-en="Country">Country</label>
                                                                                        <input type="text" class="form-control" id="country-new" placeholder="Country">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal" data-bm="Tutup" data-en="Close">Close</button>
                                                                        <button type="button" class="btn btn-success"><span data-bm="Simpan Alamat" data-en="Save Address">Save Address</span></button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-xl-6">
                                                        <div class="card custom-card card-style-6 border shadow-none mb-xl-0">
                                                            <div class="card-body p-3">
                                                                <div class="d-flex gap-2">
                                                                    <input class="form-check-input" type="radio" id="address1" name="default-address" checked="">
                                                                    <label class="form-check-label cursor-pointer" for="address1"><span data-bm="Tetapkan sebagai Lalai" data-en="Set as Default">Set as Default</span></label>
                                                                </div>
                                                                <div class="d-flex align-items-center justify-content-between mb-3">
                                                                    <div class="flex-grow-1">
                                                                        <h6 class="fs-16 mb-0 fw-semibold">My Home Address</h6>
                                                                    </div>
                                                                    <a class="btn btn-primary btn-sm"><i class="ri-edit-2-line"></i> <span data-bm="Tukar" data-en="Change">Change</span></a>
                                                                </div>
                                                                <h6 class="mb-1">Victoria Gracie</h6>
                                                                <p class="mb-1 fw-500 fs-13">victoriagracie@jinno.mail</p>
                                                                <p class="mb-2 fw-500 fs-13">+05-554-874113</p>
                                                                <p class="mb-0">
                                                                    H.No: 48A-1B/C451, Smart Avenue,Coolin Street,
                                                                    Opp. NG Super Mart, 57016, Canada
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-6">
                                                        <div class="card custom-card card-style-6 border shadow-none mb-0">
                                                            <div class="card-body p-3">
                                                                <div class="d-flex gap-2">
                                                                    <input class="form-check-input" type="radio" id="address2" name="default-address">
                                                                    <label class="form-check-label cursor-pointer" for="address2"><span data-bm="Tetapkan sebagai Lalai" data-en="Set as Default">Set as Default</span></label>
                                                                </div>
                                                                <div class="d-flex align-items-center justify-content-between mb-3">
                                                                    <div class="flex-grow-1">
                                                                        <div class="d-flex align-items-center gap-2 card-style-6-avatar">
                                                                            <h6 class="fs-16 mb-0 fw-semibold">Work Place Address</h6>
                                                                        </div>
                                                                    </div>
                                                                    <a class="btn btn-primary btn-sm"><i class="ri-edit-2-line"></i> <span data-bm="Tukar" data-en="Change">Change</span></a>
                                                                </div>
                                                                <h6 class="mb-1">Victoria Gracie</h6>
                                                                <p class="mb-1 fw-500 fs-13">victoriagracie@jinno.mail</p>
                                                                <p class="mb-2 fw-500 fs-13">+05-554-874113</p>
                                                                <p class="mb-0">
                                                                    Sunset Plaza, 5th Floor, Suite No: 502, Ocean Avenue,, Seaview Heights, Sunnydale, CA 90210, United States
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="p-3 border-top border-block-start-dashed d-sm-flex justify-content-end">
                                                <button class="btn btn-primary1-light" id="personal-details-trigger"><span data-bm="Butiran Peribadi" data-en="Personal Details">Personal Details</span><i class="ri-user-3-line ms-2 align-middle d-inline-block"></i></button>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade border-0 p-0" id="confirm-tab-pane" role="tabpanel" aria-labelledby="confirm-tab-pane" tabindex="0">
                                            <div class="p-3">
                                                <p class="mb-1 fw-semibold text-muted op-5 fs-20">02</p>
                                                <div class="fs-15 fw-semibold d-sm-flex d-block align-items-center justify-content-between mb-3">
                                                    <div><span data-bm="Butiran Peribadi" data-en="Personal Details">Personal Details</span> :</div>
                                                </div>
                                                <div class="row gy-3">
                                                    <div class="col-xl-6">
                                                        <label for="firstname-personal" class="form-label"><span data-bm="Nama Pertama" data-en="First Name">First Name</span></label>
                                                        <input type="text" class="form-control" id="firstname-personal" placeholder="<span data-bm="Nama Pertama" data-en="First Name">First Name</span>" value="Victoria ">
                                                    </div>
                                                    <div class="col-xl-6">
                                                        <label for="lastname-personal" class="form-label"><span data-bm="Nama Terakhir" data-en="Last Name">Last Name</span></label>
                                                        <input type="text" class="form-control" id="lastname-personal" placeholder="<span data-bm="Nama Terakhir" data-en="Last Name">Last Name</span>" value="Gracie">
                                                    </div>
                                                    <div class="col-xl-12">
                                                        <label for="email-personal" class="form-label">Email</label>
                                                        <input type="email" class="form-control" id="email-personal" placeholder="victoriagracie@jinno.mail" value="">
                                                    </div>
                                                    <div class="col-xl-12">
                                                        <label for="phoneno-personal" class="form-label"><span data-bm="No. Telefon" data-en="Phone no">Phone no</span></label>
                                                        <input type="text" class="form-control" id="phoneno-personal" placeholder="554-874113" value="">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="p-3 border-top border-block-start-dashed d-sm-flex justify-content-between">
                                                <button class="btn btn-primary-light" id="back-shipping-trigger"><i class="ri-truck-line me-2 align-middle d-inline-block"></i><span data-bm="Kembali ke Penghantaran" data-en="Back To Shipping">Back To Shipping</span></button>
                                                <button class="btn btn-primary1-light mt-sm-0 mt-2" id="payment-trigger"><span data-bm="Teruskan ke Pembayaran" data-en="Continue To Payment">Continue To Payment</span><i class="bi bi-credit-card-2-front align-middle ms-2 d-inline-block"></i></button>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade border-0 p-0" id="shipped-tab-pane" role="tabpanel" aria-labelledby="shipped-tab-pane" tabindex="0">
                                            <div class="p-3">
                                                <p class="mb-1 fw-semibold text-muted op-5 fs-20">03</p>
                                                <div class="row">
                                                    <div class="col-xl-12">
                                                        <div class="fs-15 fw-semibold d-sm-flex d-block align-items-center justify-content-between mb-3">
                                                            <div><span data-bm="Butiran Pembayaran" data-en="Payment Details">Payment Details</span> :</div>
                                                        </div>
                                                        <div class="mb-3 d-sm-flex d-block gap-3" role="group" aria-label="Basic radio toggle button group">
                                                            <div class="form-check form-check-inline">
                                                                <input class="form-check-input" type="radio" name="Paymentoptions" id="Paymentoptions3" value="Paymentoptions3" checked="checked">
                                                                <label class="form-check-label" for="Paymentoptions3"><span data-bm="Kad Kredit/Debit" data-en="Credit/Debit Card">Credit/Debit Card</span></label>
                                                            </div>
                                                            <div class="form-check form-check-inline">
                                                                <input class="form-check-input" type="radio" name="Paymentoptions" id="Paymentoptions1" value="Paymentoptions1">
                                                                <label class="form-check-label" for="Paymentoptions1">C.O.D (Cash on delivery)</label>
                                                            </div>
                                                            <div class="form-check form-check-inline">
                                                                <input class="form-check-input" type="radio" name="Paymentoptions" id="Paymentoptions2" value="Paymentoptions2">
                                                                <label class="form-check-label" for="Paymentoptions2">UPI Payment</label>
                                                            </div>                                                        
                                                        </div>
                                                        <div class="row gy-3 mb-3">
                                                            <div class="col-xl-12">
                                                                <label for="payment-card-number" class="form-label"><span data-bm="Nombor Kad" data-en="Card Number">Card Number</span></label>
                                                                <input type="text" class="form-control" id="payment-card-number" placeholder="<span data-bm="Nombor Kad" data-en="Card Number">Card Number</span>" value="1245 - 5447 - 8934 - XXXX">
                                                            </div>
                                                            <div class="col-xl-12">
                                                                <label for="payment-card-name" class="form-label"><span data-bm="Nama pada Kad" data-en="Name On Card">Name On Card</span></label>
                                                                <input type="text" class="form-control" id="payment-card-name" placeholder="<span data-bm="Nama pada Kad" data-en="Name On Card">Name On Card</span>" value="JSON TAYLOR">
                                                            </div>
                                                            <div class="col-xl-4">
                                                                <label for="payment-cardexpiry-date" class="form-label"><span data-bm="Tarikh Luput" data-en="Expiration Date">Expiration Date</span></label>
                                                                <input type="text" class="form-control" id="payment-cardexpiry-date" placeholder="MM/YY" value="08/2024">
                                                            </div>
                                                            <div class="col-xl-4">
                                                                <label for="payment-cvv" class="form-label">CVV</label>
                                                                <input type="text" class="form-control" id="payment-cvv" placeholder="XXX" value="341">
                                                            </div>
                                                            <div class="col-xl-4">
                                                                <label for="payment-security" class="form-label">O.T.P</label>
                                                                <input type="text" class="form-control" id="payment-security" placeholder="XXXXXX" value="183467">
                                                                <label for="payment-security" class="form-label mt-1 mb-0 text-danger fs-11"><sup><i class="ri-star-s-fill"></i></sup>Do not share O.T.P with anyone</label>
                                                            </div>
                                                            <div class="col-xl-12">
                                                                <div class="form-check">
                                                                    <input class="form-check-input form-checked-success" type="checkbox" value="" id="payment-card-save" checked="">
                                                                    <label class="form-check-label" for="payment-card-save">
                                                                        <span data-bm="Simpan kad ini" data-en="Save this card">Save this card</span>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="fs-15 fw-semibold d-sm-flex d-block align-items-center justify-content-between mb-3">
                                                            <div><span data-bm="Kad Tersimpan :" data-en="Saved Cards :">Saved Cards :</span></div>
                                                        </div>
                                                        <div class="row gy-3">
                                                            <div class="col-xl-6">
                                                                <div class="form-check payment-card-container mb-0">
                                                                    <input id="payment-card1" name="payment-cards" type="radio" class="form-check-input" checked="">
                                                                    <div class="form-check-label">
                                                                    <div class="d-sm-flex d-block align-items-center justify-content-between">
                                                                        <div class="me-2 lh-1">
                                                                            <span class="avatar avatar-md">
                                                                                <img src="https://laravelui.spruko.com/xintra/build/assets/images/ecommerce/png/26.png" alt="">
                                                                            </span>
                                                                        </div>
                                                                        <div class="saved-card-details">
                                                                            <p class="mb-0 fw-semibold">XXXX - XXXX - XXXX - 7646</p>
                                                                        </div>
                                                                    </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-xl-6">
                                                                <div class="form-check payment-card-container mb-0">
                                                                    <input id="payment-card2" name="payment-cards" type="radio" class="form-check-input">
                                                                    <div class="form-check-label">
                                                                    <div class="d-sm-flex d-block align-items-center justify-content-between">
                                                                        <div class="me-2 lh-1">
                                                                            <span class="avatar avatar-md">
                                                                                <img src="https://laravelui.spruko.com/xintra/build/assets/images/ecommerce/png/27.png" alt="">
                                                                            </span>
                                                                        </div>
                                                                        <div class="saved-card-details">
                                                                            <p class="mb-0 fw-semibold">XXXX - XXXX - XXXX - 9556</p>
                                                                        </div>
                                                                    </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="p-3 border-top border-block-start-dashed d-sm-flex justify-content-between">
                                                <button class="btn btn-primary-light" id="back-personal-trigger"><i class="ri-user-3-line me-2 align-middle d-inline-block"></i><span data-bm="Kembali ke Maklumat Peribadi" data-en="Back To Personal Info">Back To Personal Info</span></button>
                                                <button class="btn btn-primary1-light mt-sm-0 mt-2" id="continue-payment-trigger"><span data-bm="Teruskan Pembayaran" data-en="Continue Payment">Continue Payment</span><i class="bi bi-credit-card-2-front align-middle ms-2 d-inline-block"></i></button>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade border-0 p-0" id="delivery-tab-pane" role="tabpanel" aria-labelledby="delivery-tab-pane" tabindex="0">
                                            <div class="p-3 checkout-payment-success my-3">
                                                <div class="mb-4">
                                                    <h5 class="text-primary3 fw-semibold"><span data-bm="Pembayaran Menunggu" data-en="Pending Payment">Pending Payment</span></h5>
                                                </div>
                                                <div class="mb-4">
                                                    <span class="avatar avatar-xl avatar-rounded bg-warning-transparent svg-warning">
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256">
                                                            <circle cx="128" cy="128" r="96" opacity="0.2"></circle>
                                                            <line x1="128" y1="80" x2="128" y2="136" stroke="currentColor" stroke-linecap="round" stroke-width="16"></line>
                                                            <circle cx="128" cy="172" r="12" fill="currentColor"></circle>
                                                            <circle cx="128" cy="128" r="96" fill="none" stroke="currentColor" stroke-width="16"></circle>
                                                        </svg>
                                                    </span>
                                                </div>
                                                <div class="mb-4">
                                                    <p class="mb-1 fs-14"><span data-bm="Anda boleh membuat pembayaran sekarang." data-en="You can make your payment now.">You can make your payment now.</span></p>
                                                </div>
                                                <a href="https://laravelui.spruko.com/xintra/products" class="btn btn-primary"><span data-bm="Buat Pembayaran Sekarang" data-en="Make Payment Now">Make Payment Now</span><i class="bi bi-cart ms-2"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xxl-3">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title me-1"><span data-bm="Ringkasan Pesanan" data-en="Order Summary">Order Summary</span></div><span class="badge bg-primary-transparent rounded-pill">02</span>
                                </div>
                                <div class="card-body p-0">
                                    <ul class="list-group mb-0 border-0 rounded-0">
                                        <li class="list-group-item p-3 border-top-0">
                                            <div class="d-flex align-items-center flex-wrap">
                                                <span class="avatar avatar-lg bg-light me-2">
                                                    <img src="https://laravelui.spruko.com/xintra/build/assets/images/ecommerce/png/9.png" alt="">
                                                </span>
                                                <div class="flex-fill">
                                                    <p class="mb-0 fw-semibold">Versatile Hoodie</p>
                                                    <p class="mb-0 text-muted fs-12">Quantity : 2  <span class="badge bg-success-transparent ms-3">30% Off</span></p>
                                                </div>
                                                <div>
                                                    <p class="mb-0 text-end">
                                                        <a href="javascript:void(0)">
                                                            <i class="ri-close-line fs-16 text-muted"></i>
                                                        </a>
                                                    </p>
                                                    <p class="mb-0 fs-14 fw-semibold">$189<span class="ms-1 text-muted fs-11 d-inline-block fw-normal"><s>$329</s></span></p>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="list-group-item p-3  border-bottom border-block-end-dashed">
                                            <div class="d-flex align-items-center flex-wrap">
                                                <span class="avatar avatar-lg bg-light me-2">
                                                    <img src="https://laravelui.spruko.com/xintra/build/assets/images/ecommerce/png/7.png" alt="">
                                                </span>
                                                <div class="flex-fill">
                                                    <p class="mb-0 fw-semibold">Leather Hand Bag</p>
                                                    <p class="mb-0 text-muted fs-12">Quantity : 1  <span class="badge bg-success-transparent ms-3">10% Off</span></p>
                                                </div>
                                                <div>
                                                    <p class="mb-0 text-end">
                                                        <a href="javascript:void(0)">
                                                            <i class="ri-close-line fs-16 text-muted"></i>
                                                        </a>
                                                    </p>
                                                    <p class="mb-0 fs-14 fw-semibold">$187<span class="ms-1 text-muted fs-11 d-inline-block fw-normal"><s>$139</s></span></p>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                    <div class="p-3 border-bottom border-block-end-dashed">
                                        <div class="d-flex align-items-center justify-content-between flex-wrap">
                                            <div class="fs-12 fw-semibold bg-primary-transparent badge badge-md rounded">SPRUKO25</div>
                                            <div class="text-success">COUPON APPLIED</div>
                                        </div>
                                    </div>
                                    <div class="p-3 border-bottom border-block-end-dashed">
                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <div class="text-muted"><span data-bm="Jumlah Kecil" data-en="Sub Total">Sub Total</span></div>
                                            <div class="fw-semibold fs-14">$318</div>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <div class="text-muted"><span data-bm="Diskaun" data-en="Discount">Discount</span></div>
                                            <div class="fw-semibold fs-14 text-success">10% - $31.8</div>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <div class="text-muted"><span data-bm="Caj Penghantaran" data-en="Delivery Charges">Delivery Charges</span></div>
                                            <div class="fw-semibold fs-14 text-danger">- $29</div>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="text-muted"><span data-bm="Cukai Perkhidmatan" data-en="Service Tax">Service Tax</span> (18%)</div>
                                            <div class="fw-semibold fs-14">- $45.29</div>
                                        </div>
                                    </div>
                                    <div class="p-3">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="fs-15"><span data-bm="Jumlah :" data-en="Total :">Total :</span></div>
                                            <div class="fw-semibold fs-16 text-dark"> $1,387</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
    
@endsection

@push('scripts')
@endpush
