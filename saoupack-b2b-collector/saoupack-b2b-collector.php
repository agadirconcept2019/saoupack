<?php
/**
 * Plugin Name: CRM Saoupack B2B Collector
 * Description: Couche V1 conforme (table dédiée + REST + admin minimal) pour CRM Saoupack.
 * Version: 1.0.0
 * Author: Agadir Concept
 * License: GPL-2.0-or-later
 * Text Domain: saoupack-b2b-collector
 */

if (!defined('ABSPATH')) {
    exit;
}

define('SAOUPACK_B2B_COLLECTOR_VERSION', '1.0.0');
define('SAOUPACK_B2B_COLLECTOR_PATH', plugin_dir_path(__FILE__));
define('SAOUPACK_B2B_COLLECTOR_URL', plugin_dir_url(__FILE__));
define('SAOUPACK_B2B_COLLECTOR_CAP', 'manage_options');

require_once SAOUPACK_B2B_COLLECTOR_PATH . 'includes/helpers/sanitizer.php';
require_once SAOUPACK_B2B_COLLECTOR_PATH . 'includes/db/class-leads-table.php';
require_once SAOUPACK_B2B_COLLECTOR_PATH . 'includes/services/class-leads-service.php';
require_once SAOUPACK_B2B_COLLECTOR_PATH . 'includes/api/class-rest-leads.php';
require_once SAOUPACK_B2B_COLLECTOR_PATH . 'includes/admin/class-leads-page.php';
require_once SAOUPACK_B2B_COLLECTOR_PATH . 'includes/admin/class-lead-form.php';
require_once SAOUPACK_B2B_COLLECTOR_PATH . 'includes/admin/class-admin-menu.php';

class Saoupack_B2B_Collector
{
    public static function init()
    {
        Saoupack_B2B_Collector_Admin_Menu::register();
        Saoupack_B2B_Collector_Leads_Page::register();
        Saoupack_B2B_Collector_Rest_Leads::register();
    }

    public static function activate()
    {
        Saoupack_B2B_Collector_Leads_Table::create_table();
        Saoupack_B2B_Collector_Leads_Table::maybe_migrate_from_legacy();
    }
}

register_activation_hook(__FILE__, array('Saoupack_B2B_Collector', 'activate'));
add_action('plugins_loaded', array('Saoupack_B2B_Collector', 'init'));
