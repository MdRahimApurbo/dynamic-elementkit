jQuery(document).ready(function($) {
    $(document).on('click', '.wpb-add-to-cart', function(e) {
        var $button = $(this);
        if ($button.prop('tagName').toLowerCase() !== 'button') {
            return;
        }
        e.preventDefault();
        var productId = $button.data('product-id');
        var $grid = $button.closest('.wpb-product-grid');
        var ajaxEnabled = $grid.attr('data-ajax') === '1';

        if (!ajaxEnabled) {
            return;
        }

        $button.addClass('loading');

        $.post(dekAdmin.ajaxUrl, {
            action: 'dek_add_to_cart',
            product_id: productId,
            _wpnonce: dekAdmin.toggleNonce
        })
        .done(function(response) {
            $button.removeClass('loading');
var addedText = $grid.data('addedButtonText') || dekAdmin.addedText;
        if (response.success && response.data.cart_contents_updated) {
            $button.find('.wpb-button-text').text(addedText);
            $button.addClass('added');
            setTimeout(function() {
                $button.find('.wpb-button-text').text(dekAdmin.addToCartText);
                $button.removeClass('added');
            }, 2000);
                if (typeof wpbUpdateCart === 'function') {
                    wpbUpdateCart();
                }
            }
        })
        .fail(function() {
            $button.removeClass('loading');
        });
    });

    $(document).on('click', '.wpb-filter-button', function(e) {
        e.preventDefault();
        var $button = $(this);
        var $filterContainer = $button.closest('.wpb-category-filter');
        var filterMode = $filterContainer.attr('data-filter-mode') || 'single';

        if (filterMode === 'multiple') {
            return;
        }

        if ($button.hasClass('wpb-loading') || $filterContainer.hasClass('wpb-loading')) {
            return;
        }

        var categoryId = $button.data('category-id');
        var categorySlug = $button.data('category-slug') || '';
        var connectId = $filterContainer.attr('data-connect-id') || '';
        var $grid = connectId ? $('.wpb-product-grid[data-connect-id="' + connectId + '"]') : $();
        var $productsContainer = connectId ? $('#wpb-grid-' + connectId) : $();

        if (!$productsContainer.length && $grid.length) {
            $productsContainer = $grid.find('.wpb-products').first();
        }

        if (!$productsContainer.length) {
            $productsContainer = $filterContainer.nextAll('.wpb-products').first();
        }

        if (!$productsContainer.length) {
            $productsContainer = $filterContainer.prevAll('.wpb-products').first();
        }

        if (!$productsContainer.length) {
            $productsContainer = $filterContainer.closest('.wpb-product-grid').find('.wpb-products').first();
        }

        if (!$productsContainer.length) {
            $productsContainer = $('.wpb-products').first();
        }

        if (!$productsContainer.length) {
            return;
        }

        var targetId = connectId || ($productsContainer.attr('id') ? $productsContainer.attr('id').replace(/^wpb-grid-/, '') : 'wpb-products');

        if ($button.hasClass('active') && categoryId === 0) {
            $filterContainer.find('.wpb-filter-button').removeClass('active');
            $filterContainer.find('.wpb-filter-button').first().addClass('active');
            categoryId = 0;
            categorySlug = '';
        } else if ($button.hasClass('active') && categoryId !== 0) {
            $filterContainer.find('.wpb-filter-button').removeClass('active');
            $filterContainer.find('.wpb-filter-button').first().addClass('active');
            categoryId = 0;
            categorySlug = '';
        } else {
            $filterContainer.find('.wpb-filter-button').removeClass('active');
            $button.addClass('active');
        }

        $filterContainer.addClass('wpb-loading');
        $productsContainer.addClass('wpb-loading');

        var settings = {};
        var $widgetWrapper = $productsContainer.closest('.elementor-widget-wpb-product-grid');
        if ($widgetWrapper.length) {
            var elementorSettings = $widgetWrapper.data('settings');
            if (typeof elementorSettings !== 'undefined') {
                settings = elementorSettings;
            }
        }

        var currentDesktop = $productsContainer[0].style.getPropertyValue('--wpb-cols-desktop');
        var currentTablet = $productsContainer[0].style.getPropertyValue('--wpb-cols-tablet');
        var currentMobile = $productsContainer[0].style.getPropertyValue('--wpb-cols-mobile');
        if (currentDesktop) settings.columns = parseInt(currentDesktop);
        if (currentTablet) settings.columns_tablet = parseInt(currentTablet);
        if (currentMobile) settings.columns_mobile = parseInt(currentMobile);

        if (typeof wpbShowSkeletons === 'function') {
            var skeletonCols = settings.columns || 4;
            wpbShowSkeletons($productsContainer, skeletonCols);
        }

        var self = this;
        $.post(dekAdmin.ajaxUrl, {
            action: 'dek_filter_products',
            _wpnonce: dekAdmin.toggleNonce,
            connect_id: connectId,
            target_id: targetId,
            category_id: categoryId,
            category_slug: categorySlug,
            category_ids: '',
            posts_per_page: settings.posts_per_page || 12,
            order_by: settings.order_by || 'date',
            order: settings.order || 'desc',
            show_title: settings.show_title || 'yes',
            show_price: settings.show_price || 'yes',
            show_add_to_cart: settings.show_add_to_cart || 'yes',
            ajax_add_to_cart: settings.ajax_add_to_cart || 'yes',
            columns: settings.columns || 4,
            tablet_columns: settings.columns_tablet || Math.max(2, Math.floor((settings.columns || 4) / 2)),
            mobile_columns: settings.columns_mobile || 1,
            show_badge: settings.show_badge || 'no',
            badge_text: settings.badge_text || 'Sale',
            auto_sale_badge: settings.auto_sale_badge || 'no',
            show_discount_percentage: settings.show_discount_percentage || 'no',
            button_full_width: settings.button_full_width || 'no',
            button_text: settings.button_text || 'Add to Cart',
            variable_button_text: settings.variable_button_text || 'Select options',
            added_button_text: settings.added_button_text || ''
        })
        .done(function(response) {
            $productsContainer.removeClass('wpb-loading');
            $filterContainer.removeClass('wpb-loading');
            if (response.success && response.data.html) {
                var $newHtml = $(response.data.html);
                $productsContainer.replaceWith($newHtml);
                if (categoryId === 0) {
                    $filterContainer.find('.wpb-filter-button').removeClass('active');
                    $filterContainer.find('.wpb-filter-button').first().addClass('active');
                } else {
                    $filterContainer.find('.wpb-filter-button').removeClass('active');
                    $button.addClass('active');
                }
                if (typeof wpbUpdateUrlParams === 'function') {
                    if (categorySlug) {
                        wpbUpdateUrlParams({category: categorySlug});
                    } else {
                        wpbUpdateUrlParams({category: ''});
                    }
                }
            }
        })
        .fail(function() {
            $productsContainer.removeClass('wpb-loading');
            $filterContainer.removeClass('wpb-loading');
        });
    });

    $(document).on('change', '.wpb-filter-checkbox-input', function() {
        var $checkbox = $(this);
        var $filterContainer = $checkbox.closest('.wpb-category-filter');
        var filterMode = $filterContainer.attr('data-filter-mode') || 'single';

        if (filterMode === 'multiple') {
            triggerMultiFilter($filterContainer);
        }
    });

    $(document).on('change', '.wpb-price-min, .wpb-price-max', function() {
        var $input = $(this);
        var $filterContainer = $input.closest('.wpb-price-range-filter');
        var autoFilter = $filterContainer.attr('data-auto-filter') === 'yes';
        if (autoFilter) {
            clearTimeout($filterContainer.data('priceTimeout'));
            $filterContainer.data('priceTimeout', setTimeout(function() {
                triggerPriceFilter($filterContainer);
            }, 400));
        }
    });

    function triggerPriceFilter($filterContainer) {
        var connectId = $filterContainer.attr('data-connect-id') || '';
        var $grid = connectId ? $('.wpb-product-grid[data-connect-id="' + connectId + '"]') : $();
        var $productsContainer = connectId ? $('#wpb-grid-' + connectId) : $();

        if (!$productsContainer.length && $grid.length) {
            $productsContainer = $grid.find('.wpb-products').first();
        }

        if (!$productsContainer.length) {
            $productsContainer = $filterContainer.nextAll('.wpb-products').first();
        }

        if (!$productsContainer.length) {
            $productsContainer = $filterContainer.prevAll('.wpb-products').first();
        }

        if (!$productsContainer.length) {
            $productsContainer = $filterContainer.closest('.wpb-product-grid').find('.wpb-products').first();
        }

        if (!$productsContainer.length) {
            $productsContainer = $('.wpb-products').first();
        }

        if (!$productsContainer.length) {
            return;
        }

        var targetId = connectId || ($productsContainer.attr('id') ? $productsContainer.attr('id').replace(/^wpb-grid-/, '') : 'wpb-products');
        var minPrice = parseFloat($filterContainer.find('.wpb-price-min').val()) || '';
        var maxPrice = parseFloat($filterContainer.find('.wpb-price-max').val()) || '';

        $productsContainer.addClass('wpb-loading');

        var settings = {};
        if ($grid.length) {
            var elementorSettings = $grid.closest('.elementor-widget-wpb-product-grid').data('settings');
            if (typeof elementorSettings !== 'undefined') {
                settings = elementorSettings;
            }
        }

        var currentDesktop = $productsContainer[0].style.getPropertyValue('--wpb-cols-desktop');
        var currentTablet = $productsContainer[0].style.getPropertyValue('--wpb-cols-tablet');
        var currentMobile = $productsContainer[0].style.getPropertyValue('--wpb-cols-mobile');
        if (currentDesktop) settings.columns = parseInt(currentDesktop);
        if (currentTablet) settings.columns_tablet = parseInt(currentTablet);
        if (currentMobile) settings.columns_mobile = parseInt(currentMobile);

        if (typeof wpbShowSkeletons === 'function') {
            var skeletonCols = settings.columns || 4;
            wpbShowSkeletons($productsContainer, skeletonCols);
        }

        $.post(dekAdmin.ajaxUrl, {
            action: 'dek_filter_products',
            _wpnonce: dekAdmin.toggleNonce,
            connect_id: connectId,
            target_id: targetId,
            category_id: 0,
            category_ids: '',
            min_price: minPrice,
            max_price: maxPrice,
            posts_per_page: settings.posts_per_page || 12,
            order_by: settings.order_by || 'date',
            order: settings.order || 'desc',
            show_title: settings.show_title || 'yes',
            show_price: settings.show_price || 'yes',
            show_add_to_cart: settings.show_add_to_cart || 'yes',
            ajax_add_to_cart: settings.ajax_add_to_cart || 'yes',
            columns: settings.columns || 4,
            tablet_columns: settings.columns_tablet || Math.max(2, Math.floor((settings.columns || 4) / 2)),
            mobile_columns: settings.columns_mobile || 1,
            show_badge: settings.show_badge || 'no',
            badge_text: settings.badge_text || 'Sale',
            auto_sale_badge: settings.auto_sale_badge || 'no',
            show_discount_percentage: settings.show_discount_percentage || 'no',
            button_full_width: settings.button_full_width || 'no',
            button_text: settings.button_text || 'Add to Cart',
            variable_button_text: settings.variable_button_text || 'Select options',
            added_button_text: settings.added_button_text || ''
        })
        .done(function(response) {
            $productsContainer.removeClass('wpb-loading');
            if (response.success && response.data.html) {
                $productsContainer.replaceWith(response.data.html);
            }
        })
        .fail(function() {
            $productsContainer.removeClass('wpb-loading');
        });
    }

    function updatePriceSlider($slider, changedHandle) {
        var $filterContainer = $slider.closest('.wpb-price-range-filter');
        var minVal = parseFloat($slider.data('min')) || 0;
        var maxVal = parseFloat($slider.data('max')) || 1000;
        var step = parseFloat($slider.data('step')) || 1;
        var $minRange = $slider.find('.wpb-price-range-min');
        var $maxRange = $slider.find('.wpb-price-range-max');
        var $range = $slider.find('.wpb-slider-range');
        var $minValue = $filterContainer.find('.wpb-slider-min-value');
        var $maxValue = $filterContainer.find('.wpb-slider-max-value');
        var $minInput = $filterContainer.find('.wpb-price-min');
        var $maxInput = $filterContainer.find('.wpb-price-max');
        var currency = $slider.data('currency') || '';
        var currentMin = parseFloat($minRange.val());
        var currentMax = parseFloat($maxRange.val());

        if (changedHandle === 'min' && currentMin > currentMax - step) {
            currentMin = currentMax - step;
            $minRange.val(currentMin);
        } else if (changedHandle === 'max' && currentMax < currentMin + step) {
            currentMax = currentMin + step;
            $maxRange.val(currentMax);
        }

        currentMin = Math.max(minVal, currentMin);
        currentMax = Math.min(maxVal, currentMax);
        var span = Math.max(1, maxVal - minVal);
        var minPercent = ((currentMin - minVal) / span) * 100;
        var maxPercent = ((currentMax - minVal) / span) * 100;

        $range.css('left', minPercent + '%').css('right', (100 - maxPercent) + '%');
        $minValue.text(currency + Math.round(currentMin).toLocaleString());
        $maxValue.text(currency + Math.round(currentMax).toLocaleString());
        $minInput.val(currentMin);
        $maxInput.val(currentMax);
    }

    function initPriceSliders(context) {
        $(context || document).find('.wpb-price-range-slider').each(function() {
            updatePriceSlider($(this));
        });
    }

    $(document).on('input change', '.wpb-price-range-min, .wpb-price-range-max', function(event) {
        var $rangeInput = $(this);
        var $slider = $rangeInput.closest('.wpb-price-range-slider');
        var $filterContainer = $slider.closest('.wpb-price-range-filter');
        updatePriceSlider($slider, $rangeInput.hasClass('wpb-price-range-min') ? 'min' : 'max');

        if ($filterContainer.attr('data-auto-filter') === 'yes') {
            clearTimeout($filterContainer.data('priceTimeout'));
            $filterContainer.data('priceTimeout', setTimeout(function() {
                triggerPriceFilter($filterContainer);
            }, event.type === 'input' ? 450 : 0));
        }
    });

    $(document).on('click', '.wpb-price-filter-button', function() {
        triggerPriceFilter($(this).closest('.wpb-price-range-filter'));
    });

    $(function() {
        initPriceSliders(document);
    });

    $(window).on('elementor/frontend/init', function() {
        if (window.elementorFrontend && elementorFrontend.hooks) {
            elementorFrontend.hooks.addAction('frontend/element_ready/wpb-price-range.default', function($scope) {
                initPriceSliders($scope);
            });
        }
    });

    $(document).on('mouseenter focusin touchstart', '.wpb-price-range-slider', function() {
        var $slider = $(this);
        if (!$slider.data('initialized')) {
            $slider.data('initialized', true);
            updatePriceSlider($slider);
        }
    });

    function getSearchPopup($container) {
        var id = $container.attr('id') || ($container.data('popup-id') || '');
        if (!$container.data('popup-id')) {
            id = 'wpb-search-popup-' + Math.random().toString(36).slice(2);
            $container.data('popup-id', id);
        }
        var $popup = $('#' + id);
        if (!$popup.length) {
            $popup = $('<div class="wpb-product-search-results" id="' + id + '"></div>').appendTo('body');
            $container.data('popup', $popup);
        }
        return $popup;
    }

    function positionSearchPopup($container, $popup) {
        var rect = $container[0].getBoundingClientRect();
        $popup.css({
            position: 'absolute',
            top: (window.pageYOffset + rect.bottom + 6) + 'px',
            left: (window.pageXOffset + rect.left) + 'px',
            width: rect.width + 'px'
        });
    }

    function triggerProductSearch($container) {
        var $input = $container.find('.wpb-product-search-input');
        var $popup = getSearchPopup($container);
        var $loading = $container.find('.wpb-product-search-loading');
        var term = $input.val();
        var minChars = parseInt($container.data('min-chars')) || 3;
        var perPage = parseInt($container.data('per-page')) || 8;
        var showImage = $container.data('show-image') || 'yes';
        var showPrice = $container.data('show-price') || 'yes';
        var showNoResult = $container.data('show-no-result') || 'yes';
        var noResultText = $container.data('no-result-text') || 'No products found.';
        var connectId = $container.data('connect-id') || '';

        if (term.length < minChars) {
            $popup.hide().empty();
            return;
        }

        $loading.addClass('wpb-active');
        positionSearchPopup($container, $popup);

        $.post(dekAdmin.ajaxUrl, {
            action: 'dek_search_products',
            _wpnonce: dekAdmin.toggleNonce,
            term: term,
            per_page: perPage,
            show_image: showImage,
            show_price: showPrice,
            show_no_result: showNoResult,
            no_result_text: noResultText,
            connect_id: connectId
        })
        .done(function(response) {
            $loading.removeClass('wpb-active');
            if (response.success) {
                $popup.html(response.data.html);
                if (response.data.html) {
                    positionSearchPopup($container, $popup);
                    $popup.show();
                } else {
                    $popup.hide();
                }
            }
        })
        .fail(function() {
            $loading.removeClass('wpb-active');
        });
    }

    function closeAllSearchPopups(except) {
        $('.wpb-product-search-results').each(function() {
            var $popup = $(this);
            if ($popup.attr('id') && (!$popup.closest('.wpb-product-search').length)) {
                if (except && $popup[0] === except[0]) {
                    return;
                }
                $popup.hide();
            }
        });
    }

    $(document).on('input', '.wpb-product-search .wpb-product-search-input', function() {
        var $input = $(this);
        var $container = $input.closest('.wpb-product-search');
        clearTimeout($container.data('searchTimeout'));
        $container.data('searchTimeout', setTimeout(function() {
            triggerProductSearch($container);
        }, 300));
    });

    $(document).on('focus', '.wpb-product-search .wpb-product-search-input', function() {
        var $input = $(this);
        var $container = $input.closest('.wpb-product-search');
        var $popup = getSearchPopup($container);
        if ($popup.html()) {
            positionSearchPopup($container, $popup);
            $popup.show();
        }
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('.wpb-product-search').length && !$(e.target).closest('.wpb-product-search-results').length) {
            closeAllSearchPopups();
        }
    });

    $(window).on('scroll resize', function() {
        $('.wpb-product-search-results').each(function() {
            var $popup = $(this);
            if ($popup.attr('id') && !$popup.closest('.wpb-product-search').length && $popup.is(':visible')) {
                var $container = $('.wpb-product-search[data-popup-id="' + $popup.attr('id') + '"]');
                if ($container.length) {
                    positionSearchPopup($container, $popup);
                }
            }
        });
    });

    function triggerMultiFilter($filterContainer) {
        var connectId = $filterContainer.attr('data-connect-id') || '';
        var $grid = connectId ? $('.wpb-product-grid[data-connect-id="' + connectId + '"]') : $();
        var $productsContainer = connectId ? $('#wpb-grid-' + connectId) : $();

        if (!$productsContainer.length && $grid.length) {
            $productsContainer = $grid.find('.wpb-products').first();
        }

        if (!$productsContainer.length) {
            $productsContainer = $filterContainer.nextAll('.wpb-products').first();
        }

        if (!$productsContainer.length) {
            $productsContainer = $filterContainer.prevAll('.wpb-products').first();
        }

        if (!$productsContainer.length) {
            $productsContainer = $filterContainer.closest('.wpb-product-grid').find('.wpb-products').first();
        }

        if (!$productsContainer.length) {
            $productsContainer = $('.wpb-products').first();
        }

        if (!$productsContainer.length) {
            return;
        }

        var targetId = connectId || ($productsContainer.attr('id') ? $productsContainer.attr('id').replace(/^wpb-grid-/, '') : 'wpb-products');
        var categorySlugs = [];
        $filterContainer.find('.wpb-filter-checkbox-input:checked').each(function() {
            categorySlugs.push($(this).val());
        });

        $productsContainer.addClass('wpb-loading');

        var settings = {};
        var $widgetWrapper = $productsContainer.closest('.elementor-widget-wpb-product-grid');
        if ($widgetWrapper.length) {
            var elementorSettings = $widgetWrapper.data('settings');
            if (typeof elementorSettings !== 'undefined') {
                settings = elementorSettings;
            }
        }

        var currentDesktop = $productsContainer[0].style.getPropertyValue('--wpb-cols-desktop');
        var currentTablet = $productsContainer[0].style.getPropertyValue('--wpb-cols-tablet');
        var currentMobile = $productsContainer[0].style.getPropertyValue('--wpb-cols-mobile');
        if (currentDesktop) settings.columns = parseInt(currentDesktop);
        if (currentTablet) settings.columns_tablet = parseInt(currentTablet);
        if (currentMobile) settings.columns_mobile = parseInt(currentMobile);

        if (typeof wpbShowSkeletons === 'function') {
            var skeletonCols = settings.columns || 4;
            wpbShowSkeletons($productsContainer, skeletonCols);
        }

        $.post(dekAdmin.ajaxUrl, {
            action: 'dek_filter_products',
            _wpnonce: dekAdmin.toggleNonce,
            connect_id: connectId,
            target_id: targetId,
            category_id: 0,
            category_slug: categorySlugs.length === 1 ? categorySlugs[0] : '',
            category_slugs: categorySlugs.length > 1 ? categorySlugs.join(',') : '',
            posts_per_page: settings.posts_per_page || 12,
            order_by: settings.order_by || 'date',
            order: settings.order || 'desc',
            show_title: settings.show_title || 'yes',
            show_price: settings.show_price || 'yes',
            show_add_to_cart: settings.show_add_to_cart || 'yes',
            ajax_add_to_cart: settings.ajax_add_to_cart || 'yes',
            columns: settings.columns || 4,
            tablet_columns: settings.columns_tablet || Math.max(2, Math.floor((settings.columns || 4) / 2)),
            mobile_columns: settings.columns_mobile || 1,
            show_badge: settings.show_badge || 'no',
            badge_text: settings.badge_text || 'Sale',
            auto_sale_badge: settings.auto_sale_badge || 'no',
            show_discount_percentage: settings.show_discount_percentage || 'no',
            button_full_width: settings.button_full_width || 'no',
            button_text: settings.button_text || 'Add to Cart',
            variable_button_text: settings.variable_button_text || 'Select options',
            added_button_text: settings.added_button_text || ''
        })
        .done(function(response) {
            $productsContainer.removeClass('wpb-loading');
            if (response.success && response.data.html) {
                $productsContainer.replaceWith(response.data.html);
            }
        })
        .fail(function() {
            $productsContainer.removeClass('wpb-loading');
        });
    }

    window.wpbShowSkeletons = function($container, columns) {
        var skeletonCount = Math.min(columns || 4, 8);
        var $skeletonCards = $();
        for (var i = 0; i < skeletonCount; i++) {
            $skeletonCards = $skeletonCards.add($('<div class="wpb-skeleton-card">' +
                '<div class="wpb-skeleton-image"></div>' +
                '<div class="wpb-skeleton-content">' +
                '<div class="wpb-skeleton-title"></div>' +
                '<div class="wpb-skeleton-price"></div>' +
                '<div class="wpb-skeleton-button"></div>' +
                '</div>' +
                '</div>'));
        }
        $container.html($skeletonCards);
    };

    window.wpbUpdateUrlParams = function(params) {
        if (typeof history === 'undefined' || !history.replaceState) {
            return;
        }
        var url = new URL(window.location.href);
        Object.keys(params).forEach(function(key) {
            var value = params[key];
            if (value === '' || value === null || value === undefined || value === 0) {
                url.searchParams.delete(key);
            } else {
                url.searchParams.set(key, value);
            }
        });
        history.replaceState(null, '', url.toString());
    };

    window.wpbGetUrlParam = function(name) {
        var url = new URL(window.location.href);
        return url.searchParams.get(name);
    };

    var urlCategory = wpbGetUrlParam('category');
    var urlCategoryTriggered = false;
    if (urlCategory) {
        setTimeout(function() {
            $('.wpb-filter-button[data-category-slug="' + urlCategory + '"]').first().trigger('click');
            urlCategoryTriggered = true;
        }, 500);
    }

    var urlCategories = wpbGetUrlParam('categories');
    if (urlCategories) {
        setTimeout(function() {
            var slugs = urlCategories.split(',');
            slugs.forEach(function(slug) {
                $('.wpb-filter-checkbox-input[value="' + slug + '"]').prop('checked', true).trigger('change');
            });
            urlCategoryTriggered = true;
        }, 500);
    }

    if (!urlCategoryTriggered) {
        var currentCategorySlug = '';
        if ($('body').hasClass('product-cat') || $('body').hasClass('archive')) {
            var bodyClasses = $('body').attr('class').split(/\s+/);
            $.each(bodyClasses, function(i, cls) {
                if (cls.indexOf('product-cat-') === 0) {
                    currentCategorySlug = cls.replace('product-cat-', '');
                    return false;
                }
            });
        }

        if (currentCategorySlug) {
            setTimeout(function() {
                var $matchingButton = $('.wpb-filter-button[data-category-slug="' + currentCategorySlug + '"]');
                var $matchingCheckbox = $('.wpb-filter-checkbox-input[value="' + currentCategorySlug + '"]');
                var $filterContainer = $matchingButton.length ? $matchingButton.closest('.wpb-category-filter') : ($matchingCheckbox.length ? $matchingCheckbox.closest('.wpb-category-filter') : $());
                if ($filterContainer.length) {
                    var filterMode = $filterContainer.attr('data-filter-mode') || 'single';
                    if (filterMode === 'single') {
                        if ($matchingButton.length) {
                            $matchingButton.trigger('click');
                        } else if ($filterContainer.find('.wpb-filter-button').length > 0) {
                            $filterContainer.find('.wpb-filter-button').first().trigger('click');
                        }
                    } else {
                        if ($matchingCheckbox.length) {
                            $matchingCheckbox.prop('checked', true).trigger('change');
                        }
                    }
                }
            }, 500);
        }
    }

});
