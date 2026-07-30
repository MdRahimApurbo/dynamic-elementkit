(function() {
    'use strict';

    var activeMenu = null;
    var returnFocus = null;

    function focusable(panel) {
        return Array.prototype.slice.call(panel.querySelectorAll('a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])'));
    }

    function openMenu(menu) {
        if (activeMenu && activeMenu !== menu) {
            closeMenu(activeMenu);
        }
        var trigger = menu.querySelector('.wpb-sidebar-menu-trigger');
        var panel = menu.querySelector('.wpb-sidebar-menu-panel');
        returnFocus = trigger;
        activeMenu = menu;
        menu.classList.add('is-open');
        document.body.classList.add('wpb-sidebar-menu-open');
        trigger.setAttribute('aria-expanded', 'true');
        panel.setAttribute('aria-hidden', 'false');
        window.setTimeout(function() {
            var items = focusable(panel);
            (items[0] || panel).focus();
        }, 250);
    }

    function closeMenu(menu) {
        if (!menu) return;
        var trigger = menu.querySelector('.wpb-sidebar-menu-trigger');
        var panel = menu.querySelector('.wpb-sidebar-menu-panel');
        menu.classList.remove('is-open');
        trigger.setAttribute('aria-expanded', 'false');
        panel.setAttribute('aria-hidden', 'true');
        if (activeMenu === menu) {
            activeMenu = null;
            document.body.classList.remove('wpb-sidebar-menu-open');
            if (returnFocus) returnFocus.focus();
            returnFocus = null;
        }
    }

    document.addEventListener('click', function(event) {
        var trigger = event.target.closest('.wpb-sidebar-menu-trigger');
        if (trigger) {
            var menu = trigger.closest('.wpb-sidebar-menu');
            menu.classList.contains('is-open') ? closeMenu(menu) : openMenu(menu);
            return;
        }
        var close = event.target.closest('.wpb-sidebar-menu-close, .wpb-sidebar-menu-overlay');
        if (close) {
            closeMenu(close.closest('.wpb-sidebar-menu'));
            return;
        }
        var link = event.target.closest('.wpb-sidebar-menu-nav a');
        if (link) {
            var owner = link.closest('.wpb-sidebar-menu');
            if (owner && owner.getAttribute('data-close-on-link') === 'yes') closeMenu(owner);
        }
    });

    document.addEventListener('keydown', function(event) {
        if (!activeMenu) return;
        if (event.key === 'Escape') {
            event.preventDefault();
            closeMenu(activeMenu);
            return;
        }
        if (event.key !== 'Tab') return;
        var panel = activeMenu.querySelector('.wpb-sidebar-menu-panel');
        var items = focusable(panel);
        if (!items.length) return;
        var first = items[0];
        var last = items[items.length - 1];
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault(); last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault(); first.focus();
        }
    });
})();
