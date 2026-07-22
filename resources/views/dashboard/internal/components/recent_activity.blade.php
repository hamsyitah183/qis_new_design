<div class="card custom-card overflow-hidden adm-activity-card">
    <div class="card-header">
        <div class="card-title">
            Recent Activity
        </div>
        <span class="adm-card-sub">Latest actions across the system</span>
    </div>
    <div class="card-body p-0 scroll-div" style="max-height: 450px;">
        <ul class="adm-activity-list">
            @forelse($recentActivities ?? [] as $activity)
                @php
                    // maps to our own colour tokens instead of the theme's bg-*-transparent
                    // so the timeline reads in the same palette as the rest of the dashboard
                    $colors = ['adm-act-primary', 'adm-act-secondary', 'adm-act-info', 'adm-act-warning', 'adm-act-success'];
                    $color = $colors[$loop->index % count($colors)];

                    $icons = ['bx-file', 'bx-user', 'bx-package', 'bx-error', 'bx-check-circle'];
                    $icon = $icons[$loop->index % count($icons)];
                @endphp
                <li class="adm-activity-item">
                    <div class="adm-activity-icon-col">
                        <span class="adm-activity-dot {{ $color }}">
                            <i class='bx {{ $icon }}'></i>
                        </span>
                    </div>
                    <div class="adm-activity-body">
                        <div class="adm-activity-top">
                            <p class="adm-activity-actor">
                                {{ $activity->causer ? $activity->causer->fullname : 'System' }}
                            </p>
                            <span class="adm-activity-time" title="{{ $activity->created_at->format('d M Y, g:i A') }}">
                                {{ $activity->created_at->diffForHumans() }}
                            </span>
                        </div>
                        <p class="adm-activity-desc">
                            {{ $activity->description ?? 'No description' }}
                        </p>
                        @if ($activity->subject_type)
                            <span class="adm-activity-subject">
                                <i class='bx bx-link-alt'></i> {{ class_basename($activity->subject_type) }}
                            </span>
                        @endif
                    </div>
                </li>
            @empty
                <li class="adm-activity-empty">
                    <i class='bx bx-history'></i>
                    <p class="mb-0">No recent activity yet</p>
                </li>
            @endforelse
        </ul>
    </div>
</div>