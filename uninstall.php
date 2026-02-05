<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

$lead_table = $wpdb->prefix . 'b2b_leads';
$interaction_table = $wpdb->prefix . 'b2b_lead_interactions';
$account_table = $wpdb->prefix . 'b2b_accounts';
$contact_table = $wpdb->prefix . 'b2b_contacts';
$module_item_table = $wpdb->prefix . 'b2b_module_items';

$wpdb->query("DROP TABLE IF EXISTS {$lead_table}");
$wpdb->query("DROP TABLE IF EXISTS {$interaction_table}");
$wpdb->query("DROP TABLE IF EXISTS {$account_table}");
$wpdb->query("DROP TABLE IF EXISTS {$contact_table}");
$wpdb->query("DROP TABLE IF EXISTS {$module_item_table}");

remove_role('b2b_crm_agent');

$admin = get_role('administrator');
if ($admin && $admin->has_cap('b2b_crm_manage_leads')) {
    $admin->remove_cap('b2b_crm_manage_leads');
}
