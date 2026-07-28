;(function() {
    'use strict';

    function initProductMedia() {
        document.querySelectorAll('.wpb-product-media').forEach(function(widget) {
            var viewport = widget.querySelector('.wpb-product-media__viewport');
            var container = widget.querySelector('.wpb-product-media__container');
            var thumbsList = widget.querySelector('.wpb-product-media__thumbs-list');
            var thumbs = widget.querySelectorAll('.wpb-product-media__thumb');
            if (!viewport || !container) return;

            var slides = container.querySelectorAll('.wpb-product-media__slide');
            if (!slides.length) return;

            var current = 0;
            var mainDrag = { active: false, startX: 0, startTranslate: 0 };
            var thumbsDrag = { active: false, startX: 0, scrollStart: 0, target: null };

            function getSlideWidth() {
                if (container.clientWidth > 0) return container.clientWidth;
                if (slides[0]) {
                    var rect = slides[0].getBoundingClientRect();
                    if (rect.width > 0) return rect.width;
                }
                return viewport.clientWidth || viewport.offsetWidth || 0;
            }

            function goTo(index, animate) {
                if (index < 0 || index >= slides.length) return;
                current = index;
                var width = getSlideWidth();
                if (!width) {
                    requestAnimationFrame(function() {
                        goTo(index, animate);
                    });
                    return;
                }
                var translate = -current * width;
                container.style.transition = animate ? 'transform 0.3s ease' : 'none';
                container.style.transform = 'translate3d(' + translate + 'px, 0px, 0px)';
                Array.prototype.forEach.call(thumbs, function(t) {
                    var idx = parseInt(t.getAttribute('data-index') || '0', 10);
                    t.classList.toggle('wpb-product-media__thumb--active', idx === current);
                });
                if (thumbsList) {
                    var targetThumb = null;
                    Array.prototype.forEach.call(thumbs, function(t) {
                        if (parseInt(t.getAttribute('data-index') || '0', 10) === current) {
                            targetThumb = t;
                        }
                    });
                    if (targetThumb) {
                        targetThumb.scrollIntoView({ behavior: animate ? 'smooth' : 'auto', block: 'nearest', inline: 'center' });
                    }
                }
            }

            var initialIndex = 0;
            var firstThumb = thumbs[0];
            if (firstThumb) {
                initialIndex = parseInt(firstThumb.getAttribute('data-index') || '0', 10);
            }
            goTo(initialIndex, false);

            viewport.addEventListener('pointerdown', function(e) {
                if (e.target.closest('.wpb-product-media__thumb')) return;
                mainDrag.active = true;
                mainDrag.startX = e.clientX || (e.touches && e.touches[0].clientX) || 0;
                mainDrag.startTranslate = -current * getSlideWidth();
                container.style.transition = 'none';
            });
            viewport.addEventListener('pointermove', function(e) {
                if (!mainDrag.active) return;
                var clientX = e.clientX || (e.touches && e.touches[0].clientX) || 0;
                container.style.transform = 'translate3d(' + (mainDrag.startTranslate + (clientX - mainDrag.startX)) + 'px, 0px, 0px)';
            });
            viewport.addEventListener('pointerup', function(e) {
                if (!mainDrag.active) return;
                mainDrag.active = false;
                var width = getSlideWidth();
                if (width) {
                    var moved = -current * width - mainDrag.startTranslate;
                    if (Math.abs(moved) > width * 0.15) {
                        goTo(moved > 0 ? current - 1 : current + 1, true);
                    } else {
                        goTo(current, true);
                    }
                }
            });
            viewport.addEventListener('pointercancel', function() {
                if (!mainDrag.active) return;
                mainDrag.active = false;
                goTo(current, true);
            });

            if (thumbsList) {
                thumbsList.style.cursor = 'grab';
                thumbsList.addEventListener('pointerdown', function(e) {
                    var thumb = e.target.closest('.wpb-product-media__thumb');
                    if (!thumb) return;
                    thumbsDrag.active = true;
                    thumbsDrag.startX = e.clientX;
                    thumbsDrag.scrollStart = thumbsList.scrollLeft;
                    thumbsDrag.target = thumb;
                    thumbsList.style.cursor = 'grabbing';
                });
                thumbsList.addEventListener('pointermove', function(e) {
                    if (!thumbsDrag.active) return;
                    var dx = e.clientX - thumbsDrag.startX;
                    if (Math.abs(dx) > 4) {
                        thumbsList.scrollLeft = thumbsDrag.scrollStart - dx;
                    }
                });
                thumbsList.addEventListener('pointerup', function(e) {
                    if (!thumbsDrag.active) return;
                    var moved = Math.abs((e.clientX || 0) - thumbsDrag.startX);
                    if (moved <= 4 && thumbsDrag.target) {
                        var index = parseInt(thumbsDrag.target.getAttribute('data-index') || '0', 10);
                        goTo(index, true);
                    }
                    thumbsDrag.active = false;
                    thumbsDrag.target = null;
                    thumbsList.style.cursor = 'grab';
                });
                thumbsList.addEventListener('pointercancel', function() {
                    thumbsDrag.active = false;
                    thumbsDrag.target = null;
                    thumbsList.style.cursor = 'grab';
                });
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initProductMedia);
    } else {
        initProductMedia();
    }

    if (window.jQuery) {
        jQuery(document).on('wpb_content_updated', initProductMedia);
    }
})();