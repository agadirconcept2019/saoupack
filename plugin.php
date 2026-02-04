<?php
/**
 * Plugin Name: B2B CRM Maroc Leads
 * Description: CRM B2B admin-only pour la collecte légale et la gestion de leads professionnels marocains.
 * Version: 0.1.0
 * Author: OpenAI
 * License: GPL-2.0-or-later
 * Text Domain: b2b-crm-maroc
 */

if (!defined('ABSPATH')) {
    exit;
}

define('B2B_CRM_MAROC_VERSION', '0.1.0');
define('B2B_CRM_MAROC_PATH', plugin_dir_path(__FILE__));
define('B2B_CRM_MAROC_URL', plugin_dir_url(__FILE__));

define('B2B_CRM_MAROC_CAP', 'manage_options');
define('B2B_CRM_MAROC_LEADS_CAP', 'b2b_crm_manage_leads');

require_once B2B_CRM_MAROC_PATH . 'bootstrap/class-b2b-crm-bootstrap.php';

register_activation_hook(__FILE__, array('B2B_CRM_Bootstrap', 'activate'));
register_deactivation_hook(__FILE__, array('B2B_CRM_Bootstrap', 'deactivate'));

add_action('plugins_loaded', array('B2B_CRM_Bootstrap', 'init'));
