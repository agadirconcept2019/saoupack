<?php
/**
 * Plugin Name: CRM Saoupack
 * Description: CRM Saoupack admin-only pour la collecte légale et la gestion de leads professionnels marocains.
 * Version: 0.3.4
 * Author: OpenAI
 * License: GPL-2.0-or-later
 * Text Domain: b2b-crm-maroc
 */

if (!defined('ABSPATH')) {
    exit;
}

define('B2B_CRM_MAROC_VERSION', '0.3.4');
define('B2B_CRM_MAROC_PATH', plugin_dir_path(__FILE__));
define('B2B_CRM_MAROC_URL', plugin_dir_url(__FILE__));

define('B2B_CRM_MAROC_ACCESS_CAP', 'b2b_crm_access');
define('B2B_CRM_MAROC_LEADS_CAP', 'b2b_crm_manage_leads');
define('B2B_CRM_MAROC_SETTINGS_CAP', 'b2b_crm_manage_settings');
define('B2B_CRM_MAROC_SOURCES_CAP', 'b2b_crm_manage_sources');
define('B2B_CRM_MAROC_EMAIL_CAP', 'b2b_crm_send_email');

require_once B2B_CRM_MAROC_PATH . 'bootstrap/class-b2b-crm-bootstrap.php';

register_activation_hook(__FILE__, array('B2B_CRM_Bootstrap', 'activate'));
register_deactivation_hook(__FILE__, array('B2B_CRM_Bootstrap', 'deactivate'));

add_action('plugins_loaded', array('B2B_CRM_Bootstrap', 'init'));

add_filter(
    'plugin_action_links_' . plugin_basename(__FILE__),
    function ($links) {
        if (!current_user_can(B2B_CRM_MAROC_ACCESS_CAP)) {
            return $links;
        }

        $crm_link = sprintf(
            '<a href="%s">%s</a>',
            esc_url(admin_url('admin.php?page=b2b-crm-maroc')),
            esc_html__('Ouvrir le CRM', 'b2b-crm-maroc')
        );

        array_unshift($links, $crm_link);

        return $links;
    }
);
