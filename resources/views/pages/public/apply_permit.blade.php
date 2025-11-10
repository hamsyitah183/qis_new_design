@extends('pages.app')

@section('pageName', 'Apply Import Permit')


@section('breadcrumb')
    <x-breadcrumb 
        :items="[
            ['label' => 'Home', 'url' => '#'],
          
        ]" 
        title="Dashboard"
    >
     
    </x-breadcrumb>
@endsection

@section('content')




                    <!-- terssttt  -->
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">
                                        PERMIT APPLICATION
                                    </div>
                                </div>
                                <div class="card-body p-0"> <!-- method="POST"  data-wizard="active" style="display: block;"-->
                                    <form id="wizardForm" class="wizard wizard-tab horizontal" >
                                        <aside class="wizard-nav dots">
                                            <div class="wizard-step active" data-step="0">
                                                <span class="dot"></span>
                                                <span>IMPORTER & EXPORTER</span>
                                            </div>
                                            <div class="wizard-step" data-step="1">
                                                <span class="dot"></span>
                                                <span>PERMIT DETAILS</span>
                                            </div>
                                            <div class="wizard-step" data-step="2">
                                                <span class="dot"></span>
                                                <span>PERMIT ITEMS</span>
                                            </div>
                                            <div class="wizard-step" data-step="3">
                                                <span class="dot"></span>
                                                <span>Payment</span>
                                            </div>
                                            <div class="wizard-step" data-step="4">
                                                <span class="dot"></span>
                                                <span>Confirmation</span>
                                            </div>
                                        </aside>
                                        <aside class="wizard-content container">
                                            <div class="wizard-step active" data-title="IMPORTER & EXPORTER" data-id="2e8WqSV3slGIpTbnjcJzmDwBQaHrfh0Z" data-step="0">
                                                <div class="row justify-content-center">
                                                    <div class="col-xl-6">
                                                        <div class="register-page">
                                                            <h6 class="mb-3">Importer :</h6>
                                                            <div class="row gy-3">
                                                                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                                                    <label for="selectimp" class="form-label">Select Importer</label>
                                                                    <select id="selectimp" class="form-select xintra-select2" name="selectimp" style="width:100%;">
                                                                        <option value="">-- Select Importer --</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-xl-12">
                                                                    <label for="impname" class="form-label">Name</label>
                                                                    <input type="text" class="form-control " id="impname" name="impname" >
                                                                </div>
                                                                <div class="col-xl-12">
                                                                    <label for="impfonno" class="form-label">Phone No</label>
                                                                    <input type="text" class="form-control " id="impfonno" name="impfonno">
                                                                </div>
                                                                <div class="col-xl-12">
                                                                    <label for="impaddress" class="form-label">Address</label>
                                                                    <input type="text" class="form-control mb-2" id="impaddress1" name="impaddress1">
                                                                    <input type="text" class="form-control " id="impaddress2" name="impaddress2"> 
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-6">
                                                        <div class="register-page">
                                                            <h6 class="mb-3">Exporter :</h6>
                                                            <div class="row gy-3">
                                                                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                                                    <label for="selectexp" class="form-label">Select Exporter</label>
                                                                    <select id="selectexp" class="form-select xintra-select2" name="selectexp" style="width:100%;">
                                                                        <option value="">-- Select Exporter --</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-xl-12" class="">
                                                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExporterModal" >
                                                                        <i class="bx bx-plus me-1"></i> Add Exporter
                                                                    </button>
                                                                    <a style="color:red"> *If exporter is not in the selection list above</a>
                                                                </div>
                                                                <div class="col-xl-12">
                                                                    <label for="expname" class="form-label">Name</label>
                                                                    <input type="text" class="form-control " id="expname" name="expname">
                                                                </div>
                                                                <div class="col-xl-12">
                                                                    <label for="expfonno" class="form-label">Phone No</label>
                                                                    <input type="text" class="form-control " id="expfonno" name="expfonno" >
                                                                </div>
                                                                <div class="col-xl-12">
                                                                    <label for="expaddress" class="form-label">Address</label>
                                                                    <input type="text" class="form-control mb-2" id="expaddress1" name="expaddress1" >
                                                                    <input type="text" class="form-control " id="expaddress2"  name="expaddress2">
                                                                </div>
                                                                <div class="col-lg-12">
                                                                    <label for="expcountry" class="form-label">Country</label>
                                                                    <input type="text" class="form-control" id="expcountry" name="expcountry">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Add Exporter Modal -->
                                                    <div class="modal fade" id="addExporterModal" tabindex="-1" aria-labelledby="addExporterModalLabel" aria-hidden="true">
                                                        <div class="modal-dialog modal-dialog-centered modal-md">
                                                            <div class="modal-content">
                                                                <!-- Header -->
                                                                <div class="modal-header bg-primary text-white">
                                                                    <h5 class="modal-title" id="addExporterModalLabel">
                                                                    <i class="bx bx-user-plus me-2"></i> Add Exporter
                                                                    </h5>
                                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>

                                                                    <!-- Body -->
                                                                <!-- <form id="addExporterForm"> -->
                                                                    <div class="modal-body">
                                                                        <div class="mb-3">
                                                                            <label for="addexpName" class="form-label">Name</label>
                                                                            <input type="text" id="addexpName" name="addexpName" class="form-control" >
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label for="addexpfonno" class="form-label">Phone No</label>
                                                                            <input type="text" id="addexpfonno" name="addexpfonno" class="form-control" >
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label for="addexpaddress" class="form-label">Address</label>
                                                                            <input type="text" id="addexpaddress1" name="addexpaddress1" class="form-control mb-2" >
                                                                            <input type="text" id="addexpaddress2" name="addexpaddress2" class="form-control" >
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label for="addexpcountry" class="form-label">Country</label>
                                                                            <select class="form-select" id="addexpcountry" name="addexpcountry">
                                                                                <option value="">-- Select Country --</option>
                                                                            </select>
                                                                        </div>                                                                
                                                                    </div>

                                                                    <!-- Footer -->
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                                                                        <i class="bx bx-x me-1"></i> Cancel
                                                                        </button>
                                                                        <button type="submit" form="addExporterForm" class="btn btn-primary">
                                                                        <i class="bx bx-save me-1"></i> Save Exporter
                                                                        </button>
                                                                    </div>
                                                                <!-- </form> -->
                                                            </div> <!-- end class:modal-content -->
                                                        </div>
                                                    </div> <!-- end modal -->
                                                </div>
                                            </div>
                                            <div class="wizard-step" data-title="PERMIT DETAIL" data-id="2e8WqSV3slGIpTbnjcJzmDwBQaHrfh0Z" data-step="1">
                                                <div class="row gy-4">
                                                    <div class="col-xl-3">
                                                        <div class="col">
                                                            aasdasdasd
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-3">
                                                        <div class="col">
                                                            
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-3">
                                                        <div class="col">
                                                            
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="wizard-step" data-title="PERMIT ITEM DETAILS" data-id="H53WJiv9blN17MYTztq4g8U6eSVkaZDx" data-step="2">
                                                <div class="row justify-content-center summary-view">
                                                    123456
                                                </div>
                                            </div>
                                            <div class="wizard-step" data-title="SUMMARY" data-id="dOM0iRAyJXsLTr9b3KZfQ2jNv4pgn6Gu" data-limit="3" data-step="3">
                                                <div class="row">
                                                    <div class="col-xl-12">
                                                        
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="wizard-step" data-title="APPLICATION STATUS" data-id="dOM0iRAyJXsLTr9b3KZfQ2jNv4pgn6Gu" data-limit="3" data-step="4">
                                                <div class="row">
                                                    <div class="col-xl-12">
                                                        
                                                    </div>
                                                </div>
                                            </div>
                                        </aside>
                                    <aside class="wizard-buttons">
                                        <button class="wizard-btn btn prev" disabled="true">Prev</button>
                                        <button class="wizard-btn btn next">Next</button>
                                        <button class="wizard-btn btn finish" style="display: none;">Submit</button>
                                    </aside>
                                </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    
@endsection

@push('scripts')
<script>
    (function () {
        // 🟢 First wizard
        let firstWizardConfig = {
            wz_class: ".wizard-tab",
            highlight: true,
            highlight_time: 1000,
            progress: true,
            validate: true
        };
        new Wizard1(firstWizardConfig).init();

        // 🟢 Second wizard (with progress bar)
        let secondWizardConfig = {
            wz_class: ".wizard-second-tab",   // ✅ fixed selector
            highlight: true,
            highlight_time: 1000,
            progress: true,
            validate: true
        };
        new Wizard1(secondWizardConfig).init();
    })();
</script>
@endpush

