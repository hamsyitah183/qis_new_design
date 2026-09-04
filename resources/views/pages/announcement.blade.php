@extends('pages.front')

@section('content')
<div class="qis-body">

    {{-- Replace with a real paginated query, e.g.
         Announcement::orderByDesc('released_at')->paginate(12) --}}
    @php
        $announcements = $announcements ?? collect([
            [
                'title_en' => 'System maintenance on 25 July',
                'title_bm' => 'Penyelenggaraan sistem pada 25 Julai',
                'body_en' => 'QIS will be unavailable from 2:00 AM to 4:00 AM on 25 July while we perform scheduled server maintenance. Please submit urgent applications before this window.',
                'body_bm' => 'QIS tidak akan dapat diakses dari 2:00 pagi hingga 4:00 pagi pada 25 Julai semasa penyelenggaraan pelayan berjadual. Sila hantar permohonan segera sebelum tempoh ini.',
                'released_at' => '2026-07-15',
                'released_by' => 'QIS Admin',
                'expires_at' => '2026-07-26',
            ],
            [
                'title_en' => 'Phytosanitary certificates now sync with myPhyto',
                'title_bm' => 'Sijil Fitosanitasi kini disegerakkan dengan myPhyto',
                'body_en' => 'Phytosanitary certificates issued through QIS are now automatically synced with the national myPhyto system, removing the need for manual cross-registration.',
                'body_bm' => 'Sijil Fitosanitasi yang dikeluarkan melalui QIS kini disegerakkan secara automatik dengan sistem myPhyto kebangsaan, menghapuskan keperluan pendaftaran silang secara manual.',
                'released_at' => '2026-07-10',
                'released_by' => 'Plant Biosecurity Division',
                'expires_at' => null,
            ],
            [
                'title_en' => 'SabahPay now supported for all permit fees',
                'title_bm' => 'SabahPay kini disokong untuk semua bayaran permit',
                'body_en' => 'You can now settle Import Permit, Inspection Certificate and Consignment Certificate fees directly through SabahPay, in addition to existing payment methods.',
                'body_bm' => 'Anda kini boleh menyelesaikan bayaran Permit Import, Sijil Pemeriksaan dan Sijil Consignment terus melalui SabahPay, tambahan kepada kaedah pembayaran sedia ada.',
                'released_at' => '2026-07-02',
                'released_by' => 'QIS Admin',
                'expires_at' => null,
            ],
            [
                'title_en' => 'Update your company profile before 31 August',
                'title_bm' => 'Kemas kini profil syarikat anda sebelum 31 Ogos',
                'body_en' => 'Registered companies are required to review and confirm their company details, including Person In Charge contact information, before 31 August to avoid processing delays.',
                'body_bm' => 'Syarikat berdaftar dikehendaki menyemak dan mengesahkan butiran syarikat, termasuk maklumat hubungan Orang Untuk Dihubungi, sebelum 31 Ogos bagi mengelakkan kelewatan pemprosesan.',
                'released_at' => '2026-06-28',
                'released_by' => 'Plant Biosecurity Division',
                'expires_at' => '2026-08-31',
            ],
            [
                'title_en' => 'Public holiday closure notice',
                'title_bm' => 'Notis Penutupan Cuti Umum',
                'body_en' => 'QIS counters and support lines will be closed during the upcoming public holiday. Online applications remain accessible, but processing will resume the next working day.',
                'body_bm' => 'Kaunter dan talian sokongan QIS akan ditutup sepanjang cuti umum yang akan datang. Permohonan atas talian kekal boleh diakses, namun pemprosesan akan disambung pada hari bekerja berikutnya.',
                'released_at' => '2026-06-20',
                'released_by' => 'QIS Admin',
                'expires_at' => '2026-06-25',
            ],
            [
                'title_en' => 'New myGAP verification step added',
                'title_bm' => 'Langkah Pengesahan myGAP Baharu Ditambah',
                'body_en' => 'Applications that include a myGAP certificate now go through an additional automated validation step against the national myGAP registry to reduce processing errors.',
                'body_bm' => 'Permohonan yang menyertakan sijil myGAP kini melalui langkah pengesahan automatik tambahan terhadap daftar myGAP kebangsaan bagi mengurangkan ralat pemprosesan.',
                'released_at' => '2026-06-05',
                'released_by' => 'Plant Biosecurity Division',
                'expires_at' => null,
            ],
        ]);
    @endphp

    <header class="qis-nav">
        <div class="qis-nav-inner">
            <a href="/" class="qis-brand">
                <img src="{{ asset('images/Logo-DOA.png') }}" alt="Logo">
                <span class="qis-brand-text">
                    <b>QIS</b>
                    <small data-en="Plant Quarantine Info &amp; Services" data-bm="Maklumat &amp; Perkhidmatan Kuarantin">Plant Quarantine Info &amp; Services</small>
                </span>
            </a>

            <ul class="qis-nav-links">
                <li><a href="/#qis-about" data-en="About" data-bm="Tentang">About</a></li>
                <li><a href="/#qis-services" data-en="Services" data-bm="Perkhidmatan">Services</a></li>
                <li><a href="/announcements" data-en="Announcements" data-bm="Pengumuman">Announcements</a></li>
                <li><a href="/#qis-gallery" data-en="Gallery" data-bm="Galeri">Gallery</a></li>
                <li><a href="/#qis-contact" data-en="Contact" data-bm="Hubungi">Contact</a></li>
            </ul>

            <div class="qis-nav-actions">
                <div class="qis-lang-toggle">
                    <button type="button" class="qis-lang-btn active" data-lang="en">EN</button>
                    <button type="button" class="qis-lang-btn" data-lang="bm">BM</button>
                </div>
                <a href="/login" class="qis-btn-ghost d-none d-md-inline-flex" data-en="Sign In" data-bm="Log Masuk">Sign In</a>
                <a href="/login" class="qis-btn-primary" data-en="Apply Now" data-bm="Mohon Sekarang">Apply Now</a>
            </div>
        </div>
    </header>

    <section class="qis-section-tight">
        <div class="qis-container">
            <a href="/" class="qis-card-link mb-3 d-inline-flex" style="gap:6px">
                <i class='bx bx-left-arrow-alt'></i>
                <span data-en="Back to Home" data-bm="Kembali ke Laman Utama">Back to Home</span>
            </a>

            <span class="qis-eyebrow" data-en="Announcements" data-bm="Pengumuman">Announcements</span>
            <h2 class="qis-h2 mt-2" data-en="All Announcements" data-bm="Semua Pengumuman">All Announcements</h2>
            <p class="qis-lead mt-2" data-en="Every notice, update and reminder published by QIS, most recent first."
                data-bm="Setiap notis, kemas kini dan peringatan yang diterbitkan oleh QIS, terkini dahulu.">
                Every notice, update and reminder published by QIS, most recent first.
            </p>

            <div class="qis-announcement-grid" style="grid-template-columns:repeat(3,1fr)">
                @foreach ($announcements->sortByDesc('released_at') as $item)
                    @php
                        $releasedAt = \Carbon\Carbon::parse($item['released_at']);
                        $expiresAt = $item['expires_at'] ? \Carbon\Carbon::parse($item['expires_at']) : null;

                        if ($expiresAt && $expiresAt->isPast()) {
                            $statusClass = 'is-expired';
                            $statusEn = 'Expired';
                            $statusBm = 'Tamat Tempoh';
                        } elseif ($expiresAt && now()->diffInDays($expiresAt, false) <= 7) {
                            $statusClass = 'is-expiring';
                            $statusEn = 'Expiring Soon';
                            $statusBm = 'Akan Tamat';
                        } elseif ($releasedAt->diffInDays(now()) <= 5) {
                            $statusClass = 'is-new';
                            $statusEn = 'New';
                            $statusBm = 'Baharu';
                        } else {
                            $statusClass = 'is-active';
                            $statusEn = 'Active';
                            $statusBm = 'Aktif';
                        }
                    @endphp

                    <button type="button" class="qis-announcement-card" data-modal="qisModalAnnouncement">
                        <span class="qis-announcement-status {{ $statusClass }}" data-en="{{ $statusEn }}"
                            data-bm="{{ $statusBm }}">{{ $statusEn }}</span>

                        <h5 data-en="{{ $item['title_en'] }}" data-bm="{{ $item['title_bm'] }}">{{ $item['title_en'] }}</h5>

                        <p data-en="{{ \Illuminate\Support\Str::limit($item['body_en'], 100) }}"
                            data-bm="{{ \Illuminate\Support\Str::limit($item['body_bm'], 100) }}">
                            {{ \Illuminate\Support\Str::limit($item['body_en'], 100) }}
                        </p>

                        <div class="qis-announcement-meta">
                            <i class='bx bx-calendar'></i>
                            <span>{{ $releasedAt->format('d M Y') }}</span>
                        </div>

                        <span class="qis-card-link" data-en="Read more" data-bm="Baca lagi">
                            Read more <i class='bx bx-right-arrow-alt'></i>
                        </span>

                        <span class="d-none js-announcement-payload"
                            data-title-en="{{ $item['title_en'] }}"
                            data-title-bm="{{ $item['title_bm'] }}"
                            data-body-en="{{ $item['body_en'] }}"
                            data-body-bm="{{ $item['body_bm'] }}"
                            data-released-at="{{ $releasedAt->format('d M Y') }}"
                            data-released-by="{{ $item['released_by'] }}"
                            data-expires-at="{{ $expiresAt ? $expiresAt->format('d M Y') : '' }}"></span>
                    </button>
                @endforeach
            </div>

            {{-- If you switch $announcements to paginate(), drop the links in here: --}}
            {{-- <div class="mt-4">{{ $announcements->links() }}</div> --}}
        </div>
    </section>

    <footer class="qis-footer">
        <div class="qis-container">
            <div class="qis-footer-bottom">
                <span>&copy; {{ date('Y') }} Jabatan Pertanian Sabah. <span data-en="All rights reserved." data-bm="Hak cipta terpelihara.">All rights reserved.</span></span>
                <span data-en="Built for Smart Government Sabah" data-bm="Dibina untuk Smart Government Sabah">Built for Smart Government Sabah</span>
            </div>
        </div>
    </footer>

    {{-- Shared announcement modal, identical to the one on the landing page --}}
    <div class="qis-modal-overlay" id="qisModalAnnouncement">
        <div class="qis-modal">
            <button type="button" class="qis-modal-close" data-close-modal>&times;</button>
            <span class="qis-modal-tag" data-en="ANNOUNCEMENT" data-bm="PENGUMUMAN">ANNOUNCEMENT</span>
            <div class="qis-icon-wrap"><i class='bx bx-bell'></i></div>
            <h4 class="js-am-title" data-en="" data-bm=""></h4>

            <div class="qis-modal-meta">
                <div class="qis-modal-meta-row">
                    <i class='bx bx-calendar-check'></i>
                    <span><b data-en="Released" data-bm="Dikeluarkan">Released</b>: <span class="js-am-released-at"></span></span>
                </div>
                <div class="qis-modal-meta-row">
                    <i class='bx bx-user-circle'></i>
                    <span><b data-en="By" data-bm="Oleh">By</b>: <span class="js-am-released-by"></span></span>
                </div>
                <div class="qis-modal-meta-row">
                    <i class='bx bx-time-five'></i>
                    <span><b data-en="Valid Until" data-bm="Sah Sehingga">Valid Until</b>: <span class="js-am-expiry" data-en="No expiry" data-bm="Tiada tamat tempoh">No expiry</span></span>
                </div>
            </div>

            <p class="js-am-body" data-en="" data-bm=""></p>
        </div>
    </div>

</div>

<script>
    (function () {
        var STORAGE_KEY = 'qis_lang';
        var currentLang = 'en';
        try {
            currentLang = localStorage.getItem(STORAGE_KEY) || 'en';
        } catch(e) {}

        var langButtons = document.querySelectorAll('.qis-lang-btn');

        function applyLang(lang) {
            currentLang = lang;
            try {
                localStorage.setItem(STORAGE_KEY, lang);
            } catch(e) {}
            langButtons.forEach(function (btn) {
                btn.classList.toggle('active', btn.dataset.lang === lang);
            });
            document.querySelectorAll('[data-en]').forEach(function (el) {
                var val = el.dataset[lang];
                if (val === undefined || val === '') return;
                if (el.hasAttribute('data-i18n-attr')) {
                    el.setAttribute(el.getAttribute('data-i18n-attr'), val);
                } else {
                    el.innerHTML = val;
                }
            });
        }

        langButtons.forEach(function (btn) {
            btn.addEventListener('click', function () { applyLang(btn.dataset.lang); });
        });

        applyLang(currentLang);

        function setBilingual(el, en, bm) {
            if (!el) return;
            el.setAttribute('data-en', en || '');
            el.setAttribute('data-bm', bm || '');
        }

        document.querySelectorAll('.qis-announcement-card').forEach(function (card) {
            card.addEventListener('click', function () {
                var payload = card.querySelector('.js-announcement-payload');
                var modal = document.getElementById('qisModalAnnouncement');
                if (!payload || !modal) return;

                setBilingual(modal.querySelector('.js-am-title'), payload.dataset.titleEn, payload.dataset.titleBm);
                setBilingual(modal.querySelector('.js-am-body'), payload.dataset.bodyEn, payload.dataset.bodyBm);

                modal.querySelector('.js-am-released-at').textContent = payload.dataset.releasedAt || '';
                modal.querySelector('.js-am-released-by').textContent = payload.dataset.releasedBy || '';

                var expiryEl = modal.querySelector('.js-am-expiry');
                if (payload.dataset.expiresAt) {
                    setBilingual(expiryEl, payload.dataset.expiresAt, payload.dataset.expiresAt);
                } else {
                    setBilingual(expiryEl, 'No expiry', 'Tiada tamat tempoh');
                }

                applyLang(currentLang);
                modal.classList.add('qis-open');
            });
        });

        document.querySelectorAll('[data-close-modal]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                btn.closest('.qis-modal-overlay').classList.remove('qis-open');
            });
        });

        document.querySelectorAll('.qis-modal-overlay').forEach(function (overlay) {
            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) overlay.classList.remove('qis-open');
            });
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.qis-modal-overlay.qis-open').forEach(function (overlay) {
                    overlay.classList.remove('qis-open');
                });
            }
        });
    })();
</script>
@endsection