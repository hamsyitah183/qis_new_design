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
                    <li><a href="/#qis-about" data-en="About" data-bm="Tentang">About</a></li>
                    <li><a href="/#qis-services" data-en="Services" data-bm="Perkhidmatan">Services</a></li>
                    <li><a href="/#qis-announcements" data-en="Announcements" data-bm="Pengumuman">Announcements</a></li>
                    <li><a href="/#qis-gallery" data-en="Gallery" data-bm="Galeri" style="color: var(--q-primary); font-weight: 600;">Gallery</a></li>
                    <li><a href="/#qis-contact" data-en="Contact" data-bm="Hubungi">Contact</a></li>
                </ul>

                <div class="qis-nav-actions">
                    <div class="qis-lang-toggle">
                        <button type="button" class="qis-lang-btn active" data-lang="en">EN</button>
                        <button type="button" class="qis-lang-btn" data-lang="bm">BM</button>
                    </div>
                    @if (Auth::guard('public')->check() || Auth::guard('internal')->check())
                        <a href="/dashboard" class="qis-btn-ghost d-none d-md-inline-flex" data-en="Dashboard"
                            data-bm="Dashboard">Dashboard</a>
                    @else
                        <a href="/login" class="qis-btn-ghost d-none d-md-inline-flex" data-en="Sign In"
                            data-bm="Log Masuk">Sign In</a>
                    @endif
                    <a href="/public/new_application" class="qis-btn-primary" data-en="Apply Now"
                        data-bm="Mohon Sekarang">Apply Now</a>
                </div>
            </div>
        </header>

        {{-- =============================== GALLERY =============================== --}}
        <section class="qis-section" id="qis-gallery" style="padding-top: 80px;">
            <div class="qis-container">
                <div class="qis-section-header text-center mb-5">
                    <span class="qis-eyebrow" data-en="LATEST CAPTURES" data-bm="RAKAMAN TERKINI">LATEST CAPTURES</span>
                    <h1 style="font-size: 52px; font-weight: 800; margin-bottom: 20px; letter-spacing: -1px; color: var(--default-text-color);" data-en="QIS Gallery" data-bm="Galeri QIS">QIS Gallery</h1>
                    <p class="text-muted mt-3" style="max-width: 600px; margin: 0 auto; font-size: 1.1rem; line-height: 1.6;" data-en="Explore our activities, facilities, and the latest happenings at the Plant Biosecurity Division." data-bm="Terokai aktiviti, kemudahan, dan perkembangan terkini di Bahagian Biosekuriti Tumbuhan.">Explore our activities, facilities, and the latest happenings at the Plant Biosecurity Division.</p>
                </div>
                <div class="qis-gallery-grid" style="margin-top: 0;">
                    @forelse($galleries as $gallery)
                        @php $imgUrl = str_starts_with($gallery->path, 'http') ? $gallery->path : asset('storage/' . $gallery->path); @endphp
                        <div class="qis-gallery-tile"
                            style="background-image: url('{{ $imgUrl }}'); background-size: cover; background-position: center;"
                            data-modal="qisModalImage" data-image-src="{{ $imgUrl }}"
                            data-slide-index="{{ $loop->index }}"
                            data-caption-en="{{ $gallery->name }}"
                            data-caption-bm="{{ $gallery->description ?? $gallery->name }}">
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

        {{-- =============================== FOOTER =============================== --}}
        <footer class="qis-footer">
            <div class="qis-container">
                <div class="qis-footer-top">
                    <div class="qis-footer-brand">
                        <img src="{{ asset('images/logo-DOA.png') }}" alt="Logo" style="height:28px">
                        <span>QIS &middot; Jabatan Pertanian Sabah</span>
                    </div>
                    <ul class="qis-footer-links">
                        <li><a href="/#qis-about" data-en="About" data-bm="Tentang">About</a></li>
                        <li><a href="/#qis-services" data-en="Services" data-bm="Perkhidmatan">Services</a></li>
                        <li><a href="/#qis-announcements" data-en="Announcements" data-bm="Pengumuman">Announcements</a>
                        </li>
                        <li><a href="/#qis-gallery" data-en="Gallery" data-bm="Galeri">Gallery</a></li>
                        <li><a href="/#qis-contact" data-en="Contact" data-bm="Hubungi">Contact</a></li>
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

        {{-- =============================== IMAGE MODAL =============================== --}}
        <div class="qis-modal-overlay" id="qisModalImage">
            <div class="qis-modal qis-modal--image">
                <button type="button" class="qis-modal-close" data-close-modal>&times;</button>
                <div class="js-image-slot">
                    <div id="galleryCarousel" class="carousel slide" data-bs-ride="false">
                        <div class="carousel-inner">
                            @forelse($galleries as $gallery)
                                @php $imgUrl = str_starts_with($gallery->path, 'http') ? $gallery->path : asset('storage/' . $gallery->path); @endphp
                                <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                    <!-- Title -->
                                    <div class="mb-3 text-start" style="padding-right: 40px;">
                                        <h4 style="color: #0f172a; font-weight: 700; margin-bottom: 4px; font-size: 1.25rem;" data-en="{{ $gallery->name }}" data-bm="{{ $gallery->name }}">{{ $gallery->name }}</h4>
                                    </div>
                                    
                                    <!-- Image Box -->
                                    <div style="display: flex; justify-content: center; align-items: center; position: relative;">
                                        <img src="{{ $imgUrl }}" style="width: 100%; height: 500px; border: 1px solid #cbd5e1; border-radius: 10px; object-fit: cover; box-shadow: 0 4px 20px rgba(0,0,0,0.08);" alt="{{ $gallery->name }}">
                                    </div>

                                    <!-- Description -->
                                    @if($gallery->description)
                                        <div class="mt-4 text-start">
                                            <p style="color: #475569; font-size: 15px; line-height: 1.6; margin: 0;" data-en="{{ $gallery->description }}" data-bm="{{ $gallery->description }}">{{ $gallery->description }}</p>
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="qis-modal-image-placeholder">
                                    <i class='bx bx-image-alt'></i>
                                    <span data-en="Photo coming soon" data-bm="Gambar akan datang tidak lama lagi">Photo coming soon</span>
                                </div>
                            @endforelse
                        </div>
                        @if($galleries->count() > 1)
                        <button class="carousel-control-prev" type="button" data-bs-target="#galleryCarousel" data-bs-slide="prev" style="width: 10%;">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#galleryCarousel" data-bs-slide="next" style="width: 10%;">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- =============================== JAVASCRIPT =============================== --}}
@endsection
