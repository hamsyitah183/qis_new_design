@extends('pages.app')

@section('pageName', 'Control Panel')


@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => '/', 'data-en' => 'Dashboard', 'data-bm' => 'Dashboard'],
        ['label' => 'Control Panel', 'url' => '#', 'data-en' => 'Control Panel', 'data-bm' => 'Panel Kawalan'],
    ]" title="System Control Panel" title_en="System Control Panel"
        title_bm="Panel Kawalan Sistem">

    </x-breadcrumb>
@endsection

@section('content')

    <div class="row mb-5">
        <div class="col-xl-3">
            <div class="card custom-card">
                <div class="card-body">
                    <ul class="nav nav-tabs flex-column nav-tabs-header mb-0 mail-settings-tab" role="tablist">
                        <li class="nav-item me-0" role="presentation">
                            <a class="nav-link" data-bs-toggle="tab" role="tab" aria-current="page"
                                href="#email-settings" aria-selected="false" tabindex="-1"><i
                                    class="ri-map-pin-line me-2 align-middle fs-14 lh-1 text-primary"></i><span
                                    data-en="District Entry" data-bm="Pintu Masuk">District Entry</span></a>
                        </li>

                        <li class="nav-item me-0" role="presentation">
                            <a class="nav-link" data-bs-toggle="tab" role="tab" aria-current="page" href="#security"
                                aria-selected="false" tabindex="-1"><i
                                    class="ri-flag-2-line me-2 align-middle fs-14 lh-1 text-primary"></i><span
                                    data-en="Purpose of Import" data-bm="Tujuan Import">Purpose of Import</span></a>
                        </li>
                        <li class="nav-item me-0" role="presentation">
                            <a class="nav-link" data-bs-toggle="tab" role="tab" aria-current="page"
                                href="#notification-settings" aria-selected="false" tabindex="-1"><i
                                    class="ri-ruler-line me-2 align-middle fs-14 lh-1 text-primary"></i><span
                                    data-en="Unit Measurement" data-bm="Unit Ukuran">Unit Measurement</span></a>
                        </li>
                        <li class="nav-item me-0" role="presentation">
                            <a class="nav-link active" data-bs-toggle="tab" role="tab" aria-current="page"
                                href="#account-settings" aria-selected="true"><i
                                    class="ri-folders-line me-2 align-middle fs-14 lh-1 text-primary"></i><span
                                    data-en="Description Form" data-bm="Borang Penerangan">Description Form</span></a>
                        </li>
                        <li class="nav-item me-0" role="presentation">
                            <a class="nav-link " data-bs-toggle="tab" role="tab" aria-current="page"
                                href="#consignment-settings" aria-selected="true"><i
                                    class="ri-file-shield-line me-2 align-middle fs-14 lh-1 text-primary"></i><span
                                    data-en="Consignment Item Category"
                                    data-bm="Kategori Permohonan Konsainan">Consignment Item
                                    Category</span></a>
                        </li>
                        <li class="nav-item me-0" role="presentation">
                            <a class="nav-link " data-bs-toggle="tab" role="tab" aria-current="page"
                                href="#rejection-settings" aria-selected="true"><i
                                    class="ri-file-shield-line me-2 align-middle fs-14 lh-1 text-danger"></i><span
                                    data-en="Rejection Notes" data-bm="Nota Penolakan">Rejection
                                    Notes</span></a>
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

                        @include('pages.internal.misc.cp_tab6')
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
                        <i class="ri-edit-line me-1"></i> <span data-en="Edit Item" data-bm="Sunting Item">Edit Item</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <!-- Body -->
                <form id="edititemmdl">
                    <div class="modal-body">
                        <input type="hidden" id="editItemId">
                        <div class="mb-3">
                            <label for="editICOde" class="form-label" data-en="Item Code" data-bm="Kod Item">Item
                                Code</label>
                            <input type="text" id="editICOde" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label for="editDesc" class="form-label" data-en="Item Description"
                                data-bm="Penerangan Item">Item Description</label>
                            <input type="text" id="editDesc" class="form-control">
                        </div>

                        <div id="conversionContainer"></div>
                    </div>
                </form>
                <!-- Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal" data-en="Cancel"
                        data-bm="Batal">
                        Cancel
                    </button>
                    <button type="button" id="saveEditBtn" class="btn btn-primary" data-en="Save Changes"
                        data-bm="Simpan Perubahan">
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
                    <h5 class="modal-title" id="addModalTitle" data-en="Add Something" data-bm="Tambah">Add Something
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <form id="addGenericForm">
                    <div class="modal-body" id="modalFields">
                        <input type="hidden" id="addItemType">
                        <div class="mb-3">
                            <label for="addCodev" class="form-label" data-en="Item Code" data-bm="Kod Item">Item
                                Code</label>
                            <input type="text" id="addCodev" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label for="addDescv" class="form-label" data-en="Item Description"
                                data-bm="Penerangan Item">Item Description</label>
                            <input type="text" id="addDescv" class="form-control">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-en="Cancel"
                            data-bm="Batal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="saveGenericBtn" data-en="Save"
                            data-bm="Simpan">Save</button>
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
    </x-modal> --}}

    <x-modal id="entryPointModal" title="Entry Point" title_en="Entry Point" title_bm="Titik Masuk" size="modal-md">
        <form id="entryPointForm">
            @csrf

            <input type="hidden" id="districtId" name="district_id">

            <div class="mb-3">
                <label class="form-label fw-semibold" data-en="Entry Points" data-bm="Titik Masuk">Entry Points</label>

                <div id="placeList" class="vstack gap-2">
                    <!-- dynamic rows -->
                </div>

                <button type="button" class="btn btn-sm btn-outline-primary mt-3" id="addPlaceBtn">
                    <span data-en="+ Add Place" data-bm="+ Tambah Tempat">+ Add Place</span>
                </button>
            </div>

            @slot('footer')
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-en="Cancel" data-bm="Batal">
                    Cancel
                </button>
                <button type="button" id="submitEntryPoint" class="btn btn-primary" data-en="Save" data-bm="Simpan">
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
