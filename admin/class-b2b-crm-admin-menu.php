<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once B2B_CRM_MAROC_PATH . 'admin/class-b2b-crm-lead-list-page.php';
require_once B2B_CRM_MAROC_PATH . 'admin/class-b2b-crm-lead-detail-page.php';

class B2B_CRM_Admin_Menu
{
    public static function register()
    {
        add_action('admin_menu', array(__CLASS__, 'add_menu'));
    }

    public static function add_menu()
    {
        add_menu_page(
            __('CRM B2B Maroc', 'b2b-crm-maroc'),
            __('CRM B2B Maroc', 'b2b-crm-maroc'),
            B2B_CRM_MAROC_CAP,
            'b2b-crm-maroc',
            array(__CLASS__, 'render'),
            'dashicons-id-alt',
            26
        );
    }

    public static function render()
    {
        $page = isset($_GET['page']) ? sanitize_key($_GET['page']) : 'b2b-crm-maroc';
        $lead_id = isset($_GET['lead_id']) ? absint($_GET['lead_id']) : 0;

        if ($lead_id) {
            B2B_CRM_Lead_Detail_Page::render($lead_id);
            return;
        }

        if ($page === 'b2b-crm-maroc') {
            B2B_CRM_Lead_List_Page::render();
        }
    }
}
