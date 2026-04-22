/**
 * Minimal UI: mobile nav, sidebar toggle, confirm dialogs
 */
(function () {
    'use strict';

    var navToggle = document.getElementById('navToggle');
    var navMain = document.getElementById('navMain');
    if (navToggle && navMain) {
        navToggle.addEventListener('click', function () {
            navMain.classList.toggle('open');
        });
    }

    var sidebarToggle = document.getElementById('sidebarToggle');
    var sidebar = document.getElementById('sidebar');
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function () {
            sidebar.classList.toggle('open');
        });
    }

    // Close sidebar when clicking a link (mobile)
    if (sidebar) {
        sidebar.querySelectorAll('a').forEach(function (a) {
            a.addEventListener('click', function () {
                if (window.innerWidth <= 900) {
                    sidebar.classList.remove('open');
                }
            });
        });
    }

    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            var msg = el.getAttribute('data-confirm') || 'Are you sure?';
            if (!window.confirm(msg)) {
                e.preventDefault();
            }
        });
    });
})();
