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

                {{-- Category 0 - Pending --}}
                @if ($application->category_application == 0 && str_contains($status, 'pending'))
                    {!! $statusIcon !!}
                    <h3 class="mt-2">Pending</h3>
                    @if (authUser()['type'] == 'public')
                        <p>This permit application is currently pending verification by admin.</p>
                    @else
                        <p>Waiting for admin approval.</p>
                        @if (authUser() && authUser()['user']->hasRole('admin'))
                            <div class="d-flex justify-content-center gap-3 mt-3">
                                <button id="acceptAppl" class="btn btn-sm btn-success">Accept Application</button>
                                <button id="rejectAdminAppl" class="btn btn-sm btn-danger">Reject Application</button>
                            </div>
                        @endif


                    @endif
                @endif


                {{-- Category 1 Cases --}}
                @if ($application->category_application == 1)

                    {{-- Wait for company approval --}}
                    @if (str_contains($importerVerify, 'wait for company approval'))
                        {!! $statusIcon !!}
                        <h3 class="mt-2">Pending</h3>

                        @if ($application->user->uuid == $authUuid)
                            <p>Your permit application is currently pending verification by the respective importer.</p>
                        @else
                            <p>This permit application is currently pending verification by the respective importer.</p>

                            {{-- If logged in user is the importer, show verify/reject buttons --}}
                            @if ($application->importer->uuid == $authUuid)
                                <div class="d-flex justify-content-center gap-3 mt-3">
                                    <button id="verifyAppl" class="btn btn-sm btn-secondary">Verify Application</button>
                                    <button id="rejectAppl" class="btn btn-sm btn-danger">Reject Application</button>
                                </div>
                            @endif
                        @endif
                    @endif


                    {{-- Importer Verify = Pending --}}
                    {{-- @dd($importerVerify)
                    @if (str_contains($importerVerify, 'pending'))
                        {!! $statusIcon !!}
                        <h3 class="mt-2">Pending</h3>

                        @if (authUser()['type'] == 'public')
                            <p>This permit application is currently pending verification by admin.</p>
                        @else
                            <p>Waiting for admin approval.</p>
                        @endif
                    @endif --}}

                @endif

                 @if (str_contains($status, 'rejected'))
                    {!! $statusIcon !!}
                    <h3 class="mt-2">Rejected</h3>
                    @if (authUser()['type'] == 'public')
                        <p>This permit application has been rejected.</p>
                    @else
                        <p>Rejected</p>
                        @if (authUser() && authUser()['user']->hasRole('admin'))
                            {{-- <div class="d-flex justify-content-center gap-3 mt-3">
                                <button id="acceptAppl" class="btn btn-sm btn-success">Accept Application</button>
                                <button id="rejectAdminAppl" class="btn btn-sm btn-danger">Reject Application</button>
                            </div> --}}
                        @endif


                    @endif
                @endif

            </div>

        </div>
    </div>
</div>
