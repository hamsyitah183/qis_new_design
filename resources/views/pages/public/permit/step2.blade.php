<div class="wizard-step" data-title="PERMIT ITEM DETAILS" data-id="H53WJiv9blN17MYTztq4g8U6eSVkaZDx" data-step="2">
    <div class="row justify-content-center summary-view">
        <div class="table-responsive">
            <table id="itemListTbl" class="table text-nowrap fs-12">
                <thead class="table-success">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Item Name</th>
                        <th scope="col">Quantity</th>
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


    @slot('footer')
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    @endslot


</x-modal>
