jQuery(document).ready(function($) {
    var $overlay = $('#wpb-modal-overlay');
    var $form = $('#wpb-create-template-form');

    function openModal() {
        $overlay.css('display', 'flex');
        $('#dek_template_name').focus();
    }

    $('#dek_template_name').on('input', function() {
        var $slug = $('#dek_template_slug');
        if (!$slug.data('edited')) {
            $slug.val($(this).val().toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, ''));
        }
    });

    $(document).on('input', '#dek_template_slug', function() {
        $(this).data('edited', true);
    });

    function closeModal() {
        $overlay.hide();
        $form[0].reset();
        $('#dek_template_slug').removeData('edited');
        $('#dek_landing_product_id').val(0);
        $('#dek_landing_product_selected').text('No product assigned');
        $('#dek_landing_product_results').empty().prop('hidden', true);
    }

    $('#wpb-add-new-template-top, #wpb-add-new-template, #wpb-add-new-landing-top').on('click', function(e) {
        e.preventDefault();
        openModal();
    });

    $('.wpb-modal-close, .wpb-modal-close-btn').on('click', function() {
        closeModal();
    });

    $overlay.on('click', function(e) {
        if ($(e.target).is('#wpb-modal-overlay')) {
            closeModal();
        }
    });

    var productSearchTimer;
    $(document).on('input', '#dek_landing_product_search', function() {
        var $input = $(this);
        var term = $input.val().trim();
        var $results = $('#dek_landing_product_results');

        clearTimeout(productSearchTimer);
        $('#dek_landing_product_id').val(0);
        $('#dek_landing_product_selected').text('No product assigned');

        if (term.length < 2) {
            $results.empty().prop('hidden', true);
            return;
        }

        productSearchTimer = setTimeout(function() {
            $.get(dekAdmin.ajaxUrl, {
                action: 'dek_search_landing_products',
                nonce: dekAdmin.productSearchNonce,
                term: term
            }).done(function(response) {
                $results.empty();
                if (!response.success || !response.data.length) {
                    $('<div/>', { 'class': 'dek-product-search-empty', text: 'No matching products found.' }).appendTo($results);
                    $results.prop('hidden', false);
                    return;
                }

                $.each(response.data, function(_, product) {
                    $('<button/>', {
                        type: 'button',
                        'class': 'dek-product-search-option',
                        'data-product-id': product.id,
                        text: product.text
                    }).appendTo($results);
                });
                $results.prop('hidden', false);
            });
        }, 250);
    });

    $(document).on('click', '.dek-product-search-option', function() {
        var $option = $(this);
        $('#dek_landing_product_id').val($option.data('product-id'));
        $('#dek_landing_product_search').val($option.text());
        $('#dek_landing_product_selected').text('Selected: ' + $option.text());
        $('#dek_landing_product_results').empty().prop('hidden', true);
    });

    $(document).on('click', '.wpb-toggle-active', function(e) {
        e.preventDefault();
        var $checkbox = $(this);
        var postId = $checkbox.data('id');
        var nonce = dekAdmin.toggleNonce;

        $.post(dekAdmin.ajaxUrl, {
            action: 'dek_toggle_active',
            post_id: postId,
            _wpnonce: nonce
        })
        .done(function(response) {
            if (response.success) {
                $checkbox.prop('checked', response.data.active);
            }
        })
        .fail(function() {
            $checkbox.prop('checked', !$checkbox.prop('checked'));
        });
    });

    $(document).on('click', '.submitdelete', function(e) {
        if (!confirm(dekAdmin.strings.confirmDelete)) {
            e.preventDefault();
        }
    });

    $('form[action*="edit.php"]').attr('action', dekAdmin.formAction);
});
