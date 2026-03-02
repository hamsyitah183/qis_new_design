@extends('pages.app')

@section('pageName', 'Activity Log')

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
    <style>
        .filter-dropdown {
            width: 500px;
        }

        @media (max-width: 768px) {
            .filter-dropdown {
                width: 100%;
            }
        }

        /* Ensure dropdown overlaps the card body */
        .card.overflow-hidden {
            overflow: visible !important;
        }
        
        /* Make sure the header has a high z-index so dropdowns show above the table below */
        /* Removed to keep buttons on the same layer as the table, while the dropdown menu naturally overlays */
        .card-header {
            position: relative;
            /* z-index: 1050; */
        }

        /* Ensure flatpickr datepicker sits clearly above the filter dropdown */
        .flatpickr-calendar {
            z-index: 1060 !important;
            position: absolute !important;
        }

        /* Allow dropdown menu to not clip out the calendar */
        .filter-dropdown {
            overflow: visible !important;
        }
    </style>
@endpush

@push('scripts')
    @vite(['resources/js/pages/internal/user_management/activity_log.js'])
@endpush


@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Dashboard', 'url' => '/'], 
    
    ['label' => 'Activity Log', 'url' => '#']]" title="Activity Log">

    </x-breadcrumb>
@endsection

@section('content')
    <div class="row">
        <div class="row justify-content-center">
            <div class="col-xxl-11">
                <div class="card custom-card border overflow-hidden">

                    <div class="card-header">
                        <div class="card-title">Activity Log</div>
                        <div class="ms-auto d-flex gap-2 align-items-center">

                            <button class="btn btn-sm btn-info" id="openExportModal">
                                <i class="ri-download-cloud-2-line"></i> Download Report
                            </button>

                            <button class="btn btn-sm btn-primary filter dropdown-toggle" type="button"
                                id="activityFilterDropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                                aria-expanded="false">
                                <span class="me-2"><i class="ti ti-adjustments-horizontal"></i></span>
                                Filter
                            </button>

                            <ul class="dropdown-menu p-3 filter-dropdown" aria-labelledby="activityFilterDropdown">

                                {{-- User Type --}}
                                <li class="mb-2">
                                    <label class="form-label fw-semibold mb-1">User Type</label>
                                    <select class="form-select form-select-sm" id="userType">
                                        <option selected value="0">Choose User Type</option>
                                        <option value="public">Public</option>
                                        <option value="internal">Internal</option>
                                    </select>
                                </li>

                                {{-- User Account --}}
                                <li class="mb-3">
                                    <label class="form-label fw-semibold mb-1" id="accountUserModalLabel">User Account</label>
                                    <div class="btn btn-sm btn-outline-primary w-100 mb-2" id="userAccountBtn" style="pointer-events: none; opacity: 0.6;">
                                        <i class="ti ti-user me-1"></i> Choose User
                                    </div>
                                    <div id="userAccountContainer" class="d-none border rounded p-2 bg-white mt-2">
                                        <div class="input-group input-group-sm mb-2">
                                            <input type="text" class="form-control" placeholder="Search Name...." id="searchUserInput">
                                            <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="categoryDropdown">Category</button>
                                            <ul class="dropdown-menu dropdown-menu-end" id="categoryDropdownMenu">
                                                <li><a class="dropdown-item" href="javascript:void(0);">All</a></li>
                                                <li><a class="dropdown-item" href="javascript:void(0);">Individual</a></li>
                                                <li><a class="dropdown-item" href="javascript:void(0);">Company</a></li>
                                            </ul>
                                        </div>
                                        <div class="row g-2 scrollable-grey" id="userList" style="max-height: 150px; overflow-y: auto;">
                                        </div>
                                    </div>
                                </li>

                                {{-- Date Range --}}
                                <li class="mb-2">
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <label class="form-label fw-semibold mb-1">Start Date &amp; Time</label>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-text text-muted">
                                                    <i class="ri-calendar-line"></i>
                                                </div>
                                                <input type="text" id="startDateTime" class="form-control"
                                                    placeholder="Select start date &amp; time">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label fw-semibold mb-1">End Date &amp; Time</label>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-text text-muted">
                                                    <i class="ri-calendar-line"></i>
                                                </div>
                                                <input type="text" id="endDateTime" class="form-control"
                                                    placeholder="Select end date &amp; time">
                                            </div>
                                        </div>
                                    </div>
                                </li>

                                <li class="d-flex justify-content-end gap-2 mt-2 pt-2 border-top">
                                    <button class="btn btn-sm btn-secondary" id="clearAll">Reset</button>
                                    <button class="btn btn-sm btn-primary" id="find">Search</button>
                                </li>
                            </ul>
                        </div>
                    </div>


                    <div class="card-body bg-light">



                        <div class="timeline container">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="timeline-container">
                                        <div class="">
                                            <div class="timeline-end">
                                                <span
                                                    class="p-1 placeholder fs-11 bg-primary2 text-fixed-white backdrop-blur text-center border border-primary2 border-opacity-10 rounded-1 lh-1 fw-medium">

                                                </span>
                                            </div>
                                            <div class="timeline-continue">
                                                <div class="timeline-right">
                                                    <div class="timeline-content">
                                                        <p class="timeline-date text-muted mb-2 placeholder">

                                                        </p>
                                                        <div class="timeline-box">
                                                            <p class="mb-2 placeholder">

                                                            </p>
                                                            <p class="mb-2 placeholder">

                                                            </p>

                                                        </div>
                                                    </div>
                                                </div>


                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>




    {{-- Export Modal --}}
    <x-modal id="exportModal" title="Download Report" size="modal-dialog-centered">
        <div class="row g-3">
            <div class="col-md-6">
                <label for="exportMonth" class="form-label">Month</label>
                <select class="form-select" id="exportMonth">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ date('n') == $m ? 'selected' : '' }}>
                            {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label for="exportYear" class="form-label">Year</label>
                <select class="form-select" id="exportYear">
                    @foreach(range(date('Y'), date('Y') - 5) as $y)
                        <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        @slot('footer')
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-success" id="confirmExportExcel">
                <i class="ri-file-excel-2-line"></i> Download Excel
            </button>
            <button type="button" class="btn btn-danger" id="confirmExportPdf">
                <i class="ri-file-pdf-line"></i> Download PDF
            </button>
        @endslot
    </x-modal>
@endsection
