@extends('pages.app')

@section('pageName', 'State & District Management')

@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Home', 'url' => '#']]" title="State & District Management">
    </x-breadcrumb>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Manage States and Districts</div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-xl-12">
                            <p class="text-muted">Select a state to manage its districts</p>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered text-nowrap">
                            <thead>
                                <tr>
                                    <th>State Name</th>
                                    <th>District Count</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="statesTableBody">
                                <!-- States will be loaded here via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- District Management Modal -->
    <div class="modal fade" id="districtManagementModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalStateTitle">Manage Districts</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Add New District</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="newDistrictInput" placeholder="Enter district name">
                            <button class="btn btn-primary" type="button" id="addDistrictBtn">
                                <i class="ri-add-line"></i> Add
                            </button>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="form-label fw-semibold">Districts List</label>
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto; border: 1px solid #ddd; border-radius: 4px;">
                            <table class="table table-bordered table-sm mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>District Name</th>
                                        <th style="width: 80px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="districtsList">
                                    <!-- Districts will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let currentStateId = null;
        const districtModal = new bootstrap.Modal(document.getElementById('districtManagementModal'));

        // Load states on page load
        function loadStates() {
            fetch('/api/states')
                .then(response => response.json())
                .then(states => {
                    const tbody = document.getElementById('statesTableBody');
                    tbody.innerHTML = '';
                    
                    if (states.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted">No states found</td></tr>';
                        return;
                    }
                    
                    states.forEach(state => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td><strong>${state.name}</strong></td>
                            <td><span class="badge bg-info">${state.districts_count || 0}</span></td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="editState(${state.id}, '${state.name}')">
                                    <i class="ri-edit-line"></i> Manage
                                </button>
                            </td>
                        `;
                        tbody.appendChild(row);
                    });
                })
                .catch(error => {
                    console.error('Error loading states:', error);
                    alert('Error loading states. Please refresh the page.');
                });
        }

        // Edit state districts
        function editState(stateId, stateName) {
            currentStateId = stateId;
            document.getElementById('modalStateTitle').textContent = `Manage Districts for ${stateName}`;
            document.getElementById('newDistrictInput').value = '';
            
            // Load districts for this state
            fetch(`/api/districts/${stateId}`)
                .then(response => response.json())
                .then(districts => {
                    const tbody = document.getElementById('districtsList');
                    tbody.innerHTML = '';
                    
                    if (districts.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="2" class="text-center text-muted">No districts found</td></tr>';
                    } else {
                        districts.forEach(district => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${district.name}</td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-danger" onclick="deleteSingleDistrict(${district.id}, '${district.name}')">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </td>
                            `;
                            tbody.appendChild(row);
                        });
                    }
                    
                    // Show modal
                    districtModal.show();
                })
                .catch(error => {
                    console.error('Error loading districts:', error);
                    alert('Error loading districts. Please try again.');
                });
        }

        // Delete single district
        function deleteSingleDistrict(districtId, districtName) {
            if (confirm(`Are you sure you want to delete "${districtName}"?`)) {
                fetch(`/api/districts/${districtId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Reload districts
                        const stateName = document.getElementById('modalStateTitle').textContent.replace('Manage Districts for ', '');
                        editState(currentStateId, stateName);
                    } else {
                        alert('Error deleting district: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting district. Please try again.');
                });
            }
        }

        // Add new district
        document.getElementById('addDistrictBtn').addEventListener('click', function() {
            const districtName = document.getElementById('newDistrictInput').value.trim();
            
            if (!districtName) {
                alert('Please enter a district name');
                return;
            }

            // Disable button during submission
            this.disabled = true;

            fetch('/api/districts', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    name: districtName,
                    state_id: currentStateId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('newDistrictInput').value = '';
                    // Reload districts
                    const stateName = document.getElementById('modalStateTitle').textContent.replace('Manage Districts for ', '');
                    editState(currentStateId, stateName);
                    loadStates(); // Refresh states table to update count
                } else {
                    alert('Error adding district: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error adding district. Please try again.');
            })
            .finally(() => {
                document.getElementById('addDistrictBtn').disabled = false;
            });
        });

        // Load states on page load
        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('statesTableBody')) {
                loadStates();
            }
        });
    </script>
@endpush
