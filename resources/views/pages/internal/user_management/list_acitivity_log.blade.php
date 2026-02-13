@extends('pages.app')

@section('pageName', 'Activity Log')

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
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

                    <div class="card-header pb-2 d-block">
                        <div class="row">

                            <div class="col-6">
                                <span class="mb-1">User Type</span>
                                <select class="form-select form-select-lg" aria-label=".form-select-lg example"
                                    id="userType" disabled>
                                    <option selected="" value="0">Choose User Type</option>
                                    <option value="public">Public</option>
                                    <option value="internal">Internal</option>
                                </select>
                            </div>

                            <div class="col-6">
                                <span class="mb-1">User Account</span>
                                <div class="input-group">
                                    <div class="btn btn-md btn-primary w-100" id="userAccountBtn" disabled>
                                        Choose User
                                    </div>
                                </div>
                            </div>

                            <div class="col-6 mt-4">
                                <span class="mb-1">Start Date & Time</span>
                                <div class="input-group">
                                    <div class="input-group-text text-muted">
                                        <i class="ri-calendar-line"></i>
                                    </div>
                                    <input type="text" id="startDateTime" class="form-control flatpickr"
                                        placeholder="Select start date & time">
                                </div>
                            </div>

                            <div class="col-6 mt-4">
                                <span class="mb-1">End Date & Time</span>
                                <div class="input-group">
                                    <div class="input-group-text text-muted">
                                        <i class="ri-calendar-line"></i>
                                    </div>
                                    <input type="text" id="endDateTime" class="form-control flatpickr"
                                        placeholder="Select end date & time">
                                </div>
                            </div>

                        </div>

                        {{-- clear button --}}
                        <div class="me-auto pt-2 d-flex align-items-end justify-content-end">
                            <button class="btn btn-sm btn-info me-1" id="openExportModal">
                                <i class="ri-download-cloud-2-line"></i> Download Report
                            </button>
                            <button class="btn btn-sm btn-primary me-1" id="find">Search</button>
                            <button class="btn btn-sm btn-secondary" id="clearAll">Clear All</button>
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


    <x-modal id="accountUserModal" title="Choose User Account">
        <form id="accountUserList">
            <div class="input-group input-group-sm input-btn-outline mb-3">
                <input type="text" class="form-control" aria-label="Text input with dropdown button"
                    placeholder="Search Name...." id="searchUserInput">

                <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown"
                    aria-expanded="false" id="categoryDropdown">Category</button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="javascript:void(0);">All</a></li>
                    <li><a class="dropdown-item" href="javascript:void(0);">Individual</a></li>
                    <li><a class="dropdown-item" href="javascript:void(0);">Company</a></li>
                </ul>
            </div>

            <div>
                <div class="px-0 py-3 pb-0">
                    <div class="row g-2 scrollable-grey" id="userList">

                    </div>
                </div>

            </div>
        </form>

        @slot('footer')
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" form="userVerificationForm" class="btn btn-primary" id="submitBtn">Submit</button>
        @endslot
    </x-modal>

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
