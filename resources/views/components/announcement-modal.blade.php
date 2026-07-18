{{--
    <x-announcement-modal />

    Reusable announcement detail modal. Fully self-contained: it wires up
    its own click delegation, so any page that renders ".qis-announcement-card"
    buttons with a ".js-announcement-payload" child (see the Announcements
    section on the landing page, or /announcements) will work automatically
    the moment this component is included — no page-specific JS needed.

    Card markup this component expects (already used on the landing page
    and the announcements index page):

        <button type="button" class="qis-announcement-card">
            ...visible card content...
            <span class="d-none js-announcement-payload"
                data-title-en="..." data-title-bm="..."
                data-body-en="..."  data-body-bm="..."
                data-released-at="..." data-released-by="..."
                data-expires-at="..."></span>
        </button>
--}}
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

<script>
    (function () {
        var modal = document.getElementById('qisModalAnnouncement');
        // Guard: if this component somehow gets included twice on one page,
        // don't wire up duplicate listeners.
        if (!modal || modal.dataset.wired === '1') return;
        modal.dataset.wired = '1';

        function getCurrentLang() {
            var activeBtn = document.querySelector('.qis-lang-btn.active, .lang-btn.active');
            return activeBtn ? activeBtn.dataset.lang : 'en';
        }

        function applyLangTo(root, lang) {
            root.querySelectorAll('[data-en]').forEach(function (el) {
                var val = el.getAttribute('data-' + lang);
                if (val === null || val === '') return;
                el.innerHTML = val;
            });
        }

        function setBilingual(el, en, bm) {
            if (!el) return;
            el.setAttribute('data-en', en || '');
            el.setAttribute('data-bm', bm || '');
        }

        function openFromCard(card) {
            var payload = card.querySelector('.js-announcement-payload');
            if (!payload) return;

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

            applyLangTo(modal, getCurrentLang());
            modal.classList.add('qis-open');
        }

        // Delegated on document so it also catches cards rendered after this
        // script runs (e.g. injected later, infinite-scroll, etc.)
        document.addEventListener('click', function (e) {
            var card = e.target.closest('.qis-announcement-card');
            if (card) openFromCard(card);
        });

        // If the page's language toggle is clicked while this modal is open,
        // keep its content in sync too.
        document.addEventListener('click', function (e) {
            var langBtn = e.target.closest('.qis-lang-btn, .lang-btn');
            if (langBtn && modal.classList.contains('qis-open')) {
                applyLangTo(modal, langBtn.dataset.lang);
            }
        });

        modal.querySelector('[data-close-modal]').addEventListener('click', function () {
            modal.classList.remove('qis-open');
        });

        modal.addEventListener('click', function (e) {
            if (e.target === modal) modal.classList.remove('qis-open');
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') modal.classList.remove('qis-open');
        });
    })();
</script>