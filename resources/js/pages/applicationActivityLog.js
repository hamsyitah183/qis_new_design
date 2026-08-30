// ---------------------------------------------------------------
// Render: Activity tab + Application Log modal
// ---------------------------------------------------------------
function getLang() {
    try {
        return localStorage.getItem("qis_lang") || "en";
    } catch {
        return "en";
    }
}

export function escapeHtml(value) {
    return String(value ?? "").replace(
        /[&<>"']/g,
        (c) =>
            ({
                "&": "&amp;",
                "<": "&lt;",
                ">": "&gt;",
                '"': "&quot;",
                "'": "&#39;",
            })[c],
    );
}



export function renderActivityTimeline(stageconfig, activity_log) {
    const el = document.getElementById("ipvActivityTimeline");
    if (!activity_log.length) {
        el.innerHTML =
            '<div class="ipv-empty-state"><i class="bi bi-clock-history"></i><p>No activity recorded yet.</p></div>';
        return;
    }
    const lang = getLang();
    el.innerHTML = activity_log.map((entry) => {
        const cfg = stageconfig[entry.stage] || stageconfig.email;
        const title = cfg[lang] || cfg.en;
        return `
            <div class="ipv-timeline-item">
                <div class="ipv-timeline-icon is-${cfg.color}"><i class="bi ${cfg.icon}"></i></div>
                <div class="ipv-timeline-body">
                    <div>
                        <div class="ipv-timeline-title">${escapeHtml(title)}</div>
                        <p class="ipv-timeline-desc">${escapeHtml(entry.description)}</p>

                        <div>
                            <small class = "text-muted">
                                ${entry.causer} (${entry.causer_email})
                            </small>
                        </div>
                    </div>
                    <span class="ipv-timeline-time">${escapeHtml(entry.time)}</span>

                  
                </div>
            </div>
        `;
    }).join("");
}