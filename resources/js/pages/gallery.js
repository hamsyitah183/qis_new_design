document.addEventListener('DOMContentLoaded', function() {
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

    // ---------- checkpoint terminal rotator (dynamic) ----------
    var terminal = document.getElementById('qisTerminal');
    var checkpoints = [];
    document.querySelectorAll('.qis-node').forEach(function(node) {
        var label = node.querySelector('span:last-child');
        if (label) {
            var name = node.getAttribute('title') || label.textContent.trim();
            name = name.replace(/^District\s+/, '');
            if (name) checkpoints.push(name);
        }
    });
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

    // ---------- image modal (gallery) ----------
    document.querySelectorAll('.qis-gallery-tile[data-modal="qisModalImage"]').forEach(function(tile) {
        tile.addEventListener('click', function(e) {
            e.preventDefault();
            var modal = document.getElementById('qisModalImage');
            if (!modal) return;

            var slideIndex = tile.dataset.slideIndex;
            var myCarouselEl = document.getElementById('galleryCarousel');
            if (myCarouselEl && slideIndex !== undefined) {
                if (typeof bootstrap !== 'undefined') {
                    var carousel = bootstrap.Carousel.getOrCreateInstance(myCarouselEl);
                    carousel.to(parseInt(slideIndex));
                }
            }

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
});
