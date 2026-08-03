<div class="card custom-card overflow-hidden adm-activity-card">
    <div class="card-header">
        <div class="card-title" data-en="Recent Activity" data-bm="Aktiviti Terkini">
            Recent Activity
        </div>
        <span class="adm-card-sub" data-en="Latest actions across the system" data-bm="Tindakan terkini dalam sistem">Latest actions across the system</span>
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
                            @php
                                $time_en = $activity->created_at->locale('en')->diffForHumans();
                                $time_bm = $activity->created_at->locale('ms')->diffForHumans();
                            @endphp
                            <span class="adm-activity-time" title="{{ $activity->created_at->format('d M Y, g:i A') }}" data-en="{{ $time_en }}" data-bm="{{ $time_bm }}">
                                {{ $time_en }}
                            </span>
                        </div>
                        @php
                            $desc_en = $activity->description ?? 'No description';
                            $translations = [
                                'logged in to the system' => 'log masuk ke sistem',
                                'logged out from the system' => 'log keluar dari sistem',
                                '(internal user)' => '(pengguna dalaman)',
                                '(internal user )' => '(pengguna dalaman)',
                                '(public user)' => '(pengguna awam)',
                                '(public user )' => '(pengguna awam)',
                                
                                'has created a new consignment application draft' => 'telah mencipta draf permohonan konsainan baharu',
                                'has updated a consignment application draft' => 'telah mengemas kini draf permohonan konsainan',
                                'has submitted a drafted consignment application' => 'telah menghantar draf permohonan konsainan',
                                'has created a new consignment application' => 'telah mencipta permohonan konsainan baharu',
                                
                                'has created a new draft inspection' => 'telah mencipta draf pemeriksaan baharu',
                                'has updated a draft inspection' => 'telah mengemas kini draf pemeriksaan',
                                'has created a new inspection application' => 'telah mencipta permohonan pemeriksaan baharu',
                                'has updated an inspection application' => 'telah mengemas kini permohonan pemeriksaan',
                                
                                'has deleted a consignment application' => 'telah memadam permohonan konsainan',
                                'has approved a consignment application' => 'telah meluluskan permohonan konsainan',
                                'has verified consignment application' => 'telah mengesahkan permohonan konsainan',
                                'has rejected consignment application' => 'telah menolak permohonan konsainan',
                                'has approved consignment application' => 'telah meluluskan permohonan konsainan',
                                
                                'has deleted an inspection application' => 'telah memadam permohonan pemeriksaan',
                                'deleted inspection application' => 'memadam permohonan pemeriksaan',
                                
                                'has added an importer' => 'telah menambah pengimport',
                                'has updated an importer' => 'telah mengemas kini pengimport',
                                
                                'has added an exporter' => 'telah menambah pengeksport',
                                'has deleted an exporter' => 'telah memadam pengeksport',
                                
                                'updated application' => 'mengemas kini permohonan',
                                'created application' => 'mencipta permohonan',
                                
                                'accepted inspection item' => 'menerima item pemeriksaan',
                                'rejected inspection item' => 'menolak item pemeriksaan',
                                
                                'has successfully completed payment for order' => 'telah berjaya melengkapkan pembayaran untuk pesanan',
                                'payment failed for order' => 'pembayaran gagal untuk pesanan',
                                
                                'is uploading an attachment to get verification' => 'sedang memuat naik lampiran untuk mendapatkan pengesahan',
                                'was verified by' => 'telah disahkan oleh',
                                '\'s verification is rejected by' => ' pengesahannya ditolak oleh',
                                'is new user for boundary officer' => 'ialah pengguna baharu untuk pegawai sempadan',
                            ];
                            $desc_bm = str_ireplace(array_keys($translations), array_values($translations), $desc_en);
                        @endphp
                        <p class="adm-activity-desc" data-en="{{ $desc_en }}" data-bm="{{ $desc_bm }}">
                            {{ $desc_en }}
                        </p>
                        @if ($activity->subject_type)
                            <span class="adm-activity-subject">
                                @php
                                    $subj_en = strtolower(class_basename($activity->subject_type));
                                    $subj_bm = $subj_en === 'internal' ? 'dalaman' : ($subj_en === 'public' ? 'awam' : $subj_en);
                                @endphp
                                <i class='bx bx-link-alt'></i> <span data-en="{{ $subj_en }}" data-bm="{{ $subj_bm }}">{{ $subj_en }}</span>
                            </span>
                        @endif
                    </div>
                </li>
            @empty
                <li class="adm-activity-empty">
                    <i class='bx bx-history'></i>
                    <p class="mb-0" data-en="No recent activity yet" data-bm="Tiada aktiviti terkini lagi">No recent activity yet</p>
                </li>
            @endforelse
        </ul>
    </div>
</div>