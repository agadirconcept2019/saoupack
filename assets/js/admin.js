jQuery(function ($) {
    $('.b2b-crm__quick').on('change', function () {
        const $select = $(this);
        const leadId = $select.data('lead-id');
        const field = $select.data('field');
        const value = $select.val();

        $.post(B2BCRM.ajaxUrl, {
            action: 'b2b_crm_quick_update',
            nonce: B2BCRM.nonce,
            lead_id: leadId,
            field: field,
            value: value,
        }).done(function (response) {
            if (!response.success) {
                alert(response.data && response.data.message ? response.data.message : 'Erreur');
            }
        });
    });
});
