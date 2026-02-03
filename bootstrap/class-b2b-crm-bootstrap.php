<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once B2B_CRM_MAROC_PATH . 'database/class-b2b-crm-lead-table.php';
require_once B2B_CRM_MAROC_PATH . 'database/class-b2b-crm-interaction-table.php';
require_once B2B_CRM_MAROC_PATH . 'services/class-b2b-crm-lead-repository.php';
require_once B2B_CRM_MAROC_PATH . 'services/class-b2b-crm-email-service.php';
require_once B2B_CRM_MAROC_PATH . 'services/class-b2b-crm-interaction-repository.php';
require_once B2B_CRM_MAROC_PATH . 'admin/class-b2b-crm-admin-menu.php';
require_once B2B_CRM_MAROC_PATH . 'admin/class-b2b-crm-views.php';
require_once B2B_CRM_MAROC_PATH . 'admin/class-b2b-crm-assets.php';
require_once B2B_CRM_MAROC_PATH . 'api/class-b2b-crm-ajax.php';
require_once B2B_CRM_MAROC_PATH . 'utils/class-b2b-crm-sanitizer.php';

class B2B_CRM_Bootstrap
{
    public static function init()
    {
        if (!is_admin()) {
            return;
        }

        if (!current_user_can(B2B_CRM_MAROC_CAP)) {
            return;
        }

        B2B_CRM_Admin_Menu::register();
        B2B_CRM_Assets::register();
        B2B_CRM_Ajax::register();
    }

    public static function activate()
    {
        B2B_CRM_Lead_Table::create_table();
        B2B_CRM_Interaction_Table::create_table();
    }

    public static function deactivate()
    {
        // Placeholder for future cleanup.
    }
}
