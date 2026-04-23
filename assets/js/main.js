/**
 * Minimal UI: mobile nav, sidebar toggle, backdrop, confirm dialogs
 */
(function () {
    'use strict';

    /* ── Public nav toggle ── */
    var navToggle = document.getElementById('navToggle');
    var navMain   = document.getElementById('navMain');
    if (navToggle && navMain) {
        navToggle.addEventListener('click', function () {
            navMain.classList.toggle('open');
        });
        // Close when clicking outside
        document.addEventListener('click', function (e) {
            if (!navToggle.contains(e.target) && !navMain.contains(e.target)) {
                navMain.classList.remove('open');
            }
        });
    }

    /* ── Dashboard sidebar toggle + backdrop ── */
    var sidebarToggle  = document.getElementById('sidebarToggle');
    var sidebar        = document.getElementById('sidebar');
    var backdrop       = document.getElementById('sidebarBackdrop');

    function openSidebar() {
        if (!sidebar) return;
        sidebar.classList.add('open');
        if (backdrop) backdrop.classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        if (!sidebar) return;
        sidebar.classList.remove('open');
        if (backdrop) backdrop.classList.remove('open');
        document.body.style.overflow = '';
    }

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function () {
            if (sidebar.classList.contains('open')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });
    }

    // Close sidebar when tapping the backdrop
    if (backdrop) {
        backdrop.addEventListener('click', closeSidebar);
    }

    // Close sidebar when clicking a nav link on mobile
    if (sidebar) {
        sidebar.querySelectorAll('a').forEach(function (a) {
            a.addEventListener('click', function () {
                if (window.innerWidth <= 900) {
                    closeSidebar();
                }
            });
        });
    }

    // Re-enable scroll if resizing to desktop
    window.addEventListener('resize', function () {
        if (window.innerWidth > 900) {
            closeSidebar();
        }
    });

    /* ── data-confirm helper ── */
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            var msg = el.getAttribute('data-confirm') || 'Are you sure?';
            if (!window.confirm(msg)) {
                e.preventDefault();
            }
        });
    });
})();
