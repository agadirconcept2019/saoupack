<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

$tables = array(
    $wpdb->prefix . 'b2b_leads',
    $wpdb->prefix . 'b2b_lead_interactions',
    $wpdb->prefix . 'b2b_accounts',
    $wpdb->prefix . 'b2b_contacts',
    $wpdb->prefix . 'b2b_contact_activities',
    $wpdb->prefix . 'b2b_module_items',
);

foreach ($tables as $table) {
    $wpdb->query("DROP TABLE IF EXISTS {$table}");
}

$options = array(
    'b2b_crm_settings',
    'b2b_crm_sources_config',
    'b2b_crm_modules_config',
    'b2b_crm_module_settings',
    'b2b_crm_key_values',
    'b2b_crm_collect_config',
    'b2b_crm_email_logs',
    'b2b_crm_import_logs',
    'b2b_crm_db_version',
);

foreach ($options as $option) {
    delete_option($option);
}

$roles_to_remove = array('b2b_crm_admin', 'b2b_crm_agent');
foreach ($roles_to_remove as $role) {
    remove_role($role);
}

$roles_to_cleanup = array('administrator', 'editor');
$caps_to_remove = array(
    'b2b_crm_access',
    'b2b_crm_manage_leads',
    'b2b_crm_manage_settings',
    'b2b_crm_manage_sources',
    'b2b_crm_send_email',
);

foreach ($roles_to_cleanup as $role_name) {
    $role = get_role($role_name);
    if (!$role) {
        continue;
    }
    foreach ($caps_to_remove as $cap) {
        if ($role->has_cap($cap)) {
            $role->remove_cap($cap);
        }
    }
}
