@extends('pages.app')

@section('pageName', 'New Application')


@section('breadcrumb')
    <x-breadcrumb 
        :items="[
            ['label' => 'Home', 'url' => '#'],
          
        ]" 
        title="Application"
    >
     
    </x-breadcrumb>
@endsection

@section('content')
    <!-- Include Xintra’s core + stepper JS -->
    <link href="https://cdn.jsdelivr.net/npm/bs-stepper/dist/css/bs-stepper.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bs-stepper/dist/js/bs-stepper.min.js"></script>

    <div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Xintra Form Wizard</h5>
    </div>
    <div class="card-body">
        <div id="xintraWizard" class="bs-stepper linear">
        <!-- Step Headers -->
        <div class="bs-stepper-header" role="tablist">
            <div class="step" data-target="#step1">
            <button type="button" class="step-trigger" role="tab" id="stepper1trigger1">
                <span class="bs-stepper-circle">1</span>
                <span class="bs-stepper-label">Basic Info</span>
            </button>
            </div>
            <div class="line"></div>
            <div class="step" data-target="#step2">
            <button type="button" class="step-trigger" role="tab" id="stepper1trigger2">
                <span class="bs-stepper-circle">2</span>
                <span class="bs-stepper-label">Details</span>
            </button>
            </div>
            <div class="line"></div>
            <div class="step" data-target="#step3">
            <button type="button" class="step-trigger" role="tab" id="stepper1trigger3">
                <span class="bs-stepper-circle">3</span>
                <span class="bs-stepper-label">Review</span>
            </button>
            </div>
        </div>

        <!-- Step Content -->
        <div class="bs-stepper-content">
            <!-- Step 1 -->
            <div id="step1" class="content" role="tabpanel" aria-labelledby="stepper1trigger1">
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" class="form-control" id="fullname" placeholder="Enter full name">
            </div>
            <div class="text-end">
                <button class="btn btn-primary btn-next">Next <i class="bx bx-right-arrow-alt ms-1"></i></button>
            </div>
            </div>

            <!-- Step 2 -->
            <div id="step2" class="content" role="tabpanel" aria-labelledby="stepper1trigger2">
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" placeholder="example@email.com">
            </div>
            <div class="d-flex justify-content-between">
                <button class="btn btn-label-secondary btn-prev"><i class="bx bx-left-arrow-alt me-1"></i>Previous</button>
                <button class="btn btn-primary btn-next">Next <i class="bx bx-right-arrow-alt ms-1"></i></button>
            </div>
            </div>

            <!-- Step 3 -->
            <div id="step3" class="content" role="tabpanel" aria-labelledby="stepper1trigger3">
            <p class="fw-semibold text-muted mb-2">Review your details:</p>
            <ul>
                <li><strong>Name:</strong> <span id="reviewName"></span></li>
                <li><strong>Email:</strong> <span id="reviewEmail"></span></li>
            </ul>
            <div class="d-flex justify-content-between">
                <button class="btn btn-label-secondary btn-prev"><i class="bx bx-left-arrow-alt me-1"></i>Previous</button>
                <button class="btn btn-success" id="finishBtn">Finish</button>
            </div>
            </div>
        </div>
        </div>
    </div>
    </div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const wizardEl = document.querySelector('#xintraWizard');
        const stepper = new window.Stepper(wizardEl);

        // Navigation buttons
        const nextBtns = wizardEl.querySelectorAll('.btn-next');
        const prevBtns = wizardEl.querySelectorAll('.btn-prev');

        nextBtns.forEach(btn => {
            btn.addEventListener('click', function() {
            // Example: validate step before moving forward
            const currentStep = stepper._currentIndex;
            if (currentStep === 0 && !document.getElementById('fullname').value) {
                Swal.fire({ icon: 'warning', text: 'Please enter your name first!' });
                return;
            }
            if (currentStep === 1 && !document.getElementById('email').value) {
                Swal.fire({ icon: 'warning', text: 'Please enter your email!' });
                return;
            }

            // Fill review section when going to step 3
            if (currentStep === 1) {
                document.getElementById('reviewName').textContent = document.getElementById('fullname').value;
                document.getElementById('reviewEmail').textContent = document.getElementById('email').value;
            }

            stepper.next();
            });
        });

        prevBtns.forEach(btn => {
            btn.addEventListener('click', () => stepper.previous());
        });

        // Finish button
        document.getElementById('finishBtn').addEventListener('click', function() {
            Swal.fire({
            icon: 'success',
            title: 'Wizard Complete!',
            text: 'All steps finished successfully!',
            confirmButtonColor: '#7367F0'
            });
        });
    });

</script>

@endpush

