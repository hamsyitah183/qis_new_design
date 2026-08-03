@extends('pages.app')

@php
    $user = authUser()['user'];
@endphp

@section('breadcrumb')
    <x-breadcrumb 
        :items="[
            ['label' => 'Home', 'url' => route('public.dashboard')],
            ['label' => 'Manage Exporters', 'url' => '#'],
        ]" 
        title="Manage My Exporters"
    >
    </x-breadcrumb>
@endsection

@section('content')
<div class="row">
    <div class="col-xl-12 d-flex justify-content-between align-items-center mb-4">
        <h4 class="page-title fw-semibold fs-18 mb-0" data-bm="Urus Pengeksport Saya" data-en="Manage My Exporters">Manage My Exporters</h4>
        <button type="button" class="btn btn-primary btn-wave" data-bs-toggle="modal" data-bs-target="#addExporterModal">
            <i class="ti ti-plus me-1"></i> <span data-bm="Tambah Pengeksport Baru" data-en="Add New Exporter">Add New Exporter</span>
        </button>
    </div>
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title" data-bm="Senarai Pengeksport" data-en="Exporters List">Exporters List</div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col" data-bm="Nama" data-en="Name">Name</th>
                                <th scope="col" data-bm="Nombor Telefon" data-en="Phone Number">Phone Number</th>
                                <th scope="col" data-bm="Alamat" data-en="Address">Address</th>
                                <th scope="col" data-bm="Negara" data-en="Country">Country</th>
                                <th scope="col" data-bm="Tindakan" data-en="Actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($exporters as $index => $exporter)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <span class="fw-semibold">{{ $exporter->name }}</span>
                                    </td>
                                    <td>{{ $exporter->phone_no ?? 'N/A' }}</td>
                                    <td>{{ $exporter->address ?? 'N/A' }}</td>
                                    <td>{{ $exporter->countryInfo->name ?? 'N/A' }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-icon btn-info" title="Edit" onclick="editExporter({{ $exporter->id }})">
                                            <i class="ti ti-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-icon btn-danger" title="Delete" onclick="deleteExporter({{ $exporter->id }})">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted" data-bm='Tiada pengeksport dijumpai. Klik "Tambah Pengeksport Baru" untuk bermula.' data-en='No exporters found. Click "Add New Exporter" to get started.'>No exporters found. Click "Add New Exporter" to get started.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Add Exporter Modal --}}
<div class="modal fade" id="addExporterModal" tabindex="-1" aria-labelledby="addExporterModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addExporterModalLabel" data-bm="Tambah Pengeksport Baru" data-en="Add New Exporter">Add New Exporter</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="exporterForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="exporterName" class="form-label"><span data-bm="Nama Pengeksport" data-en="Exporter Name">Exporter Name</span> <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="exporterName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="exporterPhone" class="form-label" data-bm="Nombor Telefon" data-en="Phone Number">Phone Number</label>
                        <input type="text" class="form-control" id="exporterPhone" name="phone_no">
                    </div>
                    <div class="mb-3">
                        <label for="exporterAddress" class="form-label" data-bm="Alamat" data-en="Address">Address</label>
                        <textarea class="form-control" id="exporterAddress" name="address" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="exporterCountry" class="form-label"><span data-bm="Negara" data-en="Country">Country</span> <span class="text-danger">*</span></label>
                        <select class="form-select" id="exporterCountry" name="country" required>
                            <option value="" data-bm="Pilih Negara" data-en="Select Country">Select Country</option>
                            {{-- Countries will be populated via AJAX --}}
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" data-bm="Batal" data-en="Cancel">Cancel</button>
                    <button type="submit" class="btn btn-primary" data-bm="Simpan Pengeksport" data-en="Save Exporter">Save Exporter</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Load countries when modal opens
        $('#addExporterModal').on('show.bs.modal', function() {
            loadCountries();
        });

        // Handle form submission
        $('#exporterForm').on('submit', function(e) {
            e.preventDefault();
            
            $.ajax({
                url: '{{ route("public.storeExp") }}',
                type: 'POST',
                data: $(this).serialize(),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#addExporterModal').modal('hide');
                    location.reload();
                },
                error: function(xhr) {
                    alert('Error adding exporter. Please try again.');
                }
            });
        });
    });

    function loadCountries() {
        // Load countries via AJAX or populate from a predefined list
        // For now, using a simple fetch
        fetch('/api/countries') // You may need to adjust this endpoint
            .then(response => response.json())
            .then(data => {
                const select = $('#exporterCountry');
                select.empty().append('<option value="">Select Country</option>');
                data.forEach(country => {
                    select.append(`<option value="${country.code}">${country.name}</option>`);
                });
            })
            .catch(() => {
                console.error('Failed to load countries');
            });
    }

    function editExporter(id) {
        // Implement edit functionality
        alert('Edit functionality to be implemented');
    }

    function deleteExporter(id) {
        if (confirm('Are you sure you want to delete this exporter?')) {
            $.ajax({
                url: '{{ route("public.deleteExp", ":id") }}'.replace(':id', id),
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    location.reload();
                },
                error: function(xhr) {
                    alert('Error deleting exporter. Please try again.');
                }
            });
        }
    }
</script>
@endpush
