<ul class="list-group list-group-flush border rounded-3">

    <li class="list-group-item p-3">
        <span class="fw-medium fs-15 d-block mb-3">Contact Info :</span>
        <div class="text-muted">
            <p class="mb-3">
                <span class="avatar avatar-sm avatar-rounded text-primary p-1 bg-primary-transparent me-2">
                    <i class="ri-mail-line align-middle fs-15"></i>
                </span>
                <span class="fw-medium text-default">Email : </span>
                <span class="email"></span>
            </p>

            @if ($user['type'] === 'public')
                <p class="mb-3">
                    <span class="avatar avatar-sm avatar-rounded text-primary2 p-1 bg-primary2-transparent me-2">
                        <i class="ri-building-line align-middle fs-15"></i>
                    </span>
                    <span class="fw-medium text-default">Location : </span>
                    <span class="address"></span>
                </p>
            @endif
            <p class="mb-3">
                <span class="avatar avatar-sm avatar-rounded text-primary3 p-1 bg-primary3-transparent me-2">
                    <i class="ri-phone-line align-middle fs-15"></i>
                </span>
                <span class="fw-medium text-default">Phone : </span>
                <span class="phone_number"></span>
            </p>
            @if ($user['type'] === 'internal')
                <p class="mb-0 branch-info">
                    <span class="avatar avatar-sm avatar-rounded text-primary p-1 bg-primary-transparent me-2">
                        <i class="ri-map-2-line align-middle fs-15"></i>
                    </span>
                    <span class="fw-medium text-default">Branch : </span>
                    <span class="branch"></span>
                </p>
            @endif
        </div>
    </li>



</ul>
