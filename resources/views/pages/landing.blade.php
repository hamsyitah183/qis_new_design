@extends('pages.front')

@section('content')
    <div class="qis-body">

        {{-- =============================== NAVBAR =============================== --}}
        <header class="qis-nav">
            <div class="qis-nav-inner">
                <a href="/" class="qis-brand">
                    <img src="{{ asset('images/logo-DOA.png') }}" alt="Logo">
                    <span class="qis-brand-text">
                        <b>QIS</b>
                        <small data-en="Plant Quarantine Info &amp; Services"
                            data-bm="Maklumat &amp; Perkhidmatan Kuarantin">Plant Quarantine Info &amp; Services</small>
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
                    @if (Auth::guard('public')->check() || Auth::guard('internal')->check())
                        <a href="/dashboard" class="qis-btn-ghost d-md-inline-flex" data-en="Dashboard"
                            data-bm="Dashboard">Dashboard</a>
                    @else
                        <a href="/login" class="qis-btn-ghost d-md-inline-flex" data-en="Sign In"
                            data-bm="Log Masuk">Sign In</a>
                    @endif

                  
                </div>
            </div>
        </header>

        {{-- =============================== HERO =============================== --}}
        <section class="qis-hero" style="background: url('/images/background.jpg')">
            <div class="qis-container qis-hero-grid">
                <div>
                    <span class="qis-eyebrow" data-en="Jabatan Pertanian Sabah &middot; Plant Biosecurity Division"
                        data-bm="Jabatan Pertanian Sabah &middot; Bahagian Biosekuriti Tumbuhan">Jabatan Pertanian Sabah
                        &middot; Plant Biosecurity Division</span>

                    <h1 data-en="Every shipment verified. Every harvest protected."
                        data-bm="Setiap penghantaran disahkan. Setiap hasil dilindungi.">Every shipment verified. Every
                        harvest protected.</h1>

                    <p class="qis-lead text-white"
                        data-en="QIS is Sabah's digital gateway for plant quarantine permits and certificates. Apply, track and clear your agricultural import or export shipments from any device, anywhere in the state."
                        data-bm="QIS ialah get digital kuarantin tumbuhan Sabah. Mohon, jejak dan luluskan penghantaran import atau eksport barangan pertanian anda melalui sebarang peranti, di mana-mana sahaja di negeri ini.">
                        QIS is Sabah's digital gateway for plant quarantine permits and certificates. Apply, track and clear
                        your agricultural import or export shipments from any device, anywhere in the state.
                    </p>

                    <div class="qis-hero-cta">
                        <a href="/login" class="qis-btn-primary" data-en="Apply for a Permit" data-bm="Mohon Permit">
                            Apply for a Permit <i class='bx bx-right-arrow-alt'></i>
                        </a>
                        <a href="#qis-services" class="qis-btn-secondary" data-en="Explore Services"
                            data-bm="Lihat Perkhidmatan">Explore Services</a>
                    </div>
                </div>

                <div class="qis-radar-card d-none">
                    <div class="qis-radar-head">
                        <span data-en="SABAH QUARANTINE NETWORK" data-bm="RANGKAIAN KUARANTIN SABAH">SABAH QUARANTINE
                            NETWORK</span>
                        <span class="qis-live"><i class='bx bxs-circle'></i> <span data-en="LIVE"
                                data-bm="LANGSUNG">LIVE</span></span>
                    </div>

                    <div class="qis-radar-map">
                        <div class="qis-radar-sweep"></div>
                        @foreach ($entryPoints as $node)
                            @php
                                // Map district ID to CSS class
                                $classMap = [
                                    1 => 'kk',
                                    2 => 'kud',
                                    3 => 'sdk',
                                    4 => 'ld',
                                    5 => 'twu',
                                    6 => 'kun',
                                    7 => 'sem',
                                    8 => 'men',
                                    9 => 'sip',
                                ];
                                $nodeClass = $classMap[$node['district_id']] ?? 'default';
                                // Short label: use district name or first word of entry name
                                $label = $node['district_name'];
                            @endphp
                            <div class="qis-node qis-node--{{ $nodeClass }}" data-district="{{ $node['district_id'] }}"
                                data-transport="{{ $node['transport_type'] }}" title="{{ $node['entry_name'] }}">
                                <span class="qis-dot"></span>
                                <span>{{ $label }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="qis-radar-terminal" id="qisTerminal">SCANNING CHECKPOINT: KK PORT&hellip;</div>
                </div>
            </div>
        </section>

        {{-- =============================== ANNOUNCEMENT TICKER =============================== --}}
        <div class="qis-ticker">
            <div class="qis-ticker-track" id="qisTickerTrack">
                @foreach ($announcements as $item)
                    <span class="qis-ticker-item"><b data-en="NOTICE" data-bm="NOTIS">NOTICE</b><span
                            data-en="{{ $item->title }}" data-bm="{{ $item->title }}">{{ $item->title }}</span></span>
                @endforeach
                <!-- duplicate for seamless loop -->
                @foreach ($announcements as $item)
                    <span class="qis-ticker-item"><b data-en="NOTICE" data-bm="NOTIS">NOTICE</b><span
                            data-en="{{ $item->title }}"
                            data-bm="{{ $item->title }}">{{ $item->title }}</span></span>
                @endforeach
            </div>
        </div>

        {{-- =============================== ABOUT =============================== --}}
        <section class="qis-section" id="qis-about">
            <div class="qis-container">
                <div>
                    <span class="qis-eyebrow" data-en="About QIS" data-bm="Tentang QIS">About QIS</span>
                    <h2 class="qis-h2 mt-2" data-en="Sabah Plant Quarantine Information &amp; Services Centre"
                        data-bm="Pusat Maklumat dan Perkhidmatan Kuarantin Tumbuhan Sabah">Sabah Plant Quarantine
                        Information &amp; Services Centre</h2>
                    <p class="qis-lead mt-3"
                        data-en="QIS is a digital initiative by the Sabah Department of Agriculture that brings plant quarantine services online for the agrifood industry and the public. It replaces over-the-counter applications with a smart, end-to-end platform &mdash; from permit applications to inspection, payment and certificate issuance."
                        data-bm="QIS ialah inisiatif digital Jabatan Pertanian Sabah yang membawa perkhidmatan kuarantin tumbuhan atas talian untuk industri agromakanan dan orang awam. Ia menggantikan permohonan di kaunter dengan platform pintar hujung-ke-hujung &mdash; daripada permohonan permit hinggalah pemeriksaan, pembayaran dan pengeluaran sijil .">
                        QIS is a digital initiative by the Sabah Department of Agriculture that brings plant quarantine
                        services
                        online for the agrifood industry and the public. It replaces over-the-counter applications with a
                        smart,
                        end-to-end platform
                    </p>

                    <div class="qis-integration-row">
                        <div class="qis-integration-pill">
                            <i class='bx bx-certification'></i>
                            <div>
                                <b>myPhyto</b>
                                <span data-en="Phytosanitary certificates issued in sync with the national system."
                                    data-bm="Sijil Fitosanitasi dikeluarkan selaras dengan sistem kebangsaan.">Phytosanitary
                                    certificates issued in sync with the national system.</span>
                            </div>
                        </div>
                        <div class="qis-integration-pill">
                            <i class='bx bx-wallet'></i>
                            <div>
                                <b>SabahPay</b>
                                <span data-en="Settle permit fees online &mdash; no counter visit required."
                                    data-bm="Selesaikan bayaran permit atas talian &mdash; tanpa perlu ke kaunter.">Settle
                                    permit fees online &mdash; no counter visit required.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- =============================== SERVICES =============================== --}}
        <section class="qis-section-tight" id="qis-services" style="background:var(--gray-1)">
            <div class="qis-container">
                <div class="text-center" style="max-width:640px;margin:0 auto">
                    <span class="qis-eyebrow" data-en="Applications &amp; Certificates"
                        data-bm="Permohonan &amp; Sijil">Applications &amp; Certificates</span>
                    <h2 class="qis-h2 mt-2" data-en="Three ways to move goods, one platform to manage them"
                        data-bm="Tiga cara menggerakkan barangan, satu platform untuk menguruskannya">Three ways to move
                        goods, one platform to manage them</h2>
                </div>

                <div class="qis-service-grid">
                    <button type="button" class="qis-service-card" data-modal="qisModalImport">
                        <div class="qis-icon-wrap"><i class='bx bx-package'></i></div>
                        <h5 data-en="Import Permit" data-bm="Permit Import">Import Permit</h5>
                        <p data-en="Official authorization to import regulated agricultural goods into Sabah."
                            data-bm="Kebenaran rasmi untuk mengimport barangan pertanian terkawal ke Sabah.">Official
                            authorization to import regulated agricultural goods into Sabah.</p>
                        <span class="qis-card-link" data-en="View details" data-bm="Lihat Butiran">View details <i
                                class='bx bx-right-arrow-alt'></i></span>
                    </button>

                    <button type="button" class="qis-service-card" data-modal="qisModalInspection">
                        <div class="qis-icon-wrap"><i class='bx bx-search-alt'></i></div>
                        <h5 data-en="Inspection Certificate" data-bm="Sijil Pemeriksaan">Inspection Certificate</h5>
                        <p data-en="Required for agricultural goods not covered under the standard Import Permit list."
                            data-bm="Diperlukan bagi barangan pertanian yang tidak disenaraikan di bawah Permit Import standard.">
                            Required for agricultural goods not covered under the standard Import Permit list.</p>
                        <span class="qis-card-link" data-en="View details" data-bm="Lihat Butiran">View details <i
                                class='bx bx-right-arrow-alt'></i></span>
                    </button>

                    <button type="button" class="qis-service-card" data-modal="qisModalConsignment">
                        <div class="qis-icon-wrap"><i class='bx bx-file'></i></div>
                        <h5 data-en="Consignment Certificate" data-bm="Sijil Consignment">Consignment Certificate</h5>
                        <p data-en="Export authorization dedicated to the movement of agricultural goods to Brunei."
                            data-bm="Kebenaran eksport khusus untuk pergerakan barangan pertanian ke Brunei.">Export
                            authorization dedicated to the movement of agricultural goods to Brunei.</p>
                        <span class="qis-card-link" data-en="View details" data-bm="Lihat Butiran">View details <i
                                class='bx bx-right-arrow-alt'></i></span>
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
                        <h2 class="qis-h2 mt-2" data-en="Latest updates from QIS"
                            data-bm="Kemas kini terkini daripada QIS">Latest updates from QIS</h2>
                    </div>
                    <a href="{{ route('announcements.index') ?? '/announcements' }}" class="qis-btn-ghost"
                        data-en="View All Announcements" data-bm="Lihat Semua Pengumuman">
                        View All Announcements <i class='bx bx-right-arrow-alt'></i>
                    </a>
                </div>

                <div class="qis-announcement-grid">
                    @foreach ($announcements->take(3) as $item)
                        @php
                            $releasedAt = \Carbon\Carbon::parse($item->created_at);
                            $expiresAt = $item->valid_until ? \Carbon\Carbon::parse($item->valid_until) : null;

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

                            <h5 data-en="{{ $item->title }}" data-bm="{{ $item->title }}">
                                @if ($item->pin_announcement)
                                    <i class='bx bxs-pin text-warning me-1' title="Pinned"></i>
                                @endif{{ $item->title }}
                            </h5>

                            <div class="qis-announcement-meta"
                                style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; font-size: 14px; margin-bottom: 0.5rem; line-height: 1.5;">
                                {!! $item->content !!}
                            </div>

                            <div class="qis-announcement-meta">
                                <i class='bx bx-calendar'></i>
                                <span>{{ $releasedAt->format('d M Y') }}</span>
                            </div>

                            <span class="qis-card-link" data-en="Read more" data-bm="Baca lagi">
                                Read more <i class='bx bx-right-arrow-alt'></i>
                            </span>

                            {{-- payload the shared modal reads on click --}}
                            <span class="d-none js-announcement-payload" data-title-en="{{ $item->title }}"
                                data-title-bm="{{ $item->title }}" data-body-en="{{ $item->content }}"
                                data-body-bm="{{ $item->content }}"
                                data-released-at="{{ $releasedAt->format('d M Y') }}"
                                data-released-by="{{ $item->releasedBy ? $item->releasedBy->fullname : 'Admin' }}"
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
                <h2 class="qis-h2 mt-2" data-en="Checkpoints, in the field" data-bm="Titik Pemeriksaan, di Lapangan">
                    Checkpoints, in the field</h2>

                <div class="qis-gallery-grid">
                    @forelse($galleries as $gallery)
                        <div class="qis-gallery-tile"
                            style="background-image: url('{{ asset('storage/' . $gallery->path) }}'); background-size: cover; background-position: center;"
                            data-modal="qisModalImage" data-image-src="{{ asset('storage/' . $gallery->path) }}"
                            data-caption-en="{{ $gallery->name }}"
                            data-caption-bm="{{ $gallery->description ?? $gallery->name }}">
                            <span class="qis-tag">IMG_{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="qis-tile-overlay" data-en="{{ $gallery->name }}"
                                data-bm="{{ $gallery->description ?? $gallery->name }}">
                                {{ $gallery->name }}
                            </span>
                        </div>
                    @empty
                        <div class="col-12 text-center text-muted py-5">
                            <i class="bi bi-image fs-1"></i>
                            <p class="mt-2" data-en="No gallery images available."
                                data-bm="Tiada imej galeri tersedia.">No gallery images available.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        {{-- =============================== CONTACT =============================== --}}
        <section class="qis-section qis-contact-section" id="qis-contact">
            <div class="qis-container">
                <span class="qis-eyebrow" data-en="Get in Touch" data-bm="Hubungi Kami">Get in Touch</span>
                <h2 class="qis-h2 mt-2" data-en="Plant Biosecurity Division, Sabah Department of Agriculture"
                    data-bm="Bahagian Biosekuriti Tumbuhan, Jabatan Pertanian Sabah">Plant Biosecurity Division, Sabah
                    Department of Agriculture</h2>

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
                                <a href="mailto:aduan.tani@sabah.gov.my" data-en="(complaints / feedback)"
                                    data-bm="(aduan / cadangan)">aduan.tani@sabah.gov.my</a>
                            </div>
                        </div>

                        <div class="qis-contact-row">
                            <i class='bx bx-time-five'></i>
                            <div>
                                <b data-en="Office Hours" data-bm="Waktu Pejabat">Office Hours</b>
                                <span data-en="Monday &ndash; Friday, 8:00 AM &ndash; 5:00 PM (closed on public holidays)"
                                    data-bm="Isnin &ndash; Jumaat, 8:00 pagi &ndash; 5:00 petang (tutup pada cuti umum)">Monday
                                    &ndash; Friday, 8:00 AM &ndash; 5:00 PM (closed on public holidays)</span>
                            </div>
                        </div>
                    </div>

                    <div class="qis-map-placeholder">
                        <iframe
                            src="https://maps.google.com/maps?q=Wisma%20Pertanian%20Sabah%20Kota%20Kinabalu&z=15&output=embed"
                            width="100%" height="100%" style="border:0; border-radius:16px;" allowfullscreen=""
                            loading="lazy" referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
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
                        <li><a href="#qis-announcements" data-en="Announcements" data-bm="Pengumuman">Announcements</a>
                        </li>
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
                    <span>&copy; {{ date('Y') }} Jabatan Pertanian Sabah. <span data-en="All rights reserved."
                            data-bm="Hak cipta terpelihara.">All rights reserved.</span></span>
                    <span data-en="Built for Smart Government Sabah" data-bm="Dibina untuk Smart Government Sabah">Built
                        for Smart Government Sabah</span>
                </div>
            </div>
        </footer>

        {{-- =============================== SERVICE MODALS =============================== --}}
        <div class="qis-modal-overlay" id="qisModalImport">
            <div class="qis-modal">
                <button type="button" class="qis-modal-close" data-close-modal>&times;</button>
                <span class="qis-modal-tag" data-en="APPLICATION TYPE 01" data-bm="JENIS PERMOHONAN 01">APPLICATION TYPE
                    01</span>
                <div class="qis-icon-wrap"><i class='bx bx-package'></i></div>
                <h4 data-en="Import Permit" data-bm="Permit Import">Import Permit</h4>
                <p data-en="Official authorization to import regulated agricultural goods into Sabah. Covers goods listed under the standard schedule and is the primary entry point for commercial importers."
                    data-bm="Kebenaran rasmi untuk mengimport barangan pertanian terkawal ke Sabah. Merangkumi barangan yang disenaraikan di bawah jadual standard dan merupakan Pintu Masuk utama bagi pengimport komersial.">
                    Official authorization to import regulated agricultural goods into Sabah. Covers goods listed under the
                    standard schedule and is the primary entry point for commercial importers.
                </p>
                <div class="qis-modal-steps">
                    <div class="qis-modal-step">
                        <div class="qis-step-no">01</div>
                        <div data-en="Submit application" data-bm="Hantar Permohonan">Submit application</div>
                    </div>
                    <div class="qis-modal-step">
                        <div class="qis-step-no">02</div>
                        <div data-en="Clerk &amp; officer review" data-bm="Semakan Kerani &amp; Pegawai">Clerk &amp;
                            officer review</div>
                    </div>
                    <div class="qis-modal-step">
                        <div class="qis-step-no">03</div>
                        <div data-en="Pay via SabahPay" data-bm="Bayar melalui SabahPay">Pay via SabahPay</div>
                    </div>
                    <div class="qis-modal-step">
                        <div class="qis-step-no">04</div>
                        <div data-en="Download permit" data-bm="Muat Turun Permit">Download permit</div>
                    </div>
                </div>
                <a href="/login" class="qis-btn-primary" data-en="Start Application" data-bm="Mulakan Permohonan">Start
                    Application</a>
            </div>
        </div>

        <div class="qis-modal-overlay" id="qisModalInspection">
            <div class="qis-modal">
                <button type="button" class="qis-modal-close" data-close-modal>&times;</button>
                <span class="qis-modal-tag" data-en="APPLICATION TYPE 02" data-bm="JENIS PERMOHONAN 02">APPLICATION TYPE
                    02</span>
                <div class="qis-icon-wrap"><i class='bx bx-search-alt'></i></div>
                <h4 data-en="Inspection Certificate" data-bm="Sijil Pemeriksaan">Inspection Certificate</h4>
                <p data-en="Required for agricultural goods not covered under the standard Import Permit list. A quarantine officer inspects the consignment before a certificate is issued."
                    data-bm="Diperlukan bagi barangan pertanian yang tidak disenaraikan di bawah Permit Import standard. Pegawai kuarantin akan memeriksa penghantaran sebelum sijil dikeluarkan.">
                    Required for agricultural goods not covered under the standard Import Permit list. A quarantine officer
                    inspects the consignment before a certificate is issued.
                </p>
                <div class="qis-modal-steps">
                    <div class="qis-modal-step">
                        <div class="qis-step-no">01</div>
                        <div data-en="Submit application" data-bm="Hantar Permohonan">Submit application</div>
                    </div>
                    <div class="qis-modal-step">
                        <div class="qis-step-no">02</div>
                        <div data-en="Officer inspection" data-bm="Pemeriksaan Pegawai">Officer inspection</div>
                    </div>
                    <div class="qis-modal-step">
                        <div class="qis-step-no">03</div>
                        <div data-en="Pay via SabahPay" data-bm="Bayar melalui SabahPay">Pay via SabahPay</div>
                    </div>
                    <div class="qis-modal-step">
                        <div class="qis-step-no">04</div>
                        <div data-en="Download certificate" data-bm="Muat Turun Sijil">Download certificate</div>
                    </div>
                </div>
                <a href="/login" class="qis-btn-primary" data-en="Start Application" data-bm="Mulakan Permohonan">Start
                    Application</a>
            </div>
        </div>

        <div class="qis-modal-overlay" id="qisModalConsignment">
            <div class="qis-modal">
                <button type="button" class="qis-modal-close" data-close-modal>&times;</button>
                <span class="qis-modal-tag" data-en="APPLICATION TYPE 03" data-bm="JENIS PERMOHONAN 03">APPLICATION TYPE
                    03</span>
                <div class="qis-icon-wrap"><i class='bx bx-file'></i></div>
                <h4 data-en="Consignment Certificate" data-bm="Sijil Consignment">Consignment Certificate</h4>
                <p data-en="Export authorization dedicated to the movement of agricultural goods to Brunei, confirming the consignment meets cross-border requirements."
                    data-bm="Kebenaran eksport khusus untuk pergerakan barangan pertanian ke Brunei, mengesahkan penghantaran memenuhi keperluan rentas sempadan.">
                    Export authorization dedicated to the movement of agricultural goods to Brunei, confirming the
                    consignment meets cross-border requirements.
                </p>
                <div class="qis-modal-steps">
                    <div class="qis-modal-step">
                        <div class="qis-step-no">01</div>
                        <div data-en="Submit application" data-bm="Hantar Permohonan">Submit application</div>
                    </div>
                    <div class="qis-modal-step">
                        <div class="qis-step-no">02</div>
                        <div data-en="Officer review" data-bm="Semakan Pegawai">Officer review</div>
                    </div>
                    <div class="qis-modal-step">
                        <div class="qis-step-no">03</div>
                        <div data-en="Pay via SabahPay" data-bm="Bayar melalui SabahPay">Pay via SabahPay</div>
                    </div>
                    <div class="qis-modal-step">
                        <div class="qis-step-no">04</div>
                        <div data-en="Download certificate" data-bm="Muat Turun Sijil">Download certificate</div>
                    </div>
                </div>
                <a href="/login" class="qis-btn-primary" data-en="Start Application" data-bm="Mulakan Permohonan">Start
                    Application</a>
            </div>
        </div>

        {{-- =============================== ANNOUNCEMENT MODAL =============================== --}}
        <div class="qis-modal-overlay" id="qisModalAnnouncement">
            <div class="qis-modal">
                <button type="button" class="qis-modal-close" data-close-modal>&times;</button>
                <span class="qis-modal-tag" data-en="ANNOUNCEMENT" data-bm="PENGUMUMAN">ANNOUNCEMENT</span>
                <div class="qis-icon-wrap"><i class='bx bx-bell'></i></div>
                <h4 class="js-am-title" data-en="" data-bm=""></h4>

                <div class="qis-modal-meta">
                    <div class="qis-modal-meta-row">
                        <i class='bx bx-calendar-check'></i>
                        <span><b data-en="Released" data-bm="Dikeluarkan">Released</b>: <span
                                class="js-am-released-at"></span></span>
                    </div>
                    <div class="qis-modal-meta-row">
                        <i class='bx bx-user-circle'></i>
                        <span><b data-en="By" data-bm="Oleh">By</b>: <span class="js-am-released-by"></span></span>
                    </div>
                    <div class="qis-modal-meta-row">
                        <i class='bx bx-time-five'></i>
                        <span><b data-en="Valid Until" data-bm="Sah Sehingga">Valid Until</b>: <span class="js-am-expiry"
                                data-en="No expiry" data-bm="Tiada tamat tempoh">No expiry</span></span>
                    </div>
                </div>

                <p class="js-am-body" data-en="" data-bm=""></p>
            </div>
        </div>

        {{-- =============================== IMAGE MODAL =============================== --}}
        <div class="qis-modal-overlay" id="qisModalImage">
            <div class="qis-modal qis-modal--image">
                <button type="button" class="qis-modal-close" data-close-modal>&times;</button>
                <div class="js-image-slot">
                    <div class="qis-modal-image-placeholder">
                        <i class='bx bx-image-alt'></i>
                        <span data-en="Photo coming soon" data-bm="Gambar akan datang tidak lama lagi">Photo coming
                            soon</span>
                    </div>
                </div>
                <figcaption class="js-image-caption" data-en="" data-bm=""></figcaption>
            </div>
        </div>

    </div>

    {{-- =============================== JAVASCRIPT =============================== --}}
    <script>
        (function() {
            var currentLang = 'en';

            // ---------- language toggle ----------
            var langButtons = document.querySelectorAll('.qis-lang-btn');

            function applyLang(lang) {
                currentLang = lang;
                langButtons.forEach(function(btn) {
                    btn.classList.toggle('active', btn.dataset.lang === lang);
                });
                document.querySelectorAll('[data-en]').forEach(function(el) {
                    var val = el.dataset[lang];
                    if (val === undefined || val === '') return;
                    if (el.hasAttribute('data-i18n-attr')) {
                        el.setAttribute(el.getAttribute('data-i18n-attr'), val);
                    } else {
                        el.innerHTML = val;
                    }
                });
            }

            langButtons.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    applyLang(btn.dataset.lang);
                });
            });

            // ---------- checkpoint terminal rotator ----------
            // ---------- checkpoint terminal rotator (dynamic) ----------
            var terminal = document.getElementById('qisTerminal');
            var checkpoints = [];
            // Collect checkpoint names from the radar nodes
            document.querySelectorAll('.qis-node').forEach(function(node) {
                var label = node.querySelector('span:last-child');
                if (label) {
                    // Use the entry name from the title attribute (full entry point name) or fallback to the district name
                    var name = node.getAttribute('title') || label.textContent.trim();
                    // Remove "District " prefix if present (if using district names)
                    name = name.replace(/^District\s+/, '');
                    if (name) checkpoints.push(name);
                }
            });
            // Fallback if no nodes found (should not happen if data exists)
            if (checkpoints.length === 0) {
                checkpoints = ['KK PORT', 'SEPANGGAR', 'SANDAKAN', 'TAWAU', 'LABUAN'];
            }
            var idx = 0;
            if (terminal && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                setInterval(function() {
                    idx = (idx + 1) % checkpoints.length;
                    terminal.textContent = 'SCANNING CHECKPOINT: ' + checkpoints[idx] + '\u2026';
                    setTimeout(function() {
                        terminal.textContent = 'CHECKPOINT ' + checkpoints[idx] + ': CLEARED';
                    }, 1400);
                }, 3600);
            }

            // ---------- service modals ----------
            document.querySelectorAll(
                '[data-modal="qisModalImport"], [data-modal="qisModalInspection"], [data-modal="qisModalConsignment"]'
            ).forEach(function(card) {
                card.addEventListener('click', function() {
                    var modal = document.getElementById(card.dataset.modal);
                    if (modal) modal.classList.add('qis-open');
                });
            });

            // ---------- announcement modal ----------
            function setBilingual(el, en, bm) {
                if (!el) return;
                el.setAttribute('data-en', en || '');
                el.setAttribute('data-bm', bm || '');
            }

            document.querySelectorAll('.qis-announcement-card').forEach(function(card) {
                card.addEventListener('click', function() {
                    var payload = card.querySelector('.js-announcement-payload');
                    var modal = document.getElementById('qisModalAnnouncement');
                    if (!payload || !modal) return;

                    setBilingual(modal.querySelector('.js-am-title'), payload.dataset.titleEn, payload
                        .dataset.titleBm);
                    setBilingual(modal.querySelector('.js-am-body'), payload.dataset.bodyEn, payload
                        .dataset.bodyBm);

                    modal.querySelector('.js-am-released-at').textContent = payload.dataset
                        .releasedAt || '';
                    modal.querySelector('.js-am-released-by').textContent = payload.dataset
                        .releasedBy || '';

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

            // ---------- image modal (with improved error handling) ----------
            document.querySelectorAll('.qis-gallery-tile[data-modal="qisModalImage"]').forEach(function(tile) {
                tile.addEventListener('click', function() {
                    var modal = document.getElementById('qisModalImage');
                    if (!modal) return;

                    var slot = modal.querySelector('.js-image-slot');
                    var src = tile.dataset.imageSrc;
                    var captionEl = modal.querySelector('.js-image-caption');

                    // Build the image element
                    if (src) {
                        var img = new Image();
                        img.src = src;
                        img.alt = tile.dataset.captionEn || 'Gallery image';
                        img.style.width = '100%';
                        img.style.borderRadius = '14px';
                        img.style.display = 'block';

                        // Show a loading state? we can just insert.
                        slot.innerHTML = '';
                        slot.appendChild(img);

                        // Optional: handle load error
                        img.onerror = function() {
                            slot.innerHTML =
                                '<div class="qis-modal-image-placeholder">' +
                                '<i class="bx bx-image-alt"></i>' +
                                '<span data-en="Image not available" data-bm="Gambar tidak tersedia">Image not available</span>' +
                                '</div>';
                        };
                    } else {
                        slot.innerHTML =
                            '<div class="qis-modal-image-placeholder">' +
                            '<i class="bx bx-image-alt"></i>' +
                            '<span data-en="Photo coming soon" data-bm="Gambar akan datang tidak lama lagi">Photo coming soon</span>' +
                            '</div>';
                    }

                    // Set caption
                    setBilingual(captionEl, tile.dataset.captionEn, tile.dataset.captionBm);
                    applyLang(currentLang);
                    modal.classList.add('qis-open');
                });
            });

            // ---------- close handlers ----------
            document.querySelectorAll('[data-close-modal]').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    btn.closest('.qis-modal-overlay').classList.remove('qis-open');
                });
            });

            document.querySelectorAll('.qis-modal-overlay').forEach(function(overlay) {
                overlay.addEventListener('click', function(e) {
                    if (e.target === overlay) overlay.classList.remove('qis-open');
                });
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    document.querySelectorAll('.qis-modal-overlay.qis-open').forEach(function(overlay) {
                        overlay.classList.remove('qis-open');
                    });
                }
            });
        })();
    </script>
@endsection
