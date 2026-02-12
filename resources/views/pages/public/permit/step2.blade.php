<div class="wizard-step" data-title="PERMIT ITEM DETAILS" data-id="H53WJiv9blN17MYTztq4g8U6eSVkaZDx" data-step="2">
    <div class="row justify-content-center summary-view">
        <div class="table-responsive">
            <table id="itemListTbl" class="table text-nowrap fs-12">
                <thead class="table-primary">
                    <tr>
                   
                        <th scope="col">Item Name</th>
                 
                        <th scope="col">Purpose</th>
                        <th scope="col">View More</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- <tr>
                        <td>1</td>
                        <td scope="row">Durian - Fresh Fruit</td>
                        <td>500 KG</td>
                        <td>Commercial (Trade)</td>
                        <td>Fresh Produce</td>
                        <td>RM 10,000</td>
                        <td></td>
                        <td style="text-align: center">
                            <button type="button" class="btn btn-sm btn-primary-light">Remove</button>
                        </td>
                    </tr> -->
                </tbody>
            </table>
            <div class="d-flex justify-content-end align-items-end">
                <input type="text" id="itemCountCheck" class="required" style="opacity: 0; position: absolute; pointer-events: none;" required>
                <button id="mdlAddItemBtn" type="button" class="btn btn-md btn-info mt-3" data-bs-toggle="modal"
                    data-bs-target="#addItemModal">
                    <i class="bx bx-plus me-1"></i> Add Item
                </button>
            </div>
        </div>

    </div>
</div>


{{-- modal --}}
<x-modal id="ItemDetailsModal" title="Item Details">

    <div id="itemDetailsInfo"></div>

    <hr>

    <p class="p-1 mt-3">
        <strong class = "me-1">
            <span class = "avatar avatar-sm avatar-rounded  bd-gray-500">
                <i class="fa-solid fa-file"></i>
            </span> Attachment(s)
        </strong>
    </p>

    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="itemFilesTable">
            <thead class="">
                <tr>
                    <th style="width: 45%">File Name</th>
                    <th style="width: 25%">File Type</th>
                    <th style="width: 15%" class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <!-- JS inserts rows here -->
            </tbody>
        </table>
    </div>

    @slot('footer')
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    @endslot

</x-modal>
