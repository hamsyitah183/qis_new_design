 <div class="wizard-step" data-title="SUMMARY" data-id="dOM0iRAyJXsLTr9b3KZfQ2jNv4pgn6Gu" data-limit="3" data-step="3">
     <div class="row">
         <div class="col-xl-6">
             <div class="border border-bottom-0 rounded-1 mb-3 ">
                 <div class="card-body p-0">
                     <div class="table-responsive">
                         <table class="table  text-nowrap">
                             <thead>
                                 <tr class="bg-light">
                                     <th scope="col">Importer & Exporter Details</th>
                                     <th scope="col"></th>
                                     <th scope="col"></th>
                                 </tr>
                             </thead>
                             <tbody>
                                 <tr>
                                     <td class="w-25">
                                         <span class="d-block fw-semibold text-end"><strong>Importer</strong>
                                             Name</span>
                                     </td>
                                     <td class="w-10">:</td>
                                     <td class="text-start  text-muted" id="importerName">
                                         {{ $application->importer->fullname }}
                                     </td>
                                 </tr>
                                 <tr>
                                     <td class="w-25">
                                         <span class="d-block fw-semibold text-end">&nbsp;&nbsp;&nbsp;&nbsp;
                                             Phone</span>
                                     </td>
                                     <td class="w-10">:</td>
                                     <td class="text-start text-muted" id="importerPhoneno">
                                         {{ $application->importer->phone_number }}
                                     </td>
                                 </tr>
                                 <tr>
                                     <td class="w-25">
                                         <span class="d-block fw-semibold text-end">&nbsp;&nbsp;&nbsp;&nbsp;
                                             Address</span>
                                     </td>
                                     <td class="w-10">:</td>
                                     <td class="text-start text-muted" id="simpAdd">
                                         {{ $application->importer->address_1 }} ,
                                         {{ $application->importer->address_2 }} ,
                                         {{ $application->importer->postcode }},
                                         {{ $application->importer->district }}
                                     </td>
                                 </tr>
                                 <tr>
                                     <td class="w-25">
                                         <span class="d-block fw-semibold text-end"><strong>Exporter</strong>
                                             Name</span>
                                     </td>
                                     <td class="w-10">:</td>
                                     <td class="text-start text-muted" id="sexpName">
                                         {{ $application->exporter->name }}
                                     </td>
                                 </tr>
                                 <tr>
                                     <td class="w-25">
                                         <span class="d-block fw-semibold text-end">&nbsp;&nbsp;&nbsp;&nbsp;
                                             Phone</span>
                                     </td>
                                     <td class="w-10">:</td>
                                     <td class="text-start text-muted" id="sexpfonno">
                                         {{ $application->exporter->phone_no }}
                                     </td>
                                 </tr>
                                 <tr>
                                     <td class="w-25">
                                         <span class="d-block fw-semibold text-end">&nbsp;&nbsp;&nbsp;&nbsp;
                                             Address</span>
                                     </td>
                                     <td class="w-10">:</td>
                                     <td class="text-start text-muted" id="sexpAddress">
                                         {{ $application->exporter->address }}
                                     </td>
                                 </tr>
                                 <tr>
                                     <td class="w-25">
                                         <span class="d-block fw-semibold text-end">&nbsp;&nbsp;&nbsp;&nbsp;
                                             Country</span>
                                     </td>
                                     <td class="w-10">:</td>
                                     <td class="text-start text-muted" id="sexpCountry">
                                         {{ $application->exporter->country }}
                                     </td>
                                 </tr>
                             </tbody>
                         </table>
                     </div>
                 </div>
             </div>
         </div>
         <div class="col-xl-6">
             <div class="border border-bottom-0 rounded-1 mb-3 ">
                 <div class="card-body p-0">
                     <div class="table-responsive">
                         <table id="summaryTable" class="table text-nowrap">
                             <thead>
                                 <tr class="bg-light">
                                     <th scope="col">Permit Details</th>
                                     <th scope="col"></th>
                                     <th scope="col"></th>
                                 </tr>
                             </thead>
                             <tbody>
                                 <tr>
                                     <td class="w-25">
                                         <span class="d-block fw-semibold">Estimated Time
                                             Arrival</span>
                                     </td>
                                     <td class="w-10">:</td>
                                     <td class="text-start  text-muted" id="seta">
                                         {{ $application->eta }}
                                     </td>
                                 </tr>
                                 <tr>
                                     <td class="w-25">
                                         <span class="d-block fw-semibold">Transport Type</span>
                                     </td>
                                     <td class="w-10">:</td>
                                     <td class="text-start  text-muted" id="strty">
                                         {{ $application->transport_type }}
                                     </td>
                                 </tr>
                                 <tr>
                                     <td class="w-25">
                                         <span class="d-block fw-semibold">Entry Point</span>
                                     </td>
                                     <td class="w-10">:</td>
                                     <td class="text-start  text-muted" id="sentryp">
                                         {{ $application->entry_point }}
                                     </td>
                                 </tr>
                             </tbody>
                             <tfooter>
                                 <tr>
                                     <td colspan="3">
                                         <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                             data-bs-target="#editExporterModal">
                                             <i class="bx bx-edit"></i> Edit Permit Detail
                                         </button>
                                     </td>
                                 </tr>

                                 <div class="modal fade" id="editExporterModal" tabindex="-1">
                                     <div class="modal-dialog modal-lg">
                                         <div class="modal-content">
                                             <div class="modal-header">
                                                 <h5 class="modal-title">Edit Application Detail
                                                 </h5>
                                                 <button type="button" class="btn-close"
                                                     data-bs-dismiss="modal"></button>
                                             </div>
                                             <div class="modal-body">
                                                 <div class="mb-3">
                                                     <label class="form-label">ETA</label>
                                                     <input type="date" class="form-control" id="edit_eta"
                                                         name="edit_eta">
                                                 </div>

                                                 <!-- Registration Number -->
                                                 <div class="mb-3">
                                                     <label class="form-label">Transport
                                                         Type</label>
                                                     <input type="text" class="form-control"
                                                         id="edit_transporttype">
                                                 </div>

                                                 <!-- Email Address -->
                                                 <div class="mb-3">
                                                     <label class="form-label">Entry
                                                         Point</label>
                                                     <input type="email" class="form-control" id="edit_entryPoint">
                                                 </div>
                                             </div>
                                             <div class="modal-footer">
                                                 <button class="btn btn-md btn-info">Update
                                                     Details</button>
                                             </div>
                                         </div>
                                     </div>
                                 </div>
                             </tfooter>
                         </table>
                     </div>
                 </div>
             </div>
         </div>
         <div class="col-xl-12">
             <div class="border border-bottom-0 rounded-1 mb-3 ">
                 <div class="card-body p-0">
                     <div class="table-responsive">
                         <table class="table text-nowrap">
                             <thead>
                                 <tr class="bg-light">
                                     <th scope="col">Consignment Details</th>
                                     <th scope="col"></th>
                                     <th scope="col"></th>
                                 </tr>
                             </thead>
                             <tbody>
                                 <tr>
                                     <td colspan="3">
                                         <div class="table-responsive">
                                             <table id="summaryTable3" class="table text-nowrap">
                                                 <thead class="table-success">
                                                     <tr>

                                                         <th scope="col">Item Name</th>
                                                         <th scope="col">Quantity</th>
                                                         <th scope="col" style="">
                                                             Purpose</th>
                                                         <th scope="col">Value</th>
                                                         <th scope="col">Attachment</th>

                                                     </tr>
                                                 </thead>
                                                 <tbody>
                                                     {{-- @forelse ($consignmentDetails as $index => $item)
                                                         
                                                         <tr>
                                                             <td>{{ $index + 1 }}</td>
                                                             <td>{{ $item['item_name'] ?? '—' }}
                                                             </td>
                                                             <td>{{ $item['quantity'] ?? '—' }}
                                                                 {{ $item['measure'] ?? '—' }}
                                                             </td>
                                                             <td>{{ $item['uses'] ?? '—' }}</td>
                                                             <td>RM {{ $item['value'] ?? '—' }}
                                                             </td>
                                                            
                                                             <td>
                                                                 <a type="button" data-bs-toggle="modal"
                                                                     data-bs-target="#editIpItemModal"
                                                                     class="btn btn-sm btn-info">Edit
                                                                     Consignment Details
                                                                 </a><br>
                                                                 <a type="button"
                                                                     class="btn btn-sm btn-danger mt-2">RemoveDetails</a>
                                                             </td>
                                                         </tr>
                                                     @empty
                                                         <tr>
                                                             <td colspan="7" class="text-center text-muted">
                                                                 No consignment items found.
                                                             </td>
                                                         </tr>
                                                     @endforelse --}}
                                                 </tbody>
                                             </table>
                                             <div class="modal fade" id="editIpItemModal" tabindex="-1">
                                                 <div class="modal-dialog modal-xl">
                                                     <div class="modal-content">
                                                         <div class="modal-header">
                                                             <h5 class="modal-title">Edit
                                                                 Consignment Details</h5>
                                                             <button type="button" class="btn-close"
                                                                 data-bs-dismiss="modal"></button>
                                                         </div>
                                                         <div class="modal-body">
                                                             <div class="row gy-4 mb-3">
                                                                 <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                                                     <label for="itemSelect" class="form-label">Item
                                                                     </label>
                                                                     <!-- <select class="form-select" id="itemSelect" name="itemSelect">
                                                                                                                                <option value="aa" >-- Select Item</option>
                                                                                                                                <option value="aasda" >aaadwd</option>
                                                                                                                            </select> -->
                                                                     <input type="text" class="form-control"
                                                                         value="Fresh Fruit - CORN" disabled>
                                                                     <small style="color:red">Item
                                                                         refering to the
                                                                         exporter's
                                                                         Country</small>
                                                                 </div>
                                                                 <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                                                     <label for="itemValue" class="form-label">Value
                                                                         (RM)</label>
                                                                     <input type="text" class="form-control"
                                                                         id="itemValue" name="itemValue"
                                                                         placeholder="RM ...">
                                                                 </div>
                                                                 <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                                                     <label for="itemQuantity"
                                                                         class="form-label">Quantity</label>
                                                                     <input type="text" class="form-control"
                                                                         id="itemQuantity" name="itemQuantity">
                                                                 </div>
                                                                 <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                                                     <label for="itemMeasure"
                                                                         class="form-label">Measurement
                                                                         Unit</label>
                                                                     <select class="form-select" id="itemMeasure"
                                                                         name="itemMeasure">
                                                                         <option value="">
                                                                             -- Select
                                                                             Measurement Unit --
                                                                         </option>

                                                                     </select>

                                                                 </div>
                                                                 <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                                                     <label for="itemPurpose"
                                                                         class="form-label">Purpose</label>
                                                                     <select class="form-select" id="itemPurpose"
                                                                         name="itemPurpose">
                                                                         <option value="">
                                                                             -- Select Purpose --
                                                                         </option>

                                                                     </select>
                                                                 </div>
                                                                 <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                                                     <label for="itemUses"
                                                                         class="form-label">Uses</label>
                                                                     <select class="form-select" id="itemUses"
                                                                         name="itemUses">

                                                                     </select>
                                                                 </div>
                                                                 <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12"
                                                                     style="display:none">
                                                                     <label for="itemUses"
                                                                         class="form-label">Attachments</label>
                                                                     <select class="form-select" id="itemUses"
                                                                         name="itemUses">

                                                                     </select>
                                                                 </div>
                                                             </div>
                                                         </div>
                                                         <div class="modal-footer">
                                                             <button class="btn btn-info btn-md">Update
                                                                 Details</button>
                                                         </div>
                                                     </div>
                                                 </div>
                                             </div>
                                         </div>
                                     </td>
                                 </tr>
                             </tbody>
                         </table>
                     </div>
                 </div>
             </div>
         </div>
     </div>
     <div class="row justify-content-center" style="display:none">
         <div class="col-auto d-flex gap-3">
             <button id="generateSummary" type="button" class="btn btn-md btn-warning">Generate Summary</button>
             <button id="submitApps" type="button" class="btn btn-md btn-info">Submit
                 Application</button>
         </div>
     </div>
 </div>
