<?php

if (!defined('ABSPATH')) {
    exit;
}

class Saoupack_B2B_Collector_Admin_Menu
{
    public static function register()
    {
        add_action('admin_menu', array(__CLASS__, 'add_menu'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_assets'));
    }

    public static function add_menu()
    {
        global $menu;

        $legacy_slug = 'b2b-crm-maroc';
        $has_legacy = false;

        foreach ((array) $menu as $item) {
            if (!empty($item[2]) && $item[2] === $legacy_slug) {
                $has_legacy = true;
                break;
            }
        }

        if ($has_legacy) {
            add_submenu_page(
                $legacy_slug,
                __('Leads (V1)', 'saoupack-b2b-collector'),
                __('Leads (V1)', 'saoupack-b2b-collector'),
                SAOUPACK_B2B_COLLECTOR_CAP,
                'saoupack-b2b-collector-leads',
                array(__CLASS__, 'render_leads_page')
            );
        } else {
            add_menu_page(
                __('Saoupack CRM', 'saoupack-b2b-collector'),
                __('Saoupack CRM', 'saoupack-b2b-collector'),
                SAOUPACK_B2B_COLLECTOR_CAP,
                'saoupack-b2b-collector-leads',
                array(__CLASS__, 'render_leads_page'),
                'dashicons-id-alt',
                26
            );
        }
    }

    public static function enqueue_assets($hook)
    {
        if (strpos($hook, 'saoupack-b2b-collector-leads') === false) {
            return;
        }

        wp_enqueue_style(
            'saoupack-b2b-collector-admin',
            SAOUPACK_B2B_COLLECTOR_URL . 'assets/css/admin.css',
            array(),
            SAOUPACK_B2B_COLLECTOR_VERSION
        );

        wp_enqueue_script(
            'saoupack-b2b-collector-admin',
            SAOUPACK_B2B_COLLECTOR_URL . 'assets/js/admin-app.js',
            array('jquery'),
            SAOUPACK_B2B_COLLECTOR_VERSION,
            true
        );
    }

    public static function render_leads_page()
    {
        Saoupack_B2B_Collector_Leads_Page::render();
    }
}
