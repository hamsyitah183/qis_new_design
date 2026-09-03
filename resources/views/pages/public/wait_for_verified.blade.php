@extends('pages.app')

@section('pageName', 'Account Verification Status')

@section('content')
    <div class="row justify-content-center my-4">
        <div class="col-xl-8 col-lg-10 col-md-12">

            {{-- Main State Card --}}
            <div class="card custom-card verify-dashboard-card">
                <div class="card-body p-5 text-center">

                    {{-- Dynamic Alert Icon --}}
                    <div class="verification-icon-badge mb-4 animate-pulse">
                        <div class="icon-inner-bg">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256" fill="currentColor">
                                <path
                                    d="M128,24A104,104,0,1,0,232,128,104.11,104.11,0,0,0,128,24Zm0,192a88,88,0,1,1,88-88A88.1,88.1,0,0,1,128,216Zm-8-80V88a8,8,0,0,1,16,0v48a8,8,0,0,1,-16,0Zm12,36a12,12,0,1,1,12-12A12,12,0,0,1,132,172Z" />
                            </svg>
                        </div>
                    </div>

                    <h3 class="fw-semibold mb-2" data-en="Verification Notice" data-bm="Notis Pengesahan">
                        Verification Notice
                    </h3>
                    <p class="text-muted text-center max-w-md mx-auto mb-4"
                        data-en="Your account status is currently set to unverified. Access to formal dashboard functions requires an authorized record check."
                        data-bm="Status akaun anda pada masa ini ditetapkan sebagai belum disahkan. Akses kepada fungsi permohonan memerlukan semakan rekod yang dibenarkan.">
                        Your account status is currently set to unverified. Access to application functions requires an
                        authorized record check.
                    </p>

                    {{-- Document Requirements Table --}}
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-sm text-start">
                            <thead class="table-light table-bordered">
                                <tr>
                                    <th data-en="Document" data-bm="Dokumen">Document</th>
                                    <th data-en="Status" data-bm="Status">Status</th>
                                    <th data-en="Action" data-bm="Tindakan">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($docStatus as $item)
                                    @php
                                        $req = $item['requirement'];
                                        $status = $item['status'];
                                        $attachment = $item['attachment'];
                                        $isMissing = $status === 'missing';
                                        $isPending = $status === 'pending';
                                        $isExpired = $status === 'expired';
                                        $isValid = $status === 'uploaded';
                                        $isRejected = $attachment && $attachment->rejected_reason;
                                    @endphp
                                    <tr>
                                        {{-- Document column: name + rejection reason below --}}
                                        <td>
                                            <div class="d-flex align-items-center gap-1 flex-wrap">
                                                <strong>{{ $req->name }}</strong>
                                                @if ($req->description)
                                                    <button type="button"
                                                            class="badge rounded-pill bg-light-primary text-primary border-0 doc-details-btn d-flex align-items-center gap-1"
                                                            data-doc-id="{{ $req->id }}"
                                                            data-description="{{ $req->description }}">
                                                        <i class="ti ti-info-circle fs-14"></i>
                                                        <span data-en="Details" data-bm="Butiran">Details</span>
                                                    </button>
                                                @endif
                                            </div>
                                            @if ($isRejected && $attachment->rejected_reason)
                                                <div class="small text-danger mt-1">
                                                    <em data-en="Reason:" data-bm="Sebab:">Reason:</em>
                                                    {{ $attachment->rejected_reason }}
                                                </div>
                                            @endif
                                        </td>

                                        {{-- Status column: only the badge (no reason) --}}
                                        <td>
                                            @if ($isRejected)
                                                <span class="badge bg-danger" data-en="Rejected" data-bm="Ditolak">
                                                    <i class="ri-close-circle-line me-1"></i> Rejected
                                                </span>
                                            @elseif ($isMissing)
                                                <span class="badge bg-danger" data-en="Missing" data-bm="Tiada">
                                                    <i class="ri-close-circle-line me-1"></i> Missing
                                                </span>
                                            @elseif ($isPending)
                                                <span class="badge bg-warning text-dark" data-en="Pending Review"
                                                    data-bm="Dalam Semakan">
                                                    <i class="ri-time-line me-1"></i> Pending Review
                                                </span>
                                            @elseif ($isExpired)
                                                <span class="badge bg-warning text-dark" data-en="Expired"
                                                    data-bm="Tamat tempoh">
                                                    <i class="ri-alert-line me-1"></i> Expired
                                                </span>
                                            @else
                                                <span class="badge bg-success" data-en="Verified" data-bm="Disahkan">
                                                    <i class="ri-checkbox-circle-line me-1"></i> Verified
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Action column --}}
                                        <td>
                                            @if ($isRejected || $isMissing || $isExpired)
                                                <a href="{{ route('profile') }}#edit-verification-tab"
                                                   class="btn btn-sm btn-primary"
                                                   data-en="Upload" data-bm="Muat Naik ">
                                                    <i class="ri-upload-line me-1"></i> Upload
                                                </a>
                                            @elseif ($attachment && ($isValid || $isPending))
                                                <a href="{{ asset($attachment->file_path) }}" target="_blank"
                                                    class="btn btn-sm btn-outline-secondary" data-en="View" data-bm="Lihat">
                                                    <i class="ri-eye-line me-1"></i> View
                                                </a>
                                            @else
                                                <span class="text-muted" data-en="No action" data-bm="Tiada tindakan">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-muted text-center"
                                            data-en="No document requirements found."
                                            data-bm="Tiada keperluan dokumen ditemui.">
                                            No document requirements found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Overall status summary --}}
                    @php
                        $allValid = collect($docStatus)->every(function($item) {
                            return $item['status'] === 'uploaded' && !($item['attachment'] && $item['attachment']->rejected_reason);
                        });
                        $anyMissing = collect($docStatus)->contains(fn($item) => $item['status'] === 'missing');
                        $anyExpired = collect($docStatus)->contains(fn($item) => $item['status'] === 'expired');
                        $anyPending = collect($docStatus)->contains(fn($item) => $item['status'] === 'pending');
                        $anyRejected = collect($docStatus)->contains(fn($item) => $item['attachment'] && $item['attachment']->rejected_reason);
                    @endphp

                    @if ($anyMissing || $anyExpired || $anyRejected)
                        <div class="alert alert-warning d-flex align-items-start text-start" role="alert">
                            <i class="ri-alert-fill me-3 fs-4"></i>
                            <div>
                                @if ($anyMissing)
                                    <strong data-en="Some required documents are missing."
                                        data-bm="Beberapa dokumen wajib belum dimuat naik.">
                                        Some required documents are missing.
                                    </strong>
                                    <br>
                                @endif
                                @if ($anyExpired)
                                    <strong data-en="Some uploaded documents have expired."
                                        data-bm="Beberapa dokumen yang dimuat naik telah tamat tempoh.">
                                        Some uploaded documents have expired.
                                    </strong>
                                    <br>
                                @endif
                                @if ($anyRejected)
                                    <strong data-en="Some documents were rejected and need to be re-uploaded."
                                        data-bm="Beberapa dokumen telah ditolak dan perlu dimuat naik semula.">
                                        Some documents were rejected and need to be re-uploaded.
                                    </strong>
                                    <br>
                                @endif
                                <span
                                    data-en="Please upload or update the documents marked above to complete your verification."
                                    data-bm="Sila muat naik atau kemas kini dokumen yang ditanda di atas untuk melengkapkan pengesahan anda.">
                                    Please upload or update the documents marked above to complete your verification.
                                </span>
                            </div>
                        </div>
                    @elseif ($anyPending)
                        <div class="alert alert-info d-flex align-items-start text-start" role="alert">
                            <i class="ri-information-fill me-3 fs-4"></i>
                            <div>
                                <strong data-en="Documents under review" data-bm="Dokumen dalam semakan">
                                    Documents under review
                                </strong>
                                <br>
                                <span
                                    data-en="Your uploaded documents are being reviewed by a DOA officer. You will be notified once verified."
                                    data-bm="Dokumen yang dimuat naik sedang disemak oleh pegawai DOA. Anda akan dimaklumkan setelah disahkan.">
                                    Your uploaded documents are being reviewed by a DOA officer. You will be notified once
                                    verified.
                                </span>
                            </div>
                        </div>
                    @elseif ($allValid && count($docStatus) > 0)
                        <div class="alert alert-success d-flex align-items-start text-start" role="alert">
                            <i class="ri-checkbox-circle-fill me-3 fs-4"></i>
                            <div>
                                <strong data-en="All required documents are uploaded and valid."
                                    data-bm="Semua dokumen wajib telah dimuat naik dan sah.">
                                    All required documents are uploaded and valid.
                                </strong>
                                <br>
                                <span data-en="Your account is pending final verification by a DOA officer."
                                    data-bm="Akaun anda menunggu pengesahan akhir oleh pegawai DOA.">
                                    Your account is pending final verification by a DOA officer.
                                </span>
                            </div>
                        </div>
                    @endif

                    {{-- Action Group --}}
                    <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center align-items-center mt-4">
                        <a href="{{ route('home') }}" class="btn btn-outline-secondary-custom px-4 py-2"
                            data-en="Return Home" data-bm="Laman Utama">
                            Return Home
                        </a>

                        @if ($anyMissing || $anyExpired || $anyRejected)
                            <a href="/profile#edit-verification-tab" class="btn btn-primary-custom px-4 py-2"
                                data-en="Upload Documents" data-bm="Muat Naik Dokumen">
                                Upload Documents
                            </a>
                        @else
                            <a href="/profile#edit-verification-tab" class="btn btn-primary-custom px-4 py-2" data-en="View Profile"
                                data-bm="Lihat Profil">
                                View Profile
                            </a>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    (function() {
        'use strict';

        // ─── Ensure the description modal exists ──────────────
        function ensureDescriptionModal() {
            if (document.getElementById('docDescriptionModal')) return;

            const html = `
                <div class="modal fade" id="docDescriptionModal" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="docDescriptionModalLabel">Document Details</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body doc-description-modal-body"></div>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', html);
        }

        // ─── Show document description modal ──────────────────
        function showDocumentDescription(docName, description) {
            ensureDescriptionModal();

            const modalEl = document.getElementById('docDescriptionModal');
            const title = document.getElementById('docDescriptionModalLabel');
            const body = modalEl.querySelector('.doc-description-modal-body');

            if (title) title.textContent = docName || 'Document Details';
            if (body) {
                body.innerHTML = description
                    ? `<div class="py-2">${description}</div>`
                    : '<p class="text-muted mb-0">No description available.</p>';
            }

            const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            modal.show();
        }

        // ─── Bind click events to all "Details" buttons ───────
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.doc-details-btn');
            if (!btn) return;

            e.preventDefault();
            e.stopPropagation();

            const row = btn.closest('td');
            const nameEl = row ? row.querySelector('strong') : null;
            const docName = nameEl ? nameEl.textContent.trim() : 'Document';

            const description = btn.getAttribute('data-description') || '';
            showDocumentDescription(docName, description);
        });

    })();
</script>
@endpush