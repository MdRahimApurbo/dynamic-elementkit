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
    }

    $('#wpb-add-new-template-top, #wpb-add-new-template').on('click', function(e) {
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
