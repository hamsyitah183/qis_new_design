<div class="tab-pane p-0" id="email-settings" role="tabpanel">
    <ul class="list-group list-group-flush rounded">
        <li class="list-group-item">
            <div class="col-xxl-11">
                <div class="card custom-card shadow-none mb-0">
                    <div class="card-header justify-content-between d-sm-flex d-block">
                        <div class="card-title" data-en="District Entry" data-bm="Pintu Masuk">District Entry</div>
                        <div class="mt-sm-0 mt-2">
                            <button class="btn btn-sm btn-primary" onclick="addmodal('entry')" data-type = "entry"><i
                                    class="ri-add-line me-1"></i> <span data-en="Add Entry Point" data-bm="Tambah Titik Masuk">Add Entry Point</span></button>
                        </div>
                    </div>
                    <div class="card-body">
                        <ul class="list-group">
                            <li class="list-group-item">
                                <div class="d-sm-flex d-block align-items-top">
                                    <table id="tabletab1" class="table table-striped text-nowrap table-bordered">
                                        <thead>
                                            <tr>
                                                {{-- <th scope="col">#</th> --}}
                                                {{-- <th scope="col">Code</th> --}}
                                                <th scope="col" data-en="Name" data-bm="Nama">Name</th>
                                                <th scope="col" data-en="Entry Points" data-bm="Titik Masuk">Entry Points</th>
                                                <th scope="col" data-en="Action" data-bm="Tindakan">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </li>
    </ul>
</div>
