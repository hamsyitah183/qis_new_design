@extends('pages.app')


@section('pageName', 'Verification List')

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
@endpush

@push('scripts')
    @vite(['resources/js/pages/internal/user_management/verification_list.js'])
@endpush

@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Dashboard', 'url' => '/'], ['label' => 'User Verification List', 'url' => '#']]"
        title="User Verification List">
    </x-breadcrumb>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
               

                <div class="card-body">
                    <table id="verificationTable" class="table table-bordered text-nowrap w-100">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Verification Attachment</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Reason Modal -->
    <x-modal id="rejectModal" title="Reject Verification">
        <form id="rejectForm">
            @csrf
            <input type="hidden" id="rejectUserUuid" name="user_uuid">

            <div class="mb-3">
                <label for="rejectReason" class="form-label">Reason for Rejection</label>
                <textarea class="form-control" id="rejectReason" name="reason" rows="3" required></textarea>
            </div>

            @slot('footer')
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-danger" id="confirmRejectBtn">Reject</button>
            @endslot
        </form>
    </x-modal>

    <!-- Verification Modal -->
    <x-modal id="verificationModal" title="User Verification">
        <div class="mb-2 fs-14"><span class="fw-bold me-2 ">User IC: </span> <span class="ic"></span></div>

        <div class="" id="userIC"></div>

        <div class="status mt-3"></div>
        <div class="fs-12 mt-3"> <span class="fw-bold">Submitted On: </span> <span class="updated_at text-muted"></span>
        </div>

        @slot('footer')
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        @endslot
    </x-modal>
@endsection