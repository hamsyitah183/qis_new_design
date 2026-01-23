<div class="wizard-step" data-title="APPLICATION STATUS" data-id="{{ $application->id }}" data-limit="3" data-step="3">
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
                    <h3 class="mt-2">Pending</h3>
                    @if (authUser()['type'] == 'public')
                        <p>This permit application is currently pending verification by Clerk.</p>
                    @else
                        <p>Waiting for approval.</p>
                        @if (authUser() && (authUser()['user']->hasRole('clerk') || authUser()['user']->hasRole('admin')) )
                            <div class="d-flex justify-content-center gap-3 mt-3">
                                <button id="acceptAppl" class="btn btn-sm btn-success">Accept Application</button>
                                <button id="rejectAdminAppl" class="btn btn-sm btn-danger">Reject Application</button>
                            </div>
                        @endif


                    @endif
                @endif --}}
                @if (str_contains($status, 'clerk review in-progress'))
                    {!! $statusIcon !!}
                    <h3 class="mt-2">Pending Clerk Review</h3>
                    @if (authUser()['type'] == 'public')
                        <p>Your inspection application is currently pending verification by the Clerk.</p>
                    @else
                        <p>Waiting for Clerk approval.</p>
                        @if (authUser() && (authUser()['user']->hasRole('clerk') || authUser()['user']->hasRole('admin')))
                            <div class="d-flex justify-content-center gap-3 mt-3">
                                <button id="acceptAppl" class="btn btn-sm btn-success">Accept Application</button>
                                <button id="rejectAdminAppl" class="btn btn-sm btn-danger">Reject Application</button>
                            </div>
                        @endif
                    @endif
                @endif


                {{-- Category 1 Cases --}}
                @if ($application->category_application == 1 && str_contains($importerVerify, 'wait for company approval'))
                    {!! $statusIcon !!}
                    <h3 class="mt-2">Pending Importer Verification</h3>

                    @if ($application->user->uuid == $authUuid)
                        <p>Your inspection application is currently pending verification by the respective importer.</p>
                    @else
                        <p>This inspection application is currently pending verification by the respective importer.</p>

                        {{-- If logged in user is the importer, show verify/reject buttons --}}
                        @if ($application->importer->uuid == $authUuid)
                            <div class="d-flex justify-content-center gap-3 mt-3">
                                <button id="verifyAppl" class="btn btn-sm btn-secondary">Verify Application</button>
                                <button id="rejectAppl" class="btn btn-sm btn-danger">Reject Application</button>
                            </div>
                        @endif
                    @endif
                @endif

                {{-- Clerk Verified --}}
                @if (str_contains($status, 'clerk verified'))
                    <span class="avatar avatar-xl avatar-rounded bg-info-transparent svg-info">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256">
                            <circle cx="128" cy="128" r="96" opacity="0.2"></circle>
                            <polyline points="172 104 113.3 162.7 84 133.3" fill="none" stroke="currentColor"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></polyline>
                            <circle cx="128" cy="128" r="96" fill="none" stroke="currentColor"
                                stroke-width="16"></circle>
                        </svg>
                    </span>
                    <h3 class="mt-2">Clerk Verified</h3>
                    <p>Your inspection application has been verified by the clerk. It is now pending final processing.</p>
                @endif

                {{-- Fully Processed --}}
                @if (str_contains($status, 'fully processed'))
                    <span class="avatar avatar-xl avatar-rounded bg-success-transparent svg-success">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256">
                            <circle cx="128" cy="128" r="96" opacity="0.2"></circle>
                            <polyline points="172 104 113.3 162.7 84 133.3" fill="none" stroke="currentColor"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></polyline>
                            <circle cx="128" cy="128" r="96" fill="none" stroke="currentColor"
                                stroke-width="16"></circle>
                        </svg>
                    </span>
                    <h3 class="mt-2">Fully Processed</h3>
                    <p>Your inspection application has been fully processed. You can now download your certificate.</p>
                @endif

                {{-- Rejected --}}
                @if (str_contains($status, 'rejected'))
                    <span class="avatar avatar-xl avatar-rounded bg-danger-transparent svg-danger">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32">
                            <path fill="currentColor"
                                d="M16 2C8.268 2 2 8.268 2 16s6.268 14 14 14s14-6.268 14-14S23.732 2 16 2m0 26C9.383 28 4 22.617 4 16S9.383 4 16 4s12 5.383 12 12s-5.383 12-12 12" />
                            <path fill="currentColor"
                                d="M10.707 10.707a1 1 0 0 0-1.414 1.414L14.586 16l-5.293 5.293a1 1 0 1 0 1.414 1.414L16 17.414l5.293 5.293a1 1 0 0 0 1.414-1.414L17.414 16l5.293-5.293a1 1 0 0 0-1.414-1.414L16 14.586z" />
                        </svg>
                    </span>
                    <h3 class="mt-2">Rejected</h3>
                    <p>Your inspection application has been rejected.</p>
                    @php
                        $latestLog = $application->latestLog;
                    @endphp
                    @if ($latestLog && $latestLog->remark && $latestLog->remark != "Inspection application {$application->status}")
                        <div class="alert alert-danger-light mt-3">
                            <strong>Reason:</strong> {{ $latestLog->remark }}
                        </div>
                    @endif
                @endif

               

            </div>

        </div>
    </div>
</div>
