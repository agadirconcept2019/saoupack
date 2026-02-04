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

    $('.b2b-crm__chip').on('click', function () {
        const value = $(this).data('sector');
        if (!value) {
            return;
        }
        $('#b2b-crm-sector').val(value);
        $('.b2b-crm__chip').removeClass('is-active');
        $(this).addClass('is-active');
    });

    $('.b2b-crm__precision-switch button').on('click', function () {
        const value = $(this).data('precision');
        if (!value) {
            return;
        }
        $('.b2b-crm__precision-switch button').removeClass('is-active');
        $(this).addClass('is-active');
        $('#b2b-crm-precision').val(value);
    });

    $('.b2b-crm__source input[type="checkbox"]').on('change', function () {
        $(this).closest('.b2b-crm__source').toggleClass('is-active', $(this).is(':checked'));
    });

    const $sourceTabs = $('[data-source-tab]');
    const $sourcePanels = $('[data-source-panel]');

    if ($sourceTabs.length && $sourcePanels.length) {
        $sourceTabs.on('click', function () {
            const key = $(this).data('source-tab');
            $sourceTabs.removeClass('is-active');
            $sourcePanels.removeClass('is-active');
            $(this).addClass('is-active');
            $sourcePanels.filter(`[data-source-panel="${key}"]`).addClass('is-active');
        });
    }
});
