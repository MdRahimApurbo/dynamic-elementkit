(function() {
    'use strict';

    var activeNavigation = null;
    var returnFocus = null;

    function getFocusable(drawer) {
        return Array.prototype.slice.call(
            drawer.querySelectorAll('a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])')
        ).filter(function(element) {
            return element.offsetParent !== null;
        });
    }

    function openNavigation(navigation) {
        if (!navigation) return;
        if (activeNavigation && activeNavigation !== navigation) {
            closeNavigation(activeNavigation, false);
        }

        var toggle = navigation.querySelector('.dek-navigation-toggle');
        var drawer = navigation.querySelector('.dek-navigation-drawer');
        if (!toggle || !drawer) return;

        activeNavigation = navigation;
        returnFocus = toggle;
        navigation.classList.add('is-open');
        document.body.classList.add('dek-navigation-open');
        toggle.setAttribute('aria-expanded', 'true');
        drawer.setAttribute('aria-hidden', 'false');

        window.setTimeout(function() {
            var focusable = getFocusable(drawer);
            (focusable[0] || drawer).focus();
        }, 220);
    }

    function closeNavigation(navigation, restoreFocus) {
        if (!navigation) return;
        var toggle = navigation.querySelector('.dek-navigation-toggle');
        var drawer = navigation.querySelector('.dek-navigation-drawer');

        navigation.classList.remove('is-open');
        if (toggle) toggle.setAttribute('aria-expanded', 'false');
        if (drawer) drawer.setAttribute('aria-hidden', 'true');

        if (activeNavigation === navigation) {
            activeNavigation = null;
            document.body.classList.remove('dek-navigation-open');
            if (restoreFocus !== false && returnFocus) returnFocus.focus();
            returnFocus = null;
        }
    }

    function isMobileMode(navigation) {
        var mobile = navigation.querySelector('.dek-navigation-mobile');
        return mobile && window.getComputedStyle(mobile).display !== 'none';
    }

    document.addEventListener('click', function(event) {
        var toggle = event.target.closest('.dek-navigation-toggle');
        if (toggle) {
            var navigation = toggle.closest('.dek-navigation');
            navigation.classList.contains('is-open') ? closeNavigation(navigation, true) : openNavigation(navigation);
            return;
        }

        var closeControl = event.target.closest('.dek-navigation-close, .dek-navigation-overlay');
        if (closeControl) {
            closeNavigation(closeControl.closest('.dek-navigation'), true);
            return;
        }

        var drawerLink = event.target.closest('.dek-navigation-drawer a');
        if (drawerLink) {
            var owner = drawerLink.closest('.dek-navigation');
            if (owner && owner.getAttribute('data-close-on-link') === 'yes') {
                closeNavigation(owner, false);
            }
        }
    });

    document.addEventListener('keydown', function(event) {
        if (!activeNavigation) return;

        if (event.key === 'Escape') {
            event.preventDefault();
            closeNavigation(activeNavigation, true);
            return;
        }

        if (event.key !== 'Tab') return;
        var drawer = activeNavigation.querySelector('.dek-navigation-drawer');
        var focusable = drawer ? getFocusable(drawer) : [];
        if (!focusable.length) return;

        var first = focusable[0];
        var last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });

    window.addEventListener('resize', function() {
        if (activeNavigation && !isMobileMode(activeNavigation)) {
            closeNavigation(activeNavigation, false);
        }
    });
})();
