/* Minimal local Embla-compatible shim for product media */
;(function() {
    'use strict';

    if (typeof window.emblaCarousel !== 'undefined') return;

    function emblaCarousel(viewport, options) {
        options = options || {};
        var container = viewport.querySelector('.embla__container') || viewport.children[0];
        if (!container) return noopApi();
        var slides = Array.prototype.slice.call(container.children || []);
        slides = slides.filter(function(node) { return node.classList && node.classList.contains('embla__slide'); });
        if (!slides.length) return noopApi();

        var index = 0;
        var handlers = {};
        var drag = { active: false, startX: 0, startScroll: 0 };

        function snapClass() { return options.loop ? 'embla__snap-loop' : 'embla__snap'; }

        function offset() {
            if (!slides.length) return 0;
            if (index < 0) index = 0;
            if (index >= slides.length) index = slides.length - 1;
            return -index * viewport.clientWidth;
        }

        function applyTransform() {
            container.style.transform = 'translate3d(' + offset() + 'px, 0px, 0px)';
            emit('select');
        }

        function emit(name) {
            (handlers[name] || []).forEach(function(fn) { fn(); });
        }

        function toIndex(i) {
            index = i;
            applyTransform();
        }

        viewport.style.overflow = 'hidden';
        viewport.style.touchAction = 'pan-y pinch-zoom';
        container.style.display = 'flex';
        container.style.willChange = 'transform';
        container.style.userSelect = 'none';
        container.style.width = (slides.length * 100) + '%';
        slides.forEach(function(slide) {
            slide.style.flex = '0 0 100%';
            slide.style.minWidth = '0';
        });
        applyTransform();

        viewport.addEventListener('pointerdown', function(e) {
            if (options.dragFree === false) return;
            drag.active = true;
            drag.startX = e.clientX;
            drag.startScroll = offset();
            viewport.setPointerCapture(e.pointerId);
        });
        viewport.addEventListener('pointermove', function(e) {
            if (!drag.active) return;
            var diff = e.clientX - drag.startX;
            var next = drag.startScroll + diff;
            container.style.transform = 'translate3d(' + next + 'px, 0px, 0px)';
        });
        viewport.addEventListener('pointerup', function(e) {
            if (!drag.active) return;
            drag.active = false;
            var diff = e.clientX - drag.startX;
            var move = Math.abs(diff);
            var slideWidth = viewport.clientWidth;
            var step = Math.round(Math.abs(diff) / slideWidth) || 1;
            if (diff > 40 && index > 0) index -= step;
            else if (diff < -40 && index < slides.length - 1) index += step;
            else index = Math.round(-drag.startScroll / slideWidth);
            applyTransform();
        });

        return {
            selectedScrollSnap: function() { return index; },
            scrollSnaps: Array.from({length: slides.length}, function(_, i) { return i; }),
            on: function(name, fn) {
                if (!handlers[name]) handlers[name] = [];
                handlers[name].push(fn);
                return this;
            },
            scrollTo: function(i, animate) {
                toIndex(i);
            },
            canScrollNext: function() { return index < slides.length - 1; },
            canScrollPrev: function() { return index > 0; },
            slides: slides,
            container: container
        };
    }

    function noopApi() {
        return {
            selectedScrollSnap: function() { return 0; },
            scrollTo: function() {},
            on: function() { return this; }
        };
    }

    window.emblaCarousel = emblaCarousel;
})();