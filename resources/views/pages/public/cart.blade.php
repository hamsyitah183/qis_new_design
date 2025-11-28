@extends('pages.app')

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
@endpush

@push('scripts')
    <!-- vite -->
@endpush

@section('pageName', 'Cart')


@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Home', 'url' => '#']]" title="My Cart">

    </x-breadcrumb>
@endsection

@section('content')
                    <div class="row">
                        <div class="col-xl-9">
                            <div class="card custom-card overflow-hidden" id="cart-container-delete">
                                <div class="card-header">
                                    <div class="card-title">
                                        Ready Payment Application List
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table text-nowrap">
                                            <thead>
                                                <tr>
                                                    <th>
                                                        #
                                                    </th>
                                                    <th>
                                                        Application Details
                                                    </th>
                                                    <th class="text-center">
                                                        No. of Approved <br>Permit/Certificate
                                                    </th>
                                                    <th>
                                                        Total Price (RM)
                                                    </th>
                                                    <th>
                                                        Action
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr data-id="1" data-type="fruit">
                                                    <td style="text-align:center">1</td>
                                                    <td class="cart-items01">
                                                        <div class="d-flex align-items-center">
                                                            
                                                            <div class="flex-fill">
                                                                <div class="mb-1 fs-14 fw-semibold">
                                                                    <span class="me-1">Exporter :</span><span class="fw-medium text-bold text-decoration-underline">Serba Wangi Sdn Bhd</span>
                                                                </div>
                                                                <div class="d-flex gap-4 flex-wrap mb-1 align-items-center">
                                                                    <div>
                                                                        <span class="me-1">ETA:</span><span class="fw-medium text-muted">19-11-2025</span>
                                                                    </div>
                                                                    <div>
                                                                        <span class="me-1">Entry Point:</span><span class="fw-medium text-muted">Sepanggar Container Port</span>
                                                                    </div>
                                                                </div>
                                                                <span class="me-1">Type:</span>&nbsp;<span class="badge bg-success-transparent">Import Permit</span>
                                                                <!-- <span class="badge bg-primary3-transparent">Inspection Certificate</span>
                                                                <span class="badge bg-info-transparent">Consignment Certificate</span> -->
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="product-quantity-container">
                                                        <div class="input-group flex-nowrap gap-1 rounded-pill cart-input-group">
                                                            <input type="text" class="form-control form-control-sm rounded-pill text-center p-0" aria-label="quantity" value="1">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="fw-semibold fs-14 text-center">
                                                            554
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <a href="https://laravelui.spruko.com/xintra/wishlist" class="btn btn-icon btn-primary btn-sm me-1" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Add To Wishlist"><i class="ri-heart-line"></i></a>
                                                        <a href="javascript:void(0);" class="btn btn-icon btn-primary2 btn-sm btn-delete" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Remove From cart">
                                                            <i class="ri-delete-bin-line"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="card custom-card d-none" id="cart-empty-cart">
                                <div class="card-header">
                                    <div class="card-title">
                                        Empty Cart
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="cart-empty text-center">
                                        <span class="svg-muted">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="" width="24" height="24" viewBox="0 0 24 24"><path d="M18.6 16.5H8.9c-.9 0-1.6-.6-1.9-1.4L4.8 6.7c0-.1 0-.3.1-.4.1-.1.2-.1.4-.1h17.1c.1 0 .3.1.4.2.1.1.1.3.1.4L20.5 15c-.2.8-1 1.5-1.9 1.5zM5.9 7.1 8 14.8c.1.4.5.8 1 .8h9.7c.5 0 .9-.3 1-.8l2.1-7.7H5.9z"></path><path d="M6 10.9 3.7 2.5H1.3v-.9H4c.2 0 .4.1.4.3l2.4 8.7-.8.3zM8.1 18.8 6 11l.9-.3L9 18.5z"></path><path d="M20.8 20.4h-.9V20c0-.7-.6-1.3-1.3-1.3H8.9c-.7 0-1.3.6-1.3 1.3v.5h-.9V20c0-1.2 1-2.2 2.2-2.2h9.7c1.2 0 2.2 1 2.2 2.2v.4z"></path><path d="M8.9 22.2c-1.2 0-2.2-1-2.2-2.2s1-2.2 2.2-2.2c1.2 0 2.2 1 2.2 2.2s-1 2.2-2.2 2.2zm0-3.5c-.7 0-1.3.6-1.3 1.3 0 .7.6 1.3 1.3 1.3.8 0 1.3-.6 1.3-1.3 0-.7-.5-1.3-1.3-1.3zM18.6 22.2c-1.2 0-2.2-1-2.2-2.2s1-2.2 2.2-2.2c1.2 0 2.2 1 2.2 2.2s-.9 2.2-2.2 2.2zm0-3.5c-.8 0-1.3.6-1.3 1.3 0 .7.6 1.3 1.3 1.3.7 0 1.3-.6 1.3-1.3 0-.7-.5-1.3-1.3-1.3z"></path></svg>
                                        </span>
                                        <h3 class="fw-bold mb-1">Your Cart is Empty</h3>
                                        <h5 class="mb-3">Add some items to make me happy :)</h5>
                                        <a href="https://laravelui.spruko.com/xintra/products" class="btn btn-primary btn-wave m-3 waves-effect waves-light" data-abc="true">continue shopping <i class="bi bi-arrow-right ms-1"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3">	
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">
                                        Order Summary
                                    </div>
                                </div>
                                <div class="card-body p-0">                                    
                                    <div class="p-3 border-bottom border-block-end-dashed ">
                                        <div class="tab-pane show active overflow-hidden p-0 border-0" id="freeshipping-pane" role="tabpanel" aria-labelledby="freeshipping" tabindex="0">
                                            <div class="fs-12 text-muted mb-3"><i class="ri-information-fill"></i> Make sure list is what you want to pay</div>
                                            <div class="d-flex align-items-center justify-content-between mb-3">
                                                <div class="text-muted">Sub Total</div>
                                                <div class="fw-medium fs-14">$2,547</div>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-between mb-3">
                                                <div class="text-muted">Discount</div>
                                                <div class="fw-medium fs-14 text-success">0%</div>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-between mb-3">
                                                <div class="text-muted">Delivery Charges</div>
                                                <div class="fw-medium fs-14 text-danger">- RM0</div>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-between mb-3">
                                                <div class="text-muted">Service Tax (18%)</div>
                                                <div class="fw-medium fs-14">- RM0</div>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-between h5">
                                                <div class="fs-16">Total :</div>
                                                <div class="fw-semibold"> $2,254</div>
                                            </div>
                                            <div class="d-grid">
                                                <a href="https://laravelui.spruko.com/xintra/checkout" class="btn btn-primary btn-wave mb-2 waves-effect waves-light">Proceed To Checkout</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
    
@endsection

@push('scripts')
@endpush
