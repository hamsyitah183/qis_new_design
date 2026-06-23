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
                <h3 class="mt-2">Pending</h3>
                @if (authUser()['type'] == 'public')
                <p>This permit application is currently pending verification by Clerk.</p>
                @else
                <p>Waiting for approval.</p>
                @if (authUser() && (authUser()['user']->hasRole('clerk') || authUser()['user']->hasRole('admin')))
                <div class="d-flex justify-content-center gap-3 mt-3">
                    <button id="acceptAppl" class="btn btn-sm btn-success">Accept Application</button>
                    <button id="rejectAdminAppl" class="btn btn-sm btn-danger">Reject Application</button>
                </div>
                @endif


                @endif
                @endif --}}
                {{-- @dd($status, $application->importer_verify) --}}
                @if (str_contains($status, 'clerk review in-progress'))
                    {!! $statusIcon !!}
                    <h3 class="mt-2">Pending</h3>
                    @if (authUser()['type'] == 'public')
                        <p>This permit application is currently pending verification by Clerk.</p>
                    @else
                        <p>Waiting for approval.</p>
                        @php
                            $user = authUser()['user'] ?? null;
                            $userBranch = $user->branch ?? null;
                            $userRole = $user->roles->first()->name ?? null;

                            // Check if user is from Sipitang branch
                            $isSipitang = $userBranch === 'Sipitang';

                            // Check if user has required roles (clerk, admin, or superadmin)
                            $hasRequiredRole = in_array($userRole, ['clerk', 'admin', 'superadmin']);

                            // Superadmin can access regardless of branch
                            $isSuperAdmin = $userRole === 'superadmin';

                            // Show buttons if:
                            // 1. User is superadmin (any branch), OR
                            // 2. User has required role AND is from Sipitang branch
                            $showButtons = $isSuperAdmin || ($hasRequiredRole && $isSipitang);
                        @endphp

                        @if ($showButtons)
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
                            <p>Your permit application is currently pending verification by the respective exporter.</p>
                        @else
                            <p>This permit application is currently pending verification by the respective exporter.</p>

                            {{-- If logged in user is the importer, show verify/reject buttons --}}
                            @if ($application->exporter_id == $authUuid)
                                <div class="d-flex justify-content-center gap-3 mt-3">
                                    <button id="verifyAppl" class="btn btn-sm btn-secondary">Verify Application</button>
                                    <button id="rejectAppl" class="btn btn-sm btn-danger">Reject Application</button>
                                </div>
                            @endif
                        @endif
                    @endif


                    {{-- compnay reject --}}
                    @if (str_contains($status, 'not approved'))
                        {!! $statusIcon !!}
                        <h3 class="mt-2">Rejected</h3>
                        <p>This permit application has been rejected by the individual/company .</p>
                    @endif

                @endif

                @if (str_contains($status, 'rejected'))
                    {!! $statusIcon !!}
                    <h3 class="mt-2">Rejected</h3>
                    @if (authUser()['type'] == 'public')
                        <p>This permit application has been rejected.</p>
                    @else
                        <p>Rejected</p>
                        @if (authUser() && (authUser()['user']->hasRole('admin') || authUser()['user']->hasRole('superadmin')))
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
