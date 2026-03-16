/**
 * Inactivity Timeout Module
 *
 * Shows a warning modal after WARN_MS of inactivity.
 * Gives the user WARNING_DURATION_S seconds to respond before saving drafts
 * and auto-logging out.
 *
 * Each form page can register a draft-save hook:
 *   window.qisDraftSaver = async () => { ... }
 *
 * To adjust timeouts during testing, change:
 *   TIMEOUT_MS  - idle time before the warning appears (default: 29 minutes)
 *   WARNING_DURATION_S - countdown seconds in the warning modal (default: 60)
 */

const TIMEOUT_MS = 29 * 60 * 1000;       // 29 minutes
const WARNING_DURATION_S = 60;            // 60-second countdown
const LOGOUT_URL = '/logout';

let idleTimer = null;
let countdownTimer = null;
let remainingSeconds = WARNING_DURATION_S;
let modalInstance = null;

const ACTIVITY_EVENTS = ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'];

// ─── DOM IDs ───────────────────────────────────────────────────────────────
const MODAL_ID = 'qisInactivityModal';
const COUNTDOWN_EL_ID = 'qisCountdownDisplay';
const STAY_BTN_ID = 'qisStayLoggedInBtn';
const LOGOUT_BTN_ID = 'qisLogoutNowBtn';
const SAVING_MSG_ID = 'qisSavingMsg';

// ─── Helpers ────────────────────────────────────────────────────────────────

function getModal() {
    const el = document.getElementById(MODAL_ID);
    if (!el) return null;
    if (!modalInstance) {
        modalInstance = new bootstrap.Modal(el, { backdrop: 'static', keyboard: false });
    }
    return modalInstance;
}

function showWarningModal() {
    const m = getModal();
    if (!m) return;

    remainingSeconds = WARNING_DURATION_S;
    updateCountdown();

    // Hide saving message
    const savingMsg = document.getElementById(SAVING_MSG_ID);
    if (savingMsg) savingMsg.classList.add('d-none');

    m.show();
    startCountdown();
}

function hideWarningModal() {
    const m = getModal();
    if (m) m.hide();
}

function updateCountdown() {
    const el = document.getElementById(COUNTDOWN_EL_ID);
    if (el) el.textContent = remainingSeconds;
}

function startCountdown() {
    clearInterval(countdownTimer);
    countdownTimer = setInterval(() => {
        remainingSeconds -= 1;
        updateCountdown();

        if (remainingSeconds <= 0) {
            clearInterval(countdownTimer);
            triggerAutoLogout();
        }
    }, 1000);
}

function stopCountdown() {
    clearInterval(countdownTimer);
    countdownTimer = null;
}

// ─── Idle Timer ─────────────────────────────────────────────────────────────

function resetIdleTimer() {
    clearTimeout(idleTimer);
    idleTimer = setTimeout(showWarningModal, TIMEOUT_MS);
}

function onActivity() {
    // Only reset if the modal is not currently open
    const modalEl = document.getElementById(MODAL_ID);
    if (modalEl && modalEl.classList.contains('show')) return;
    resetIdleTimer();
}

// ─── Draft Save & Logout ────────────────────────────────────────────────────

async function saveDraftIfAvailable() {
    if (typeof window.qisDraftSaver === 'function') {
        try {
            const savingMsg = document.getElementById(SAVING_MSG_ID);
            if (savingMsg) savingMsg.classList.remove('d-none');
            await window.qisDraftSaver();
        } catch (err) {
            console.warn('[QIS] Draft save failed (non-blocking):', err);
        }
    }
}

async function triggerAutoLogout() {
    await saveDraftIfAvailable();
    window.location.href = LOGOUT_URL;
}

// ─── Public API ─────────────────────────────────────────────────────────────

export function initInactivityTimeout() {
    // Attach activity listeners
    ACTIVITY_EVENTS.forEach(event => {
        document.addEventListener(event, onActivity, { passive: true });
    });

    // Wire up "Stay Logged In" button
    document.addEventListener('click', (e) => {
        if (e.target && e.target.id === STAY_BTN_ID) {
            stopCountdown();
            hideWarningModal();
            resetIdleTimer();
        }
        if (e.target && e.target.id === LOGOUT_BTN_ID) {
            stopCountdown();
            triggerAutoLogout();
        }
    });

    // Start the idle timer on init
    resetIdleTimer();

    console.log('[QIS] Inactivity timeout initialized (idle threshold:', TIMEOUT_MS / 60000, 'min)');
}
