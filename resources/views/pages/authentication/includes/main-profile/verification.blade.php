<div class="border rounded-3 p-3">
    @php
        $approved = $user['user']->approved ?? null;
        $imgLink = $approved->verification_attachment ?? null;
        $verifiedDOA = $approved->doa_verified ?? null;
    @endphp

    <div class="hasImage" style="display:none;">
        <div id="imgLink"></div>

        <div class="mt-3 status"></div>

        <div class="mt-3">
            <span class="fs-12">
                <span class="fw-bold">Submitted on :</span>
                <span class="submittedVerification text-muted">
                    {{ $approved->created_at ?? 'N/A' }}
                </span>
            </span>
            <br>
            <span class="fs-12">
                <span class="fw-bold">View By :</span>
                <span class="approvedBy text-muted">
                    {{ $approved->approver->name ?? 'N/A' }}
                </span>
                <span class="approvedDate text-muted">
                    {{ $approved->doa_approved_time ?? '' }}
                </span>
            </span>
        </div>
    </div>



    <div class="hasNoImage" style="display: none;">
        <form class="dropzone" id="verificationDropzone" enctype="multipart/form-data">

            <div class="dz-message p-5 text-center">
                <h5 class="display-3 text-muted">
                    <i class="ti ti-folder-down"></i>
                </h5>
                <div class="text-muted">
                    Drop your verification file here or click to upload.
                </div>
            </div>
        </form>

        <div class="text-end mt-3">
            <button id="uploadBtn" class="btn btn-primary" type="button">
                Upload File
            </button>
        </div>
    </div>

</div>
