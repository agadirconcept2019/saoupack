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
            __('CRM Saoupack', 'b2b-crm-maroc'),
            __('CRM Saoupack', 'b2b-crm-maroc'),
            B2B_CRM_MAROC_ACCESS_CAP,
            'b2b-crm-maroc',
            array(__CLASS__, 'render'),
            self::menu_icon(),
            26
        );
    }



    private static function menu_icon()
    {
        $svg = "<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'><circle cx='10' cy='10' r='9' fill='#111b27'/><text x='10' y='14' text-anchor='middle' font-family='Arial, sans-serif' font-size='11' font-weight='700' fill='white'>S</text></svg>";
        return 'data:image/svg+xml;utf8,' . rawurlencode($svg);
    }

    public static function render()
    {
        $page = isset($_GET['page']) ? sanitize_key($_GET['page']) : 'b2b-crm-maroc';
        $tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'dashboard';
        $lead_id = isset($_GET['lead_id']) ? absint($_GET['lead_id']) : 0;

        if ($lead_id) {
            B2B_CRM_Lead_Detail_Page::render($lead_id);
            return;
        }

        if ($page === 'b2b-crm-maroc') {
            B2B_CRM_Lead_List_Page::render($tab);
        }
    }
}
