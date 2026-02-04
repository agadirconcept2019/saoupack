<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

$lead_table = $wpdb->prefix . 'b2b_leads';
$interaction_table = $wpdb->prefix . 'b2b_lead_interactions';

$wpdb->query("DROP TABLE IF EXISTS {$lead_table}");
$wpdb->query("DROP TABLE IF EXISTS {$interaction_table}");

remove_role('b2b_crm_agent');

$admin = get_role('administrator');
if ($admin && $admin->has_cap('b2b_crm_manage_leads')) {
    $admin->remove_cap('b2b_crm_manage_leads');
}
