@extends('pages.front')

@section('content')
<div class="qis-body">

    {{-- Demo data fallback — replace by passing $announcements from a controller,
         e.g. Announcement::where('expires_at', '>=', now())->orWhereNull('expires_at')
                ->orderByDesc('released_at')->get() --}}
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
        ]);
    @endphp

    {{-- =============================== NAVBAR =============================== --}}
    <header class="qis-nav">
        <div class="qis-nav-inner">
            <a href="/" class="qis-brand">
                <img src="{{ asset('images/logo-DOA.png') }}" alt="Logo">
                <span class="qis-brand-text">
                    <b>QIS</b>
                    <small data-en="Plant Quarantine Info &amp; Services" data-bm="Maklumat &amp; Perkhidmatan Kuarantin">Plant Quarantine Info &amp; Services</small>
                </span>
            </a>

            <ul class="qis-nav-links">
                <li><a href="#qis-about" data-en="About" data-bm="Tentang">About</a></li>
                <li><a href="#qis-services" data-en="Services" data-bm="Perkhidmatan">Services</a></li>
                <li><a href="#qis-announcements" data-en="Announcements" data-bm="Pengumuman">Announcements</a></li>
                <li><a href="#qis-gallery" data-en="Gallery" data-bm="Galeri">Gallery</a></li>
                <li><a href="#qis-contact" data-en="Contact" data-bm="Hubungi">Contact</a></li>
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

    {{-- =============================== HERO =============================== --}}
    {{-- Image URL is injected here via asset() so it resolves correctly no matter
         how the webserver's document root is configured; qis-landing.css consumes
         it through the --hero-bg-image custom property (see additions below). --}}
    <section class="qis-hero" style="--hero-bg-image: url('{{ asset('images/background.jpg') }}')">
        <div class="qis-container qis-hero-grid">
            <div>
                <span class="qis-eyebrow" data-en="Jabatan Pertanian Sabah &middot; Plant Biosecurity Division" data-bm="Jabatan Pertanian Sabah &middot; Bahagian Biosekuriti Tumbuhan">Jabatan Pertanian Sabah &middot; Plant Biosecurity Division</span>

                <h1 data-en="Every shipment verified. Every harvest protected." data-bm="Setiap penghantaran disahkan. Setiap hasil dilindungi.">Every shipment verified. Every harvest protected.</h1>

                <p class="qis-lead" data-en="QIS is Sabah's digital gateway for plant quarantine permits and certificates. Apply, track and clear your agricultural import or export shipments from any device, anywhere in the state."
                   data-bm="QIS ialah get digital kuarantin tumbuhan Sabah. Mohon, jejak dan luluskan penghantaran import atau eksport barangan pertanian anda melalui sebarang peranti, di mana-mana sahaja di negeri ini.">
                    QIS is Sabah's digital gateway for plant quarantine permits and certificates. Apply, track and clear your agricultural import or export shipments from any device, anywhere in the state.
                </p>

                <div class="qis-hero-cta">
                    <a href="/login" class="qis-btn-primary" data-en="Apply for a Permit" data-bm="Mohon Permit">
                        Apply for a Permit <i class='bx bx-right-arrow-alt'></i>
                    </a>
                    <a href="#qis-services" class="qis-btn-ghost" data-en="Explore Services" data-bm="Lihat Perkhidmatan">Explore Services</a>
                </div>
            </div>

            <div class="qis-radar-card">
                <div class="qis-radar-head">
                    <span data-en="SABAH QUARANTINE NETWORK" data-bm="RANGKAIAN KUARANTIN SABAH">SABAH QUARANTINE NETWORK</span>
                    <span class="qis-live"><i class='bx bxs-circle'></i> <span data-en="LIVE" data-bm="LANGSUNG">LIVE</span></span>
                </div>

                <div class="qis-radar-map">
                    <div class="qis-radar-sweep"></div>
                    <div class="qis-node qis-node--kk"><span class="qis-dot"></span><span>KK PORT</span></div>
                    <div class="qis-node qis-node--sep"><span class="qis-dot"></span><span>SEPANGGAR</span></div>
                    <div class="qis-node qis-node--labu"><span class="qis-dot"></span><span>LABUAN</span></div>
                    <div class="qis-node qis-node--sdk"><span class="qis-dot"></span><span>SANDAKAN</span></div>
                    <div class="qis-node qis-node--twu"><span class="qis-dot"></span><span>TAWAU</span></div>
                </div>

                <div class="qis-radar-terminal" id="qisTerminal">SCANNING CHECKPOINT: KK PORT&hellip;</div>
            </div>
        </div>
    </section>

    {{-- =============================== ANNOUNCEMENT TICKER =============================== --}}
    <div class="qis-ticker">
        <div class="qis-ticker-track" id="qisTickerTrack">
            <span class="qis-ticker-item"><b data-en="NOTICE" data-bm="NOTIS">NOTICE</b><span data-en="System maintenance on 25 July, 2:00&ndash;4:00 AM." data-bm="Penyelenggaraan sistem pada 25 Julai, 2:00&ndash;4:00 pagi.">System maintenance on 25 July, 2:00&ndash;4:00 AM.</span></span>
            <span class="qis-ticker-item"><b data-en="NEW" data-bm="BAHARU">NEW</b><span data-en="Phytosanitary certificates now sync directly with myPhyto." data-bm="Sijil Fitosanitasi kini disegerakkan terus dengan myPhyto.">Phytosanitary certificates now sync directly with myPhyto.</span></span>
            <span class="qis-ticker-item"><b data-en="PAYMENTS" data-bm="PEMBAYARAN">PAYMENTS</b><span data-en="SabahPay is now supported for all permit fees." data-bm="SabahPay kini disokong untuk semua bayaran permit.">SabahPay is now supported for all permit fees.</span></span>
            <span class="qis-ticker-item"><b data-en="REMINDER" data-bm="PERINGATAN">REMINDER</b><span data-en="Update your company profile before 31 August." data-bm="Kemas kini profil syarikat anda sebelum 31 Ogos.">Update your company profile before 31 August.</span></span>
            <!-- duplicate for seamless loop -->
            <span class="qis-ticker-item"><b data-en="NOTICE" data-bm="NOTIS">NOTICE</b><span data-en="System maintenance on 25 July, 2:00&ndash;4:00 AM." data-bm="Penyelenggaraan sistem pada 25 Julai, 2:00&ndash;4:00 pagi.">System maintenance on 25 July, 2:00&ndash;4:00 AM.</span></span>
            <span class="qis-ticker-item"><b data-en="NEW" data-bm="BAHARU">NEW</b><span data-en="Phytosanitary certificates now sync directly with myPhyto." data-bm="Sijil Fitosanitasi kini disegerakkan terus dengan myPhyto.">Phytosanitary certificates now sync directly with myPhyto.</span></span>
            <span class="qis-ticker-item"><b data-en="PAYMENTS" data-bm="PEMBAYARAN">PAYMENTS</b><span data-en="SabahPay is now supported for all permit fees." data-bm="SabahPay kini disokong untuk semua bayaran permit.">SabahPay is now supported for all permit fees.</span></span>
            <span class="qis-ticker-item"><b data-en="REMINDER" data-bm="PERINGATAN">REMINDER</b><span data-en="Update your company profile before 31 August." data-bm="Kemas kini profil syarikat anda sebelum 31 Ogos.">Update your company profile before 31 August.</span></span>
        </div>
    </div>

    {{-- =============================== ABOUT =============================== --}}
    <section class="qis-section" id="qis-about">
        <div class="qis-container qis-about-grid">
            <div>
                <span class="qis-eyebrow" data-en="About QIS" data-bm="Tentang QIS">About QIS</span>
                <h2 class="qis-h2 mt-2" data-en="Sabah Plant Quarantine Information &amp; Services Centre" data-bm="Pusat Maklumat dan Perkhidmatan Kuarantin Tumbuhan Sabah">Sabah Plant Quarantine Information &amp; Services Centre</h2>
                <p class="qis-lead mt-3" data-en="QIS is a digital initiative by the Sabah Department of Agriculture that brings plant quarantine services online for the agrifood industry and the public. It replaces over-the-counter applications with a smart, end-to-end platform &mdash; from permit applications to inspection, payment and certificate issuance &mdash; as part of the state's Smart Government strategy."
                   data-bm="QIS ialah inisiatif digital Jabatan Pertanian Sabah yang membawa perkhidmatan kuarantin tumbuhan atas talian untuk industri agromakanan dan orang awam. Ia menggantikan permohonan di kaunter dengan platform pintar hujung-ke-hujung &mdash; daripada permohonan permit hinggalah pemeriksaan, pembayaran dan pengeluaran sijil &mdash; sebagai sebahagian daripada Pelan Strategik Smart Government negeri.">
                    QIS is a digital initiative by the Sabah Department of Agriculture that brings plant quarantine services online for the agrifood industry and the public. It replaces over-the-counter applications with a smart, end-to-end platform &mdash; from permit applications to inspection, payment and certificate issuance &mdash; as part of the state's Smart Government strategy.
                </p>

                <div class="qis-integration-row">
                    <div class="qis-integration-pill">
                        <i class='bx bx-certification'></i>
                        <div>
                            <b>myPhyto</b>
                            <span data-en="Phytosanitary certificates issued in sync with the national system." data-bm="Sijil Fitosanitasi dikeluarkan selaras dengan sistem kebangsaan.">Phytosanitary certificates issued in sync with the national system.</span>
                        </div>
                    </div>
                    <div class="qis-integration-pill">
                        <i class='bx bx-wallet'></i>
                        <div>
                            <b>SabahPay</b>
                            <span data-en="Settle permit fees online &mdash; no counter visit required." data-bm="Selesaikan bayaran permit atas talian &mdash; tanpa perlu ke kaunter.">Settle permit fees online &mdash; no counter visit required.</span>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <div class="qis-5a-grid">
                    <div class="qis-5a-chip">
                        <span class="qis-5a-letter">A</span>
                        <small data-en="Accessible" data-bm="Boleh Diakses">Accessible</small>
                    </div>
                    <div class="qis-5a-chip">
                        <span class="qis-5a-letter">A</span>
                        <small data-en="by Anyone" data-bm="Sesiapa Sahaja">by Anyone</small>
                    </div>
                    <div class="qis-5a-chip">
                        <span class="qis-5a-letter">A</span>
                        <small data-en="Any Time" data-bm="Bila-bila Masa">Any Time</small>
                    </div>
                    <div class="qis-5a-chip">
                        <span class="qis-5a-letter">A</span>
                        <small data-en="Anywhere" data-bm="Di Mana-mana">Anywhere</small>
                    </div>
                    <div class="qis-5a-chip">
                        <span class="qis-5a-letter">A</span>
                        <small data-en="Any Mobile Device" data-bm="Sebarang Peranti">Any Mobile Device</small>
                    </div>
                </div>
                <p class="qis-lead mt-3" style="font-size:13px" data-en="The 5A principle behind QIS: a service that meets you wherever you're applying from." data-bm="Prinsip 5A di sebalik QIS: perkhidmatan yang menemui anda di mana sahaja anda memohon.">
                    The 5A principle behind QIS: a service that meets you wherever you're applying from.
                </p>
            </div>
        </div>
    </section>

    {{-- =============================== SERVICES =============================== --}}
    <section class="qis-section-tight" id="qis-services" style="background:var(--gray-1)">
        <div class="qis-container">
            <div class="text-center" style="max-width:640px;margin:0 auto">
                <span class="qis-eyebrow" data-en="Applications &amp; Certificates" data-bm="Permohonan &amp; Sijil">Applications &amp; Certificates</span>
                <h2 class="qis-h2 mt-2" data-en="Three ways to move goods, one platform to manage them" data-bm="Tiga cara menggerakkan barangan, satu platform untuk menguruskannya">Three ways to move goods, one platform to manage them</h2>
            </div>

            <div class="qis-service-grid">
                <button type="button" class="qis-service-card" data-modal="qisModalImport">
                    <div class="qis-icon-wrap"><i class='bx bx-package'></i></div>
                    <h5 data-en="Import Permit" data-bm="Permit Import">Import Permit</h5>
                    <p data-en="Official authorization to import regulated agricultural goods into Sabah." data-bm="Kebenaran rasmi untuk mengimport barangan pertanian terkawal ke Sabah.">Official authorization to import regulated agricultural goods into Sabah.</p>
                    <span class="qis-card-link" data-en="View details" data-bm="Lihat Butiran">View details <i class='bx bx-right-arrow-alt'></i></span>
                </button>

                <button type="button" class="qis-service-card" data-modal="qisModalInspection">
                    <div class="qis-icon-wrap"><i class='bx bx-search-alt'></i></div>
                    <h5 data-en="Inspection Certificate" data-bm="Sijil Pemeriksaan">Inspection Certificate</h5>
                    <p data-en="Required for agricultural goods not covered under the standard Import Permit list." data-bm="Diperlukan bagi barangan pertanian yang tidak disenaraikan di bawah Permit Import standard.">Required for agricultural goods not covered under the standard Import Permit list.</p>
                    <span class="qis-card-link" data-en="View details" data-bm="Lihat Butiran">View details <i class='bx bx-right-arrow-alt'></i></span>
                </button>

                <button type="button" class="qis-service-card" data-modal="qisModalConsignment">
                    <div class="qis-icon-wrap"><i class='bx bx-file'></i></div>
                    <h5 data-en="Consignment Certificate" data-bm="Sijil Consignment">Consignment Certificate</h5>
                    <p data-en="Export authorization dedicated to the movement of agricultural goods to Brunei." data-bm="Kebenaran eksport khusus untuk pergerakan barangan pertanian ke Brunei.">Export authorization dedicated to the movement of agricultural goods to Brunei.</p>
                    <span class="qis-card-link" data-en="View details" data-bm="Lihat Butiran">View details <i class='bx bx-right-arrow-alt'></i></span>
                </button>
            </div>
        </div>
    </section>

    {{-- =============================== ANNOUNCEMENTS =============================== --}}
    <section class="qis-section-tight" id="qis-announcements">
        <div class="qis-container">
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-3">
                <div>
                    <span class="qis-eyebrow" data-en="Announcements" data-bm="Pengumuman">Announcements</span>
                    <h2 class="qis-h2 mt-2" data-en="Latest updates from QIS" data-bm="Kemas kini terkini daripada QIS">Latest updates from QIS</h2>
                </div>
                <a href="{{ route('announcements.index') ?? '/announcements' }}" class="qis-btn-ghost"
                    data-en="View All Announcements" data-bm="Lihat Semua Pengumuman">
                    View All Announcements <i class='bx bx-right-arrow-alt'></i>
                </a>
            </div>

            <div class="qis-announcement-grid">
                @foreach ($announcements->take(3) as $item)
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

                        {{-- payload the shared modal reads on click --}}
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
        </div>
    </section>

    {{-- =============================== GALLERY =============================== --}}
    <section class="qis-section" id="qis-gallery">
        <div class="qis-container">
            <span class="qis-eyebrow" data-en="Gallery" data-bm="Galeri">Gallery</span>
            <h2 class="qis-h2 mt-2" data-en="Checkpoints, in the field" data-bm="Titik Pemeriksaan, di Lapangan">Checkpoints, in the field</h2>

            <div class="qis-gallery-grid">
                {{-- TODO: once real photos are ready, add data-image-src="{{ asset('images/gallery/kk-port.jpg') }}" to each tile below --}}
                <div class="qis-gallery-tile" data-modal="qisModalImage" data-image-src=""
                    data-caption-en="Kota Kinabalu Port checkpoint" data-caption-bm="Titik Pemeriksaan Pelabuhan Kota Kinabalu">
                    <span class="qis-tag">IMG_01</span>
                    <i class='bx bx-image-alt'></i>
                    <span data-en="Kota Kinabalu Port checkpoint" data-bm="Titik Pemeriksaan Pelabuhan Kota Kinabalu">Kota Kinabalu Port checkpoint</span>
                </div>
                <div class="qis-gallery-tile" data-modal="qisModalImage" data-image-src=""
                    data-caption-en="Quarantine laboratory" data-caption-bm="Makmal Kuarantin">
                    <span class="qis-tag">IMG_02</span>
                    <i class='bx bx-image-alt'></i>
                    <span data-en="Quarantine laboratory" data-bm="Makmal Kuarantin">Quarantine laboratory</span>
                </div>
                <div class="qis-gallery-tile" data-modal="qisModalImage" data-image-src=""
                    data-caption-en="Cargo inspection" data-caption-bm="Pemeriksaan Kargo">
                    <span class="qis-tag">IMG_03</span>
                    <i class='bx bx-image-alt'></i>
                    <span data-en="Cargo inspection" data-bm="Pemeriksaan Kargo">Cargo inspection</span>
                </div>
                <div class="qis-gallery-tile" data-modal="qisModalImage" data-image-src=""
                    data-caption-en="Agricultural farms across Sabah" data-caption-bm="Ladang Pertanian di Sabah">
                    <span class="qis-tag">IMG_04</span>
                    <i class='bx bx-image-alt'></i>
                    <span data-en="Agricultural farms across Sabah" data-bm="Ladang Pertanian di Sabah">Agricultural farms across Sabah</span>
                </div>
            </div>
        </div>
    </section>

    {{-- =============================== CONTACT =============================== --}}
    <section class="qis-section qis-contact-section" id="qis-contact">
        <div class="qis-container">
            <span class="qis-eyebrow" data-en="Get in Touch" data-bm="Hubungi Kami">Get in Touch</span>
            <h2 class="qis-h2 mt-2" data-en="Plant Biosecurity Division, Sabah Department of Agriculture" data-bm="Bahagian Biosekuriti Tumbuhan, Jabatan Pertanian Sabah">Plant Biosecurity Division, Sabah Department of Agriculture</h2>

            <div class="qis-contact-grid">
                <div class="qis-contact-card">
                    <div class="qis-contact-row">
                        <i class='bx bx-map'></i>
                        <div>
                            <b data-en="Address" data-bm="Alamat">Address</b>
                            <span>
                                Bahagian Biosekuriti Tumbuhan,<br>
                                Jabatan Pertanian Sabah,<br>
                                Aras 1, 5, 6 &amp; 7, Wisma Pertanian Sabah,<br>
                                Jalan Tasik Luyang, Off Jalan Maktab Gaya,<br>
                                Beg Berkunci No. 2050, 88632 Kota Kinabalu, Sabah, Malaysia
                            </span>
                        </div>
                    </div>

                    <div class="qis-contact-row">
                        <i class='bx bx-phone'></i>
                        <div>
                            <b data-en="Phone" data-bm="Telefon">Phone</b>
                            <a href="tel:+6088283283">088-283 283</a>
                            <a href="tel:+6088283282">088-283 282</a>
                        </div>
                    </div>

                    <div class="qis-contact-row">
                        <i class='bx bx-printer'></i>
                        <div>
                            <b data-en="Fax" data-bm="Faks">Fax</b>
                            <span>088-239 046</span>
                        </div>
                    </div>

                    <div class="qis-contact-row">
                        <i class='bx bx-envelope'></i>
                        <div>
                            <b data-en="Email" data-bm="Emel">Email</b>
                            <a href="mailto:doasabah@sabah.gov.my">doasabah@sabah.gov.my</a>
                            <a href="mailto:aduan.tani@sabah.gov.my" data-en="(complaints / feedback)" data-bm="(aduan / cadangan)">aduan.tani@sabah.gov.my</a>
                        </div>
                    </div>

                    <div class="qis-contact-row">
                        <i class='bx bx-time-five'></i>
                        <div>
                            <b data-en="Office Hours" data-bm="Waktu Pejabat">Office Hours</b>
                            <span data-en="Monday &ndash; Friday, 8:00 AM &ndash; 5:00 PM (closed on public holidays)" data-bm="Isnin &ndash; Jumaat, 8:00 pagi &ndash; 5:00 petang (tutup pada cuti umum)">Monday &ndash; Friday, 8:00 AM &ndash; 5:00 PM (closed on public holidays)</span>
                        </div>
                    </div>
                </div>

                <div class="qis-map-placeholder">
                    <i class='bx bx-map-pin'></i>
                    <span data-en="Map embed placeholder &mdash; Wisma Pertanian Sabah" data-bm="Placeholder Peta &mdash; Wisma Pertanian Sabah">Map embed placeholder &mdash; Wisma Pertanian Sabah</span>
                </div>
            </div>
        </div>
    </section>

    {{-- =============================== FOOTER =============================== --}}
    <footer class="qis-footer">
        <div class="qis-container">
            <div class="qis-footer-top">
                <div class="qis-footer-brand">
                    <img src="{{ asset('images/logo-DOA.png') }}" alt="Logo" style="height:28px">
                    <span>QIS &middot; Jabatan Pertanian Sabah</span>
                </div>
                <ul class="qis-footer-links">
                    <li><a href="#qis-about" data-en="About" data-bm="Tentang">About</a></li>
                    <li><a href="#qis-services" data-en="Services" data-bm="Perkhidmatan">Services</a></li>
                    <li><a href="#qis-announcements" data-en="Announcements" data-bm="Pengumuman">Announcements</a></li>
                    <li><a href="#qis-gallery" data-en="Gallery" data-bm="Galeri">Gallery</a></li>
                    <li><a href="#qis-contact" data-en="Contact" data-bm="Hubungi">Contact</a></li>
                </ul>
                <div class="qis-footer-social">
                    <a href="#"><i class='bx bxl-facebook'></i></a>
                    <a href="#"><i class='bx bx-envelope'></i></a>
                    <a href="#"><i class='bx bx-globe'></i></a>
                </div>
            </div>
            <div class="qis-footer-bottom">
                <span>&copy; {{ date('Y') }} Jabatan Pertanian Sabah. <span data-en="All rights reserved." data-bm="Hak cipta terpelihara.">All rights reserved.</span></span>
                <span data-en="Built for Smart Government Sabah" data-bm="Dibina untuk Smart Government Sabah">Built for Smart Government Sabah</span>
            </div>
        </div>
    </footer>

    {{-- =============================== SERVICE MODALS =============================== --}}
    <div class="qis-modal-overlay" id="qisModalImport">
        <div class="qis-modal">
            <button type="button" class="qis-modal-close" data-close-modal>&times;</button>
            <span class="qis-modal-tag" data-en="APPLICATION TYPE 01" data-bm="JENIS PERMOHONAN 01">APPLICATION TYPE 01</span>
            <div class="qis-icon-wrap"><i class='bx bx-package'></i></div>
            <h4 data-en="Import Permit" data-bm="Permit Import">Import Permit</h4>
            <p data-en="Official authorization to import regulated agricultural goods into Sabah. Covers goods listed under the standard schedule and is the primary entry point for commercial importers." data-bm="Kebenaran rasmi untuk mengimport barangan pertanian terkawal ke Sabah. Merangkumi barangan yang disenaraikan di bawah jadual standard dan merupakan titik masuk utama bagi pengimport komersial.">
                Official authorization to import regulated agricultural goods into Sabah. Covers goods listed under the standard schedule and is the primary entry point for commercial importers.
            </p>
            <div class="qis-modal-steps">
                <div class="qis-modal-step"><div class="qis-step-no">01</div><div data-en="Submit application" data-bm="Hantar Permohonan">Submit application</div></div>
                <div class="qis-modal-step"><div class="qis-step-no">02</div><div data-en="Clerk &amp; officer review" data-bm="Semakan Kerani &amp; Pegawai">Clerk &amp; officer review</div></div>
                <div class="qis-modal-step"><div class="qis-step-no">03</div><div data-en="Pay via SabahPay" data-bm="Bayar melalui SabahPay">Pay via SabahPay</div></div>
                <div class="qis-modal-step"><div class="qis-step-no">04</div><div data-en="Download permit" data-bm="Muat Turun Permit">Download permit</div></div>
            </div>
            <a href="/login" class="qis-btn-primary" data-en="Start Application" data-bm="Mulakan Permohonan">Start Application</a>
        </div>
    </div>

    <div class="qis-modal-overlay" id="qisModalInspection">
        <div class="qis-modal">
            <button type="button" class="qis-modal-close" data-close-modal>&times;</button>
            <span class="qis-modal-tag" data-en="APPLICATION TYPE 02" data-bm="JENIS PERMOHONAN 02">APPLICATION TYPE 02</span>
            <div class="qis-icon-wrap"><i class='bx bx-search-alt'></i></div>
            <h4 data-en="Inspection Certificate" data-bm="Sijil Pemeriksaan">Inspection Certificate</h4>
            <p data-en="Required for agricultural goods not covered under the standard Import Permit list. A quarantine officer inspects the consignment before a certificate is issued." data-bm="Diperlukan bagi barangan pertanian yang tidak disenaraikan di bawah Permit Import standard. Pegawai kuarantin akan memeriksa penghantaran sebelum sijil dikeluarkan.">
                Required for agricultural goods not covered under the standard Import Permit list. A quarantine officer inspects the consignment before a certificate is issued.
            </p>
            <div class="qis-modal-steps">
                <div class="qis-modal-step"><div class="qis-step-no">01</div><div data-en="Submit application" data-bm="Hantar Permohonan">Submit application</div></div>
                <div class="qis-modal-step"><div class="qis-step-no">02</div><div data-en="Officer inspection" data-bm="Pemeriksaan Pegawai">Officer inspection</div></div>
                <div class="qis-modal-step"><div class="qis-step-no">03</div><div data-en="Pay via SabahPay" data-bm="Bayar melalui SabahPay">Pay via SabahPay</div></div>
                <div class="qis-modal-step"><div class="qis-step-no">04</div><div data-en="Download certificate" data-bm="Muat Turun Sijil">Download certificate</div></div>
            </div>
            <a href="/login" class="qis-btn-primary" data-en="Start Application" data-bm="Mulakan Permohonan">Start Application</a>
        </div>
    </div>

    <div class="qis-modal-overlay" id="qisModalConsignment">
        <div class="qis-modal">
            <button type="button" class="qis-modal-close" data-close-modal>&times;</button>
            <span class="qis-modal-tag" data-en="APPLICATION TYPE 03" data-bm="JENIS PERMOHONAN 03">APPLICATION TYPE 03</span>
            <div class="qis-icon-wrap"><i class='bx bx-file'></i></div>
            <h4 data-en="Consignment Certificate" data-bm="Sijil Consignment">Consignment Certificate</h4>
            <p data-en="Export authorization dedicated to the movement of agricultural goods to Brunei, confirming the consignment meets cross-border requirements." data-bm="Kebenaran eksport khusus untuk pergerakan barangan pertanian ke Brunei, mengesahkan penghantaran memenuhi keperluan rentas sempadan.">
                Export authorization dedicated to the movement of agricultural goods to Brunei, confirming the consignment meets cross-border requirements.
            </p>
            <div class="qis-modal-steps">
                <div class="qis-modal-step"><div class="qis-step-no">01</div><div data-en="Submit application" data-bm="Hantar Permohonan">Submit application</div></div>
                <div class="qis-modal-step"><div class="qis-step-no">02</div><div data-en="Officer review" data-bm="Semakan Pegawai">Officer review</div></div>
                <div class="qis-modal-step"><div class="qis-step-no">03</div><div data-en="Pay via SabahPay" data-bm="Bayar melalui SabahPay">Pay via SabahPay</div></div>
                <div class="qis-modal-step"><div class="qis-step-no">04</div><div data-en="Download certificate" data-bm="Muat Turun Sijil">Download certificate</div></div>
            </div>
            <a href="/login" class="qis-btn-primary" data-en="Start Application" data-bm="Mulakan Permohonan">Start Application</a>
        </div>
    </div>

    {{-- =============================== ANNOUNCEMENT MODAL =============================== --}}
    <x-announcement-modal />

    {{-- =============================== IMAGE MODAL (shared, populated by JS) =============================== --}}
    <div class="qis-modal-overlay" id="qisModalImage">
        <div class="qis-modal qis-modal--image">
            <button type="button" class="qis-modal-close" data-close-modal>&times;</button>
            <div class="js-image-slot">
                <div class="qis-modal-image-placeholder">
                    <i class='bx bx-image-alt'></i>
                    <span data-en="Photo coming soon" data-bm="Gambar akan datang tidak lama lagi">Photo coming soon</span>
                </div>
            </div>
            <figcaption class="js-image-caption" data-en="" data-bm=""></figcaption>
        </div>
    </div>

</div>

<script>
    (function () {
        var currentLang = 'en';

        // ---------- language toggle ----------
        var langButtons = document.querySelectorAll('.qis-lang-btn');

        function applyLang(lang) {
            currentLang = lang;
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

        // ---------- checkpoint terminal rotator ----------
        var terminal = document.getElementById('qisTerminal');
        var checkpoints = ['KK PORT', 'SEPANGGAR', 'SANDAKAN', 'TAWAU', 'LABUAN'];
        var idx = 0;
        if (terminal && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            setInterval(function () {
                idx = (idx + 1) % checkpoints.length;
                terminal.textContent = 'SCANNING CHECKPOINT: ' + checkpoints[idx] + '\u2026';
                setTimeout(function () {
                    terminal.textContent = 'CHECKPOINT ' + checkpoints[idx] + ': CLEARED';
                }, 1400);
            }, 3600);
        }

        // ---------- static service modals (Import / Inspection / Consignment) ----------
        document.querySelectorAll('[data-modal="qisModalImport"], [data-modal="qisModalInspection"], [data-modal="qisModalConsignment"]').forEach(function (card) {
            card.addEventListener('click', function () {
                var modal = document.getElementById(card.dataset.modal);
                if (modal) modal.classList.add('qis-open');
            });
        });

        // ---------- announcement modal ----------
        // Handled entirely by the <x-announcement-modal /> component now —
        // no page-side JS needed for it.

        function setBilingual(el, en, bm) {
            if (!el) return;
            el.setAttribute('data-en', en || '');
            el.setAttribute('data-bm', bm || '');
        }

        // ---------- image modal (shared, dynamic content) ----------
        document.querySelectorAll('.qis-gallery-tile[data-modal="qisModalImage"]').forEach(function (tile) {
            tile.addEventListener('click', function () {
                var modal = document.getElementById('qisModalImage');
                if (!modal) return;

                var slot = modal.querySelector('.js-image-slot');
                var src = tile.dataset.imageSrc;

                if (src) {
                    slot.innerHTML = '<img src="' + src + '" alt="">';
                } else {
                    slot.innerHTML =
                        '<div class="qis-modal-image-placeholder">' +
                        '<i class="bx bx-image-alt"></i>' +
                        '<span data-en="Photo coming soon" data-bm="Gambar akan datang tidak lama lagi">Photo coming soon</span>' +
                        '</div>';
                }

                setBilingual(modal.querySelector('.js-image-caption'), tile.dataset.captionEn, tile.dataset.captionBm);
                applyLang(currentLang);
                modal.classList.add('qis-open');
            });
        });

        // ---------- close handlers (shared by every modal) ----------
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