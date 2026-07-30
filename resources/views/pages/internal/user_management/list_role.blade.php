@extends('pages.app')

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">

    <style>
        #roleTable tbody tr {
            cursor: pointer;
        }
    </style>
@endpush

@push('scripts')
    @vite(['resources/js/pages/internal/user_management/role_list.js'])
@endpush

@section('pageName', ' List Role')

@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Dashboard', 'url' => '/', 'data-en' => 'Dashboard', 'data-bm' => 'Dashboard'], ['label' => 'List Role', 'url' => '#', 'data-en' => 'List Role', 'data-bm' => 'Senarai Peranan']]" title="List Role" title_en="List Role" title_bm="Senarai Peranan" />
@endsection

@section('content')
    <!-- FLEX layout (important for animation) -->
    <div class="role-layout">

        <!-- LEFT TABLE -->
        <div id="roleTableWrapper">
            <div class="card custom-card">
                <div class="card-body">
                    <table id="roleTable" class="table table-hover table-bordered text-nowrap w-100">
                        <thead>
                            <tr>
                                <th data-en="Name" data-bm="Nama">Name</th>
                                {{-- <th class="text-center">User</th> --}}
                                <th class="text-center" style="width: 50%;" data-en="Permission" data-bm="Kebenaran">Permission</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>



    </div>


    <div class="modal fade" id="roleDetailsModal" tabindex="-1" aria-labelledby="roleDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="roleDetailsModalLabel" data-en="Role Details" data-bm="Butiran Peranan">Role Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="roleDetailsContentModal" data-en="Select a role to see details" data-bm="Pilih peranan untuk melihat butiran">
                    Select a role to see details
                </div>
            </div>
        </div>
    </div>

    <x-modal id="userModal" title="User List" title_en="User List" title_bm="Senarai Pengguna">

        <form id="userModalForm">
            @csrf
            <input type="hidden" name="role" id="roleVal">
            <div id="userListContainer"></div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-en="Close" data-bm="Tutup">Close</button>
                <button type="submit" class="btn btn-primary" id="updateRoleBtn" data-en="Submit" data-bm="Hantar">Submit</button>
            </div>
        </form>


    </x-modal>

    <x-modal id="permissionModal" title="Permission List" title_en="Permission List" title_bm="Senarai Kebenaran">

        <form id="permissionModalForm">
            @csrf
            <input type="hidden" name="role" id="roleVal">
          
            <div id="permissionListContainer"></div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-en="Close" data-bm="Tutup">Close</button>
                <button type="submit" class="btn btn-primary" id="updatePermissionBtn" data-en="Submit" data-bm="Hantar">Submit</button>
            </div>
        </form>


    </x-modal>
@endsection

{{-- <div class="avatar-list-stacked">
    <span class="avatar avatar-sm avatar-rounded">
        <img src="https://laravelui.spruko.com/xintra/build/assets/images/faces/2.jpg" alt="img">
    </span>
    <span class="avatar avatar-sm avatar-rounded">
        <img src="https://laravelui.spruko.com/xintra/build/assets/images/faces/8.jpg" alt="img">
    </span>
    <span class="avatar avatar-sm avatar-rounded">
        <img src="https://laravelui.spruko.com/xintra/build/assets/images/faces/2.jpg" alt="img">
    </span>
    <a class="avatar avatar-sm bg-primary text-fixed-white avatar-rounded" href="javascript:void(0);">
        +5
    </a>
</div> --}}
