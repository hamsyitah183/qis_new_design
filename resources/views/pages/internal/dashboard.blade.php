@extends('pages.app')


@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Home', 'url' => '#']]" title="Dashboard">

    </x-breadcrumb>
@endsection

@section('content')
    <div class="row row-cols row-cols-xl-5">
        <div class="col-xxl col-xl-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex gap-3 align-items-center">
                        <div class="flex-fill">
                            <p class="text-muted fw-medium mb-2">Draft</p>
                            <h4 class="mb-1">47,784</h4>

                        </div>
                        <span
                            class="avatar avatar-lg bg-primary-transparent svg-primary avatar-rounded border-3 border border-opacity-50 flex-shrink-0 border-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#000000"
                                viewBox="0 0 256 256">
                                <path d="M224,80l-96,56L32,80l96-56Z" opacity="0.2"></path>
                                <path
                                    d="M230.91,172A8,8,0,0,1,228,182.91l-96,56a8,8,0,0,1-8.06,0l-96-56A8,8,0,0,1,36,169.09l92,53.65,92-53.65A8,8,0,0,1,230.91,172ZM220,121.09l-92,53.65L36,121.09A8,8,0,0,0,28,134.91l96,56a8,8,0,0,0,8.06,0l96-56A8,8,0,1,0,220,121.09ZM24,80a8,8,0,0,1,4-6.91l96-56a8,8,0,0,1,8.06,0l96,56a8,8,0,0,1,0,13.82l-96,56a8,8,0,0,1-8.06,0l-96-56A8,8,0,0,1,24,80Zm23.88,0L128,126.74,208.12,80,128,33.26Z">
                                </path>
                            </svg>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl col-xl-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex gap-3 align-items-center">
                        <div class="flex-fill">
                            <p class="text-muted fw-medium mb-2">Waiting Verification</p>
                            <h4 class="mb-1">47,784</h4>

                        </div>
                        <span
                            class="avatar avatar-lg bg-primary-transparent svg-primary avatar-rounded border-3 border border-opacity-50 flex-shrink-0 border-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#000000"
                                viewBox="0 0 256 256">
                                <path d="M224,80l-96,56L32,80l96-56Z" opacity="0.2"></path>
                                <path
                                    d="M230.91,172A8,8,0,0,1,228,182.91l-96,56a8,8,0,0,1-8.06,0l-96-56A8,8,0,0,1,36,169.09l92,53.65,92-53.65A8,8,0,0,1,230.91,172ZM220,121.09l-92,53.65L36,121.09A8,8,0,0,0,28,134.91l96,56a8,8,0,0,0,8.06,0l96-56A8,8,0,1,0,220,121.09ZM24,80a8,8,0,0,1,4-6.91l96-56a8,8,0,0,1,8.06,0l96,56a8,8,0,0,1,0,13.82l-96,56a8,8,0,0,1-8.06,0l-96-56A8,8,0,0,1,24,80Zm23.88,0L128,126.74,208.12,80,128,33.26Z">
                                </path>
                            </svg>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl col-xl-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex gap-3 align-items-center">
                        <div class="flex-fill">
                            <p class="text-muted fw-medium mb-2">Waiting Approval</p>
                            <h4 class="mb-1">47,784</h4>

                        </div>
                        <span
                            class="avatar avatar-lg bg-primary-transparent svg-primary avatar-rounded border-3 border border-opacity-50 flex-shrink-0 border-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#000000"
                                viewBox="0 0 256 256">
                                <path d="M224,80l-96,56L32,80l96-56Z" opacity="0.2"></path>
                                <path
                                    d="M230.91,172A8,8,0,0,1,228,182.91l-96,56a8,8,0,0,1-8.06,0l-96-56A8,8,0,0,1,36,169.09l92,53.65,92-53.65A8,8,0,0,1,230.91,172ZM220,121.09l-92,53.65L36,121.09A8,8,0,0,0,28,134.91l96,56a8,8,0,0,0,8.06,0l96-56A8,8,0,1,0,220,121.09ZM24,80a8,8,0,0,1,4-6.91l96-56a8,8,0,0,1,8.06,0l96,56a8,8,0,0,1,0,13.82l-96,56a8,8,0,0,1-8.06,0l-96-56A8,8,0,0,1,24,80Zm23.88,0L128,126.74,208.12,80,128,33.26Z">
                                </path>
                            </svg>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl col-xl-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex gap-3 align-items-center">
                        <div class="flex-fill">
                            <p class="text-muted fw-medium mb-2">Waiting Payment</p>
                            <h4 class="mb-1">47,784</h4>

                        </div>
                        <span
                            class="avatar avatar-lg bg-primary-transparent svg-primary avatar-rounded border-3 border border-opacity-50 flex-shrink-0 border-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#000000"
                                viewBox="0 0 256 256">
                                <path d="M224,80l-96,56L32,80l96-56Z" opacity="0.2"></path>
                                <path
                                    d="M230.91,172A8,8,0,0,1,228,182.91l-96,56a8,8,0,0,1-8.06,0l-96-56A8,8,0,0,1,36,169.09l92,53.65,92-53.65A8,8,0,0,1,230.91,172ZM220,121.09l-92,53.65L36,121.09A8,8,0,0,0,28,134.91l96,56a8,8,0,0,0,8.06,0l96-56A8,8,0,1,0,220,121.09ZM24,80a8,8,0,0,1,4-6.91l96-56a8,8,0,0,1,8.06,0l96,56a8,8,0,0,1,0,13.82l-96,56a8,8,0,0,1-8.06,0l-96-56A8,8,0,0,1,24,80Zm23.88,0L128,126.74,208.12,80,128,33.26Z">
                                </path>
                            </svg>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl col-xl-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex gap-3 align-items-center">
                        <div class="flex-fill">
                            <p class="text-muted fw-medium mb-2">Total Completed</p>
                            <h4 class="mb-1">47,784</h4>

                        </div>
                        <span
                            class="avatar avatar-lg bg-primary-transparent svg-primary avatar-rounded border-3 border border-opacity-50 flex-shrink-0 border-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#000000"
                                viewBox="0 0 256 256">
                                <path d="M224,80l-96,56L32,80l96-56Z" opacity="0.2"></path>
                                <path
                                    d="M230.91,172A8,8,0,0,1,228,182.91l-96,56a8,8,0,0,1-8.06,0l-96-56A8,8,0,0,1,36,169.09l92,53.65,92-53.65A8,8,0,0,1,230.91,172ZM220,121.09l-92,53.65L36,121.09A8,8,0,0,0,28,134.91l96,56a8,8,0,0,0,8.06,0l96-56A8,8,0,1,0,220,121.09ZM24,80a8,8,0,0,1,4-6.91l96-56a8,8,0,0,1,8.06,0l96,56a8,8,0,0,1,0,13.82l-96,56a8,8,0,0,1-8.06,0l-96-56A8,8,0,0,1,24,80Zm23.88,0L128,126.74,208.12,80,128,33.26Z">
                                </path>
                            </svg>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl col-xl-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex gap-3 align-items-center">
                        <div class="flex-fill">
                            <p class="text-muted fw-medium mb-2">Total Rejected</p>
                            <h4 class="mb-1">47,784</h4>

                        </div>
                        <span
                            class="avatar avatar-lg bg-primary-transparent svg-primary avatar-rounded border-3 border border-opacity-50 flex-shrink-0 border-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#000000"
                                viewBox="0 0 256 256">
                                <path d="M224,80l-96,56L32,80l96-56Z" opacity="0.2"></path>
                                <path
                                    d="M230.91,172A8,8,0,0,1,228,182.91l-96,56a8,8,0,0,1-8.06,0l-96-56A8,8,0,0,1,36,169.09l92,53.65,92-53.65A8,8,0,0,1,230.91,172ZM220,121.09l-92,53.65L36,121.09A8,8,0,0,0,28,134.91l96,56a8,8,0,0,0,8.06,0l96-56A8,8,0,1,0,220,121.09ZM24,80a8,8,0,0,1,4-6.91l96-56a8,8,0,0,1,8.06,0l96,56a8,8,0,0,1,0,13.82l-96,56a8,8,0,0,1-8.06,0l-96-56A8,8,0,0,1,24,80Zm23.88,0L128,126.74,208.12,80,128,33.26Z">
                                </path>
                            </svg>
                        </span>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
