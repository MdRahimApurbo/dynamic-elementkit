jQuery(document).ready(function($) {
    var $overlay = $('#wpb-modal-overlay');
    var $form = $('#wpb-create-template-form');

    function openModal() {
        $overlay.css('display', 'flex');
        $('#wpb_template_name').focus();
    }

    function closeModal() {
        $overlay.hide();
        $form[0].reset();
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
        var nonce = wpbAdmin.toggleNonce;

        $.post(wpbAdmin.ajaxUrl, {
            action: 'wpb_toggle_active',
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
        if (!confirm(wpbAdmin.strings.confirmDelete)) {
            e.preventDefault();
        }
    });

    $('form[action*="edit.php"]').attr('action', wpbAdmin.formAction);
});
