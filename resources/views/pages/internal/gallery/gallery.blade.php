@extends('pages.app')

@section('pageName', 'Gallery')

@push('scripts')
    <script>
        window.baseUrl = "{{ url('/') }}";
    </script>
    @vite(['resources/js/pages/internal/gallery/gallery.js'])
@endpush

@section('breadcrumb')
    <x-breadcrumb 
        :items="[
            ['label' => 'Dashboard', 'url' => '/', 'data-en' => 'Dashboard', 'data-bm' => 'Dashboard'],
            ['label' => 'Gallery', 'url' => '#', 'data-en' => 'Gallery', 'data-bm' => 'Galeri']
        ]" 
        title="Gallery"
        title_en="Gallery"
        title_bm="Galeri"
    >
    </x-breadcrumb>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title" data-en="Gallery List" data-bm="Senarai Galeri">Gallery List</div>
                    <div class="ms-auto d-flex gap-2 align-items-center">
                        <button type="button" class="btn btn-success btn-sm" id="btnAddGallery">
                            <i class="ti ti-plus me-1"></i> <span data-en="Add Picture" data-bm="Tambah Gambar">Add Picture</span>
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <table id="galleryTable" class="table table-bordered text-nowrap w-100">
                        <thead>
                            <tr>
                                <th data-en="Image" data-bm="Gambar">Image</th>
                                <th data-en="Name" data-bm="Nama">Name</th>
                                <th data-en="Description" data-bm="Keterangan">Description</th>
                                <th data-en="Uploaded By" data-bm="Dimuat Naik Oleh">Uploaded By</th>
                                <th data-en="Uploaded At" data-bm="Dimuat Naik Pada">Uploaded At</th>
                                <th data-en="Action" data-bm="Tindakan">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Add / Edit Modal --}}
    <x-modal id="galleryModal" title="Gallery" title_en="Gallery" title_bm="Galeri" size="modal-lg modal-dialog-centered">
        <form id="galleryForm">
            <input type="hidden" id="gallery_id" name="id">
            <div class="row gy-3">
                <div class="col-xl-12">
                    <label for="name" class="form-label" data-en="Name" data-bm="Nama">Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>

                <div class="col-xl-12">
                    <label for="description" class="form-label" data-en="Description" data-bm="Keterangan">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                </div>

                <div class="col-xl-12">
                    <label class="form-label" data-en="Image" data-bm="Gambar">Image <span class="text-danger">*</span></label>
                    <input type="file" class="form-control" id="image" name="image" accept="image/*">

                    {{-- Existing image preview (edit mode) --}}
                    <div id="existing_image_preview" class="mt-2" style="display:none;"></div>

                    {{-- New image preview --}}
                    <div id="new_image_preview" class="mt-2" style="display:none;"></div>
                </div>
            </div>
        </form>
        <x-slot name="footer">
            <div class="w-100 d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-en="Cancel" data-bm="Batal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnSaveGallery" data-en="Save" data-bm="Simpan">Save</button>
            </div>
        </x-slot>
    </x-modal>

    {{-- View Modal --}}
    <x-modal id="viewGalleryModal" title="View Image" title_en="View Image" title_bm="Lihat Gambar" size="modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="row gy-3">
            <div class="col-xl-12 text-center">
                <img id="view_image" src="" class="img-fluid rounded" style="max-height: 60vh; object-fit: contain;" alt="">
            </div>
            <div class="col-xl-12">
                <h5 id="view_name" class="fw-bold mb-1"></h5>
                <p id="view_description" class="text-muted"></p>
            </div>
        </div>
        <x-slot name="footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-en="Close" data-bm="Tutup">Close</button>
        </x-slot>
    </x-modal>
@endsection