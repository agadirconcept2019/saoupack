(function () {
    const key = 'b2b_crm_sidebar_collapsed';
    const apply = () => {
        document.querySelectorAll('.b2b-crm__shell').forEach((shell) => {
            const collapsed = window.localStorage.getItem(key) === '1';
            shell.classList.toggle('is-collapsed', collapsed);
            const toggle = shell.querySelector('.b2b-crm__sidebar-toggle');
            if (toggle) {
                toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
                toggle.setAttribute('aria-label', collapsed ? 'Afficher le menu' : 'Réduire le menu');
            }
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', apply, { once: true });
    } else {
        apply();
    }
})();

jQuery(function ($) {
    const sidebarStorageKey = 'b2b_crm_sidebar_collapsed';
    $('.b2b-crm__sidebar-toggle').on('click keydown', function (event) {
        if (event.type === 'keydown' && event.key !== 'Enter' && event.key !== ' ') {
            return;
        }
        if (event.type === 'keydown') {
            event.preventDefault();
        }
        const $shell = $(this).closest('.b2b-crm__shell');
        if (!$shell.length) {
            return;
        }
        $shell.toggleClass('is-collapsed');
        const collapsed = $shell.hasClass('is-collapsed');
        window.localStorage.setItem(sidebarStorageKey, collapsed ? '1' : '0');
        $(this)
            .attr('aria-expanded', collapsed ? 'false' : 'true')
            .attr('aria-label', collapsed ? 'Afficher le menu' : 'Réduire le menu');
    });

    $('.b2b-crm__quick').on('change', function () {
        const $select = $(this);
        $.post(B2BCRM.ajaxUrl, {
            action: 'b2b_crm_quick_update',
            nonce: B2BCRM.nonce,
            lead_id: $select.data('lead-id'),
            field: $select.data('field'),
            value: $select.val(),
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
        const activateSourceTab = (key) => {
            $sourceTabs.removeClass('is-active').attr('aria-selected', 'false');
            $sourcePanels.removeClass('is-active').attr('hidden', true);
            $sourceTabs.filter(`[data-source-tab="${key}"]`).addClass('is-active').attr('aria-selected', 'true');
            $sourcePanels.filter(`[data-source-panel="${key}"]`).addClass('is-active').attr('hidden', false);
        };

        $sourceTabs.on('click', function () {
            activateSourceTab($(this).data('source-tab'));
        });

        activateSourceTab($sourceTabs.filter('.is-active').data('source-tab') || $sourceTabs.first().data('source-tab'));
    }



    $('[data-crm-modal-open]').on('click', function () {
        const key = $(this).data('crm-modal-open');
        $(`[data-crm-modal="${key}"]`).prop('hidden', false);
    });

    $('[data-crm-modal-close]').on('click', function () {
        const key = $(this).data('crm-modal-close');
        $(`[data-crm-modal="${key}"]`).prop('hidden', true);
    });

    $('.b2b-crm__modal').on('click', function (event) {
        if ($(event.target).is('.b2b-crm__modal')) {
            $(this).prop('hidden', true);
        }
    });

    $('.b2b-crm__test-logo').on('click', function () {
        const target = $(this).data('target');
        const url = String($(target).val() || '').trim();
        if (!url) {
            window.alert('Veuillez renseigner une URL de logo.');
            return;
        }
        window.open(url, '_blank', 'noopener,noreferrer');
    });
});
