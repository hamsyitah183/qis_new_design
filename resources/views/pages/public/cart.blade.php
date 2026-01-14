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

                                    <th>
                                        Total Price (RM)
                                    </th>

                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($permits as $index => $permit)
                      
                                    <tr data-id="{{ $permit->id }}" data-type="permit">
                                        <td style="text-align:center">
                                            {{ $index + 1 }}
                                        </td>

                                        <td class="cart-items01">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-fill">
                                                    <div class="mb-1 fs-14 fw-semibold">
                                                        <span class="me-1">Exporter :</span>
                                                        <span class="fw-medium text-bold text-decoration-underline">
                                                            {{ $application->exporter->name ?? '-' }}
                                                        </span>
                                                    </div>

                                                    <div class="d-flex gap-4 flex-wrap mb-1 align-items-center">
                                                        <div>
                                                            <span class="me-1">ETA:</span>
                                                            <span class="fw-medium text-muted">
                                                                {{ optional($application->eta)->format('d-m-Y') ?? '-' }}
                                                            </span>
                                                        </div>

                                                        <div>
                                                            <span class="me-1">Entry Point:</span>
                                                            <span class="fw-medium text-muted">
                                                                {{ $application->entryPoint->entry_name ?? '-' }}
                                                            </span>
                                                        </div>
                                                    </div>

                                                    <span class="me-1">Type:</span>
                                                    <span class="badge bg-success-transparent">
                                                        {{ $permit->type ?? 'Import Permit' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            <div class="fw-semibold fs-14 text-center">
                                                RM {{ $permit->value ?? '' }}
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach

                            </tbody>
                        </table>
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
                        <div class="tab-pane show active overflow-hidden p-0 border-0" id="freeshipping-pane"
                            role="tabpanel" aria-labelledby="freeshipping" tabindex="0">
                            <div class="fs-12 text-muted mb-3"><i class="ri-information-fill"></i> Make sure list is what
                                you want to pay</div>
                           
                           
                            <div class="d-flex align-items-center justify-content-between h5">
                                <div class="fs-16">Total :</div>
                                <div class="fw-semibold"> $2,254</div>
                            </div>
                            <div class="d-grid">
                                <button class="btn btn-primary btn-wave mb-2 waves-effect waves-light">Pay Now</button>
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
