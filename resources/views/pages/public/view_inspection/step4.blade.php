<div class="wizard-step" data-title="APPLICATION STATUS" data-id="{{ $application->id }}" data-limit="3" data-step="4">
    <div class="row justify-content-center">
        <div class="col-xl-10">

            @php
                $authUuid = authUser()['user']->uuid ?? null;
                $status = strtolower($application->status ?? '');
                $importerVerify = strtolower($application->importer_verify ?? '');
            @endphp

            {{-- Reusable status icon --}}
            @php
                $statusIcon = '
                    <span class="avatar avatar-xl avatar-rounded bg-warning-transparent svg-warning">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256">
                            <circle cx="128" cy="128" r="96" opacity="0.2"></circle>
                            <line x1="128" y1="80" x2="128" y2="136" stroke="currentColor" stroke-linecap="round" stroke-width="16"></line>
                            <circle cx="128" cy="172" r="12" fill="currentColor"></circle>
                            <circle cx="128" cy="128" r="96" fill="none" stroke="currentColor" stroke-width="16"></circle>
                        </svg>
                    </span>';
            @endphp

            <div class="text-center p-4">

                {{-- @dd($status) --}}

                {{-- Category 0 - Pending --}}
                {{-- @if ($application->category_application == 0 && str_contains($status, 'clerk review in-progress'))
                    {!! $statusIcon !!}
                    <h3 class="mt-2" data-bm="Menunggu" data-en="Pending">Pending</h3>
                    @if (authUser()['type'] == 'public')
                        <p data-bm="Permohonan permit ini sedang menunggu pengesahan oleh Kerani." data-en="This permit application is currently pending verification by Clerk.">This permit application is currently pending verification by Clerk.</p>
                    @else
                        <p data-bm="Menunggu kelulusan." data-en="Waiting for approval.">Waiting for approval.</p>
                        @if (authUser() && (authUser()['user']->hasRole('clerk') || authUser()['user']->hasRole('admin')) )
                            <div class="d-flex justify-content-center gap-3 mt-3">
                                <button id="acceptAppl" class="btn btn-sm btn-success" data-bm="Terima Permohonan" data-en="Accept Application">Accept Application</button>
                                <button id="rejectAdminAppl" class="btn btn-sm btn-danger" data-bm="Tolak Permohonan" data-en="Reject Application">Reject Application</button>
                            </div>
                        @endif


                    @endif
                @endif --}}
      
                @if ( str_contains($status, 'clerk review in-progress'))
                    {!! $statusIcon !!}
                    <h3 class="mt-2" data-bm="Menunggu" data-en="Pending">Pending</h3>
                    @if (authUser()['type'] == 'public')
                        <p data-bm="Permohonan permit ini sedang menunggu pengesahan oleh Kerani." data-en="This permit application is currently pending verification by Clerk.">This permit application is currently pending verification by Clerk.</p>
                    @else
                        <p data-bm="Menunggu kelulusan." data-en="Waiting for approval.">Waiting for approval.</p>
                        @if (authUser() && (authUser()['user']->hasRole('clerk') || authUser()['user']->hasRole('admin') || authUser()['user']->hasRole('superadmin')) )
                            <div class="d-flex justify-content-center gap-3 mt-3">
                                <button id="acceptAppl" class="btn btn-sm btn-success" data-bm="Terima Permohonan" data-en="Accept Application">Accept Application</button>
                                <button id="rejectAdminAppl" class="btn btn-sm btn-danger" data-bm="Tolak Permohonan" data-en="Reject Application">Reject Application</button>
                            </div>
                        @endif


                    @endif
                @endif


                {{-- Category 1 Cases --}}
                @if ($application->category_application == 1)

                    {{-- Wait for company approval --}}
                    {{-- @dd($importerVerify) --}}
                    @if (str_contains($importerVerify, 'awaiting approval'))
                        {!! $statusIcon !!}
                        <h3 class="mt-2" data-bm="Menunggu" data-en="Pending">Pending</h3>

                        @if ($application->user->uuid == $authUuid)
                            <p>Your permit application is currently pending verification by the respective importer.</p>
                        @else
                            <p>This permit application is currently pending verification by the respective importer.</p>

                            {{-- If logged in user is the importer, show verify/reject buttons --}}
                            @if ($application->importer->uuid == $authUuid)
                                <div class="d-flex justify-content-center gap-3 mt-3">
                                    <button id="verifyAppl" class="btn btn-sm btn-secondary" data-bm="Sahkan Permohonan" data-en="Verify Application">Verify Application</button>
                                    <button id="rejectAppl" class="btn btn-sm btn-danger" data-bm="Tolak Permohonan" data-en="Reject Application">Reject Application</button>
                                </div>
                            @endif
                        @endif
                    @endif


                    {{-- compnay reject --}}
                    @if (str_contains($status, 'not approved'))
                    {!! $statusIcon !!}
                    <h3 class="mt-2" data-bm="Ditolak" data-en="Rejected">Rejected</h3>
                    <p data-bm="Permohonan permit ini telah ditolak oleh individu/syarikat." data-en="This permit application has been rejected by the individual/company .">This permit application has been rejected by the individual/company .</p>
                @endif

                @endif

                @if (str_contains($status, 'rejected'))
                    {!! $statusIcon !!}
                    <h3 class="mt-2" data-bm="Ditolak" data-en="Rejected">Rejected</h3>
                    @if (authUser()['type'] == 'public')
                        <p data-bm="Permohonan permit ini telah ditolak." data-en="This permit application has been rejected.">This permit application has been rejected.</p>
                        
                    @else
                        <p data-bm="Ditolak" data-en="Rejected">Rejected</p>
                        @if (authUser() && (authUser()['user']->hasRole('admin') || authUser()['user']->hasRole('superadmin')))
                            {{-- <div class="d-flex justify-content-center gap-3 mt-3">
                                <button id="acceptAppl" class="btn btn-sm btn-success" data-bm="Terima Permohonan" data-en="Accept Application">Accept Application</button>
                                <button id="rejectAdminAppl" class="btn btn-sm btn-danger" data-bm="Tolak Permohonan" data-en="Reject Application">Reject Application</button>
                            </div> --}}
                        @endif


                    @endif
                @endif

               

            </div>

        </div>
    </div>
</div>
