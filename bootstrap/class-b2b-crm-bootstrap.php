<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once B2B_CRM_MAROC_PATH . 'database/class-b2b-crm-lead-table.php';
require_once B2B_CRM_MAROC_PATH . 'database/class-b2b-crm-interaction-table.php';
require_once B2B_CRM_MAROC_PATH . 'database/class-b2b-crm-account-table.php';
require_once B2B_CRM_MAROC_PATH . 'database/class-b2b-crm-contact-table.php';
require_once B2B_CRM_MAROC_PATH . 'database/class-b2b-crm-module-item-table.php';
require_once B2B_CRM_MAROC_PATH . 'services/class-b2b-crm-lead-repository.php';
require_once B2B_CRM_MAROC_PATH . 'services/class-b2b-crm-account-repository.php';
require_once B2B_CRM_MAROC_PATH . 'services/class-b2b-crm-contact-repository.php';
require_once B2B_CRM_MAROC_PATH . 'services/class-b2b-crm-module-item-repository.php';
require_once B2B_CRM_MAROC_PATH . 'services/class-b2b-crm-collector.php';
require_once B2B_CRM_MAROC_PATH . 'services/class-b2b-crm-email-service.php';
require_once B2B_CRM_MAROC_PATH . 'services/class-b2b-crm-interaction-repository.php';
require_once B2B_CRM_MAROC_PATH . 'frontend/class-b2b-crm-shortcode.php';
require_once B2B_CRM_MAROC_PATH . 'admin/class-b2b-crm-admin-menu.php';
require_once B2B_CRM_MAROC_PATH . 'admin/class-b2b-crm-views.php';
require_once B2B_CRM_MAROC_PATH . 'admin/class-b2b-crm-actions.php';
require_once B2B_CRM_MAROC_PATH . 'admin/class-b2b-crm-assets.php';
require_once B2B_CRM_MAROC_PATH . 'api/class-b2b-crm-ajax.php';
require_once B2B_CRM_MAROC_PATH . 'utils/class-b2b-crm-sanitizer.php';

class B2B_CRM_Bootstrap
{
    public static function init()
    {
        B2B_CRM_Shortcode::register();

        if (is_admin()) {
            if (!current_user_can(B2B_CRM_MAROC_CAP)) {
                return;
            }

            B2B_CRM_Admin_Menu::register();
            B2B_CRM_Actions::register();
            B2B_CRM_Assets::register();
            B2B_CRM_Ajax::register();
        }
    }

    public static function activate()
    {
        B2B_CRM_Lead_Table::create_table();
        B2B_CRM_Interaction_Table::create_table();
        B2B_CRM_Account_Table::create_table();
        B2B_CRM_Contact_Table::create_table();
        B2B_CRM_Module_Item_Table::create_table();
        self::register_roles();
    }

    public static function deactivate()
    {
        // Placeholder for future cleanup.
    }

    private static function register_roles()
    {
        $admin = get_role('administrator');
        if ($admin && !$admin->has_cap(B2B_CRM_MAROC_LEADS_CAP)) {
            $admin->add_cap(B2B_CRM_MAROC_LEADS_CAP);
        }

        add_role(
            'b2b_crm_agent',
            __('CRM Agent', 'b2b-crm-maroc'),
            array(
                'read' => true,
                B2B_CRM_MAROC_LEADS_CAP => true,
            )
        );
    }
}
