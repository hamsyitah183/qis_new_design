<div class="border rounded-3 p-3">
    @php
        // dd($user['user']->approved);
        $imgLink = $user['user']->approved['verification_attachment'];
        $verifiedDOA = $user['user']->approved['doa_verified'];

    @endphp

    @if ($imgLink)
        <img src="" class="img-fluid" alt="" id="imgLink">

        <div class="mt-1 status">
            
        </div>

        <div class="mt-3">
            <span class="fs-12 "><span class="fw-bold">Submitted on :</span> <span class="submittedVerification text-muted"></span></span> <br>
            <span class="fs-12 "><span class="fw-bold">Approved By :</span> <span class="approvedBy text-muted"></span> <span
                    class="approvedDate text-muted"></span></span>
        </div>
    @else
        <form class="dropzone" id="verificationDropzone" enctype="multipart/form-data">
            <div class="dz-message p-5 text-center">
                <h5 class="display-3 text-muted"><i class="ti ti-folder-down"></i></h5>
                <div class="text-muted">Drop your verification file here or click to upload.</div>
            </div>
        </form>

        <div class="text-end mt-3">
            <button id="uploadBtn" class="btn btn-primary" type="button">
                Upload File
            </button>
        </div>
    @endif

</div>
