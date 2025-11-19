<!-- <div class="wizard-step" data-title="PERMIT ITEM DETAILS" data-id="H53WJiv9blN17MYTztq4g8U6eSVkaZDx" data-step="2">
                                                        <div class="row justify-content-center summary-view">
                                                            <div class="table-responsive">
                                                                <table id="itemListTbl" class="table text-nowrap">
                                                                    <thead class="table-success">
                                                                        <tr>
                                                                            <th scope="col">#</th>
                                                                            <th scope="col">Item Name</th>
                                                                            <th scope="col">Quantity</th>
                                                                            <th scope="col">Purpose</th>
                                                                            <th scope="col">Uses</th>
                                                                            <th scope="col">Value</th>
                                                                            <th scope="col">Uploaded Item</th>
                                                                            <th scope="col" style="text-align: center">Action</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @forelse ($consignmentDetails as $index => $item)
<tr>
                                                                                <td>{{ $index + 1 }}</td>
                                                                                <td>{{ $item['item_name'] ?? '—' }}</td>
                                                                                <td>{{ $item['quantity'] ?? '—' }}</td>
                                                                                <td>{{ $item['measure'] ?? '—' }}</td>
                                                                                <td>{{ $item['purpose'] ?? '—' }}</td>
                                                                                <td>{{ $item['uses'] ?? '—' }}</td>
                                                                                <td>{{ $item['value'] ?? '—' }}</td>
                                                                            </tr>
                                                            @empty
                                                                            <tr>
                                                                                <td colspan="7" class="text-center text-muted">
                                                                                    No consignment items found.
                                                                                </td>
                                                                            </tr>
@endforelse
                                                                        <tr>
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
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                                
                                                            </div>
                                                            
                                                        </div>
                                                    </div> -->
