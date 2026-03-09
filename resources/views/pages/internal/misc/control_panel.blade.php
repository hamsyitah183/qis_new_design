@extends('pages.app')

@section('pageName', 'Control Panel')


@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Home', 'url' => '#']]" title="System Control Panel">

    </x-breadcrumb>
@endsection

@section('content')

    <div class="row mb-5">
        <div class="col-xl-3">
            <div class="card custom-card">
                <div class="card-body">
                    <ul class="nav nav-tabs flex-column nav-tabs-header mb-0 mail-settings-tab" role="tablist">
                        <li class="nav-item me-0" role="presentation">
                            <a class="nav-link" data-bs-toggle="tab" role="tab" aria-current="page" href="#email-settings"
                                aria-selected="false" tabindex="-1"><i
                                    class="ri-map-pin-line me-2 align-middle fs-14 lh-1 text-primary"></i>District Entry</a>
                        </li>

                        <li class="nav-item me-0" role="presentation">
                            <a class="nav-link" data-bs-toggle="tab" role="tab" aria-current="page" href="#security"
                                aria-selected="false" tabindex="-1"><i
                                    class="ri-flag-2-line me-2 align-middle fs-14 lh-1 text-primary"></i> Consignment
                                Purpose</a>
                        </li>
                        <li class="nav-item me-0" role="presentation">
                            <a class="nav-link" data-bs-toggle="tab" role="tab" aria-current="page"
                                href="#notification-settings" aria-selected="false" tabindex="-1"><i
                                    class="ri-ruler-line me-2 align-middle fs-14 lh-1 text-primary"></i>Unit Measurement</a>
                        </li>
                        <li class="nav-item me-0" role="presentation">
                            <a class="nav-link active" data-bs-toggle="tab" role="tab" aria-current="page"
                                href="#account-settings" aria-selected="true"><i
                                    class="ri-folders-line me-2 align-middle fs-14 lh-1 text-primary"></i>Condition
                                Category</a>
                        </li>
                        <li class="nav-item me-0" role="presentation">
                            <a class="nav-link " data-bs-toggle="tab" role="tab" aria-current="page"
                                href="#rejection-settings" aria-selected="true"><i
                                    class="ri-file-shield-line me-2 align-middle fs-14 lh-1 text-danger"></i>Rejection
                                Notes</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-xl-9">
            <div class="card custom-card">
                <div class="card-body p-0">
                    <div class="tab-content border-0">
                        <!-- tab1 -->
                        @include('pages.internal.misc.cp_tab1')
                        <!-- tab2 -->
                        @include('pages.internal.misc.cp_tab2')
                        <!-- tab3 -->
                        @include('pages.internal.misc.cp_tab3')
                        <!-- tab4 -->
                        @include('pages.internal.misc.cp_tab4')
                        <!-- tab5 -->
                        @include('pages.internal.misc.cp_tab5')

                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- edit modal -->
    <div class="modal fade" id="editItemModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">

                <!-- Header -->
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ri-edit-line me-1"></i> Edit Item
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <!-- Body -->
                <form id="edititemmdl">
                    <div class="modal-body">
                        <input type="hidden" id="editItemId">
                        <div class="mb-3">
                            <label for="editICOde" class="form-label">Item Code</label>
                            <input type="text" id="editICOde" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label for="editDesc" class="form-label">Item Description</label>
                            <input type="text" id="editDesc" class="form-control">
                        </div>

                        <div id="conversionContainer"></div>
                    </div>
                </form>
                <!-- Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="button" id="saveEditBtn" class="btn btn-primary">
                        Save Changes
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- add modal -->
    <div class="modal fade" id="addGenericModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header ">
                    <h5 class="modal-title" id="addModalTitle">Add Something</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <form id="addGenericForm">
                    <div class="modal-body" id="modalFields">
                        <input type="hidden" id="addItemType">
                        <div class="mb-3">
                            <label for="addCodev" class="form-label">Item Code</label>
                            <input type="text" id="addCodev" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label for="addDescv" class="form-label">Item Description</label>
                            <input type="text" id="addDescv" class="form-control">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="saveGenericBtn">Save</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    {{-- <x-modal id="editItemModal" title="Edit Item" size="modal-md">
        <form id="edititemmdl">
            @csrf

            <input type="hidden" id="editItemId">

            <div class="mb-3 d-none">
                <label for="editICOde" class="form-label">Item Code</label>
                <input type="text" id="editICOde" class="form-control">
            </div>

            <div class="mb-3">
                <label for="editDesc" class="form-label">Name</label>
                <input type="text" id="editDesc" class="form-control">
            </div>

            @slot('footer')
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                Cancel
            </button>
            <button type="button" id="saveEditBtn" class="btn btn-primary">
                Save Changes
            </button>
            @endslot
        </form>
    </x-modal>


    <x-modal id="addGenericModal" title="Add Item" size="modal-md">
        <form id="addGenericForm">
            @csrf

            <input type="hidden" id="addItemType">
            <div class="mb-3 d-none">
                <label for="addCodev" class="form-label">Item Code</label>
                <input type="text" id="addCodev" class="form-control">
            </div>

            <div class="mb-3">
                <label for="addDescv" class="form-label">Name</label>
                <input type="text" id="addDescv" class="form-control">
            </div>

            @slot('footer')
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary" id="saveGenericBtn">Save</button>
            @endslot
        </form>
    </x-modal>--}}

    <x-modal id="entryPointModal" title="Entry Point" size="modal-md">
        <form id="entryPointForm">
            @csrf

            <input type="hidden" id="districtId" name="district_id">

            <div class="mb-3">
                <label class="form-label fw-semibold">Entry Points</label>

                <div id="placeList" class="vstack gap-2">
                    <!-- dynamic rows -->
                </div>

                <button type="button" class="btn btn-sm btn-outline-primary mt-3" id="addPlaceBtn">
                    + Add Place
                </button>
            </div>

            @slot('footer')
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                Cancel
            </button>
            <button type="button" id="submitEntryPoint" class="btn btn-primary">
                Save
            </button>
            @endslot
        </form>
    </x-modal>


@endsection

@push('scripts')
    <script>
        window.baseUrl = "{{ url('/') }}";
    </script>

    @vite(['resources/js/pages/internal/misc/control_panel.js'])
@endpush