/* ============================================================
   app.js — Sidebar toggle + billing plan picker
   ============================================================ */

/* ── Sidebar (mobile) ───────────────────────────────────── */
(function () {
    var sidebar  = document.getElementById('sidebar');
    var toggle   = document.getElementById('sidebarToggle');
    var overlay  = document.getElementById('sidebarOverlay');

    if (!sidebar || !toggle) return;

    function openSidebar() {
        sidebar.classList.add('is-open');
        toggle.setAttribute('aria-expanded', 'true');
        if (overlay) {
            overlay.style.display = 'block';
            // Fade in
            overlay.style.opacity = '0';
            requestAnimationFrame(function () {
                overlay.style.transition = 'opacity 180ms ease';
                overlay.style.opacity = '1';
            });
        }
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        sidebar.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
        if (overlay) {
            overlay.style.opacity = '0';
            setTimeout(function () { overlay.style.display = 'none'; }, 200);
        }
        document.body.style.overflow = '';
    }

    toggle.addEventListener('click', function () {
        if (sidebar.classList.contains('is-open')) {
            closeSidebar();
        } else {
            openSidebar();
        }
    });

    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
    }

    // Close on Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && sidebar.classList.contains('is-open')) {
            closeSidebar();
            toggle.focus();
        }
    });

    // Close sidebar on nav-link click (mobile UX)
    var navLinks = sidebar.querySelectorAll('.nav-link');
    navLinks.forEach(function (link) {
        link.addEventListener('click', function () {
            if (window.innerWidth <= 900) closeSidebar();
        });
    });
})();

/* ── Topbar contextual accent ──────────────────────────── */
(function () {
    var root = document.documentElement;
    if (!root) return;

    var activeLink = document.querySelector('.sidebar .nav-link.is-active[data-topbar-accent]');
    var accent = activeLink ? activeLink.getAttribute('data-topbar-accent') : '';

    if (!accent) {
        var path = (window.location.pathname || '').toLowerCase();
        var routeAccents = [
            ['/attendance', 'var(--accent-attendance)'],
            ['/leave', 'var(--accent-leave)'],
            ['/payroll', 'var(--accent-payroll)'],
            ['/settings', 'var(--accent-settings)'],
            ['/employees', 'var(--accent-employees)'],
            ['/billing', 'var(--accent-billing)'],
            ['/dashboard', 'var(--accent-dashboard)']
        ];

        for (var i = 0; i < routeAccents.length; i += 1) {
            var routePrefix = routeAccents[i][0];
            if (path === routePrefix || path.indexOf(routePrefix + '/') === 0) {
                accent = routeAccents[i][1];
                break;
            }
        }
    }

    if (!accent) {
        accent = 'var(--accent-dashboard)';
    }

    root.style.setProperty('--topbar-accent', accent);
})();

/* ── Billing plan picker ────────────────────────────────── */
(function () {
    var planPickers = document.querySelectorAll('[data-plan-picker]');
    if (!planPickers || planPickers.length === 0) return;

    planPickers.forEach(function (picker) {
        var cards = picker.querySelectorAll('[data-plan-card]');
        if (!cards || cards.length === 0) return;

        var liveRegion  = picker.querySelector('.bill-live-region, .mk-live-region');
        var isSubmitting = false;

        var setSelectedState = function (announce) {
            var selectedPlanName = '';
            cards.forEach(function (card) {
                var radio    = card.querySelector('input[type="radio"][name="plan_id"]');
                var selected = !!radio && radio.checked;
                card.classList.toggle('is-selected', selected);
                card.setAttribute('data-selected', selected ? 'true' : 'false');
                if (selected) selectedPlanName = card.getAttribute('data-plan-name') || '';
            });
            if (announce && liveRegion) {
                liveRegion.textContent = selectedPlanName
                    ? selectedPlanName + ' selected.'
                    : 'No plan selected.';
            }
        };

        cards.forEach(function (card) {
            var radio = card.querySelector('input[type="radio"][name="plan_id"]');
            if (!radio) return;

            card.setAttribute('tabindex', '0');

            card.addEventListener('click', function () {
                if (!radio.checked) radio.checked = true;
                setSelectedState(true);
            });

            card.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    radio.checked = true;
                    setSelectedState(true);
                }
            });

            radio.addEventListener('change', function () { setSelectedState(true); });
        });

        picker.addEventListener('submit', function (e) {
            if (isSubmitting) { e.preventDefault(); return; }

            var selected = picker.querySelector('input[type="radio"][name="plan_id"]:checked');
            if (!selected) {
                var firstRadio = picker.querySelector('input[type="radio"][name="plan_id"]');
                if (firstRadio) { firstRadio.checked = true; setSelectedState(true); selected = firstRadio; }
            }
            if (!selected) return;

            var btn = picker.querySelector('button[type="submit"]');
            if (!btn) return;
            isSubmitting = true;
            btn.disabled = true;
            btn.textContent = btn.getAttribute('data-loading-label') || 'Processing…';
        });

        setSelectedState(false);
    });
})();
