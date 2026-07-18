@extends('pages.app')

@section('pageName', 'Account Verification Status')

@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Verification', 'url' => '#']]"></x-breadcrumb>
@endsection

@section('content')
<div class="row justify-content-center my-4">
    <div class="col-xl-7 col-lg-9 col-md-12">
        
        {{-- Main State Card --}}
        <div class="card custom-card verify-dashboard-card">
            
           

            <div class="card-body p-5 text-center">
                
                {{-- Dynamic Alert Icon Wrapper using CSS design variables --}}
                <div class="verification-icon-badge mb-4 animate-pulse">
                    <div class="icon-inner-bg">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256" fill="currentColor">
                            <path d="M128,24A104,104,0,1,0,232,128,104.11,104.11,0,0,0,128,24Zm0,192a88,88,0,1,1,88-88A88.1,88.1,0,0,1,128,216Zm-8-80V88a8,8,0,0,1,16,0v48a8,8,0,0,1,-16,0Zm12,36a12,12,0,1,1,12-12A12,12,0,0,1,132,172Z"/>
                        </svg>
                    </div>
                </div>

                <h3 class="fw-semibold mb-2" data-en="Verification Notice" data-bm="Notis Pengesahan">Verification Notice</h3>
                <p class="text-muted text-center max-w-md mx-auto mb-4"
                   data-en="Your account status is currently set to unverified. Access to formal dashboard functions requires an authorized record check."
                   data-bm="Status akaun anda pada masa ini ditetapkan sebagai belum disahkan. Akses kepada fungsi papan pemuka rasmi memerlukan semakan rekod yang dibenarkan.">
                    Your account status is currently set to unverified. Access to formal dashboard functions requires an authorized record check.
                </p>

                {{-- Status Alert Callout Block --}}
                <div class="verification-status-callout mb-5">
                    <div class="callout-text text-start">
                        @if(authUser()['user']->approved?->verification_attachment)
                            <strong class="d-block mb-1" data-en="Status: Documents Pending Action" data-bm="Status: Dokumen Dalam Tindakan">Status: Documents Pending Action</strong>
                            <p class="mb-0 text-muted" 
                               data-en="Your verification file has been uploaded successfully. A Department of Agriculture (DOA) officer will audit the record details shortly."
                               data-bm="Fail pengesahan anda telah berjaya dimuat naik. Pegawai Jabatan Pertanian (DOA) akan mengaudit butiran rekod tidak lama lagi.">
                                Your verification file has been uploaded successfully. A Department of Agriculture (DOA) officer will audit the record details shortly.
                            </p>
                        @else
                            <strong class="d-block mb-1 text-danger-custom" data-en="Status: Missing Attachment" data-bm="Status: Lampiran Hilang">Status: Missing Attachment</strong>
                            <p class="mb-0 text-muted" 
                               data-en="No active verification attachment found. To request full platform clear rights, please upload your official operating credentials."
                               data-bm="Tiada lampiran pengesahan aktif ditemui. Untuk memohon hak pelepasan platform penuh, sila muat naik dokumen operasi rasmi anda.">
                                No active verification attachment found. To request full platform clear rights, please upload your official operating credentials.
                            </p>
                        @endif
                    </div>
                </div>

                {{-- Action Group --}}
                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center align-items-center">
                    <a href="/" class="btn btn-outline-secondary-custom px-4 py-2" data-en="Return Home" data-bm="Laman Utama">
                        Return Home
                    </a>
                    
                    <a href="/profile" class="btn btn-primary-custom px-4 py-2"
                       data-en="{{ authUser()['user']->approved?->verification_attachment ? 'View Attachment File' : 'Upload Attachment' }}"
                       data-bm="{{ authUser()['user']->approved?->verification_attachment ? 'Lihat Fail Lampiran' : 'Muat Naik Lampiran' }}">
                        {{ authUser()['user']->approved?->verification_attachment ? 'View Attachment File' : 'Upload Attachment' }}
                    </a>
                </div>

            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
    {{-- Language switcher logic matches the hook logic from front page asset scripts --}}
@endpush