import $ from "jquery";
import select2 from "select2";
import "select2/dist/css/select2.min.css";

let initError = null;

const jq = window.jQuery || $;
try {
    select2(jq);
} catch (e) {
    initError = e.message;
}

/**
 * Initialise Select2 on a selector.
 * Guard: if already initialised, skip (idempotent).
 *
 * @param {string|Element|jQuery} selector
 * @param {string} placeholder
 * @param {object} options  - extra select2 options (merged)
 */
export function setupSelect2(selector, placeholder = "", options = {}) {
    const $el = jq(selector);
    if (!$el.length) return;
    if ($el.data("select2")) return;

    $el.data("placeholder", placeholder);
    try {
        if (typeof $el.select2 !== 'function') {
            console.error("$.fn.select2 is not a function. Init error:", initError);
            return;
        }

        $el.select2({
            placeholder,
            allowClear: true,
            multiple: true,
            width: "100%",
            // Attach to body by default so it's not clipped by .filter-dropdown bounds
            ...options,
        });

        // Prevent Bootstrap dropdown from closing when interacting with Select2
        if (!window._select2PreventInit) {
            document.addEventListener("hide.bs.dropdown", function (e) {
                if (e.clickEvent && e.clickEvent.target.closest(".select2-container")) {
                    e.preventDefault();
                }
            });
            window._select2PreventInit = true;
        }
    } catch (e) {
        console.error("Select2 Error:", e.message);
    }
}

/**
 * Destroy Select2 on a selector. Silent on failure.
 *
 * @param {string|Element|jQuery} selector
 */
export function destroySelect2(selector) {
    try {
        jq(selector).select2("destroy");
    } catch (e) {
        // ignore — element may not exist or may not have been initialised
    }
}

/**
 * Auto-initialise all .select2 selects inside .filter-dropdown containers.
 * Uses the first option's text as the placeholder.
 * Safe to call multiple times — the guard inside setupSelect2 prevents double init.
 */
export function autoInitFilterSelect2() {
    try {
        const selects = jq(".filter-dropdown select.select2");
        selects.each(function () {
            let placeholder = jq(this).data("placeholder");
            if (!placeholder) {
                // Try to find the label for this select to use as placeholder
                const label = jq(this).closest("li, div").find("label").text();
                placeholder = label ? `Select ${label}` : "Select...";
            }
            setupSelect2(this, placeholder);
            // Clear default selection so it doesn't auto-select the first option in multi-select mode
            jq(this).val(null).trigger("change");
        });
    } catch (e) {
        console.error("Select2 Auto Init Error:", e.message);
    }
}
