<?php

if (!defined('ABSPATH')) {
    exit;
}

class B2B_CRM_Assets
{
    public static function register()
    {
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue'));
    }

    public static function enqueue($hook)
    {
        if (strpos($hook, 'b2b-crm-maroc') === false) {
            return;
        }

        wp_enqueue_style('b2b-crm-maroc-admin', B2B_CRM_MAROC_URL . 'assets/css/admin.css', array(), B2B_CRM_MAROC_VERSION);
        wp_enqueue_script('b2b-crm-maroc-admin', B2B_CRM_MAROC_URL . 'assets/js/admin.js', array('jquery'), B2B_CRM_MAROC_VERSION, true);

        wp_localize_script(
            'b2b-crm-maroc-admin',
            'B2BCRM',
            array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('b2b_crm_maroc_nonce'),
            )
        );
    }
}
