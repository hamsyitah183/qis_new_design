<div class="card custom-card overflow-hidden">
    <div class="card-header">
        <div class="card-title">
            Recent Activity
        </div>
    </div>
    <div class="card-body p-0">
        <div class = "scroll-div" style= "max-height: 450px;">
            <ul class="list-unstyled mb-0 activity-timeline ">
                @forelse($recentActivities ?? [] as $activity)
                    <li class="p-3 border-bottom">
                        <div class="d-flex align-items-start gap-2">
                            @php
                                $colors = ['primary', 'secondary', 'info', 'warning', 'success'];
                                $color = $colors[$loop->index % count($colors)];
                            @endphp
                            <span class="avatar avatar-xs avatar-rounded bg-{{ $color }}-transparent">
                                <i class="ti ti-point-filled"></i>
                            </span>
                            <div class="flex-fill">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <p class="mb-0 fw-medium fs-13">
                                        {{ $activity->causer ? $activity->causer->fullname : 'System' }}
                                    </p>
                                    <span class="text-muted fs-11">
                                        {{ $activity->created_at->diffForHumans() }}
                                    </span>
                                </div>
                                <p class="text-muted mb-0 fs-12">
                                    {{ $activity->description ?? 'No description' }}
                                </p>
                                @if ($activity->subject_type)
                                    <span class="badge bg-light text-dark fs-10 mt-1">
                                        {{ class_basename($activity->subject_type) }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </li>
                @empty
                    <li class="p-4 text-center text-muted">
                        <i class="ti ti-history fs-24 mb-2 d-block"></i>
                        <p class="mb-0">No recent activity</p>
                    </li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
