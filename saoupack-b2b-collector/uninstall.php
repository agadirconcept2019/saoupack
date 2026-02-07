<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

$table = $wpdb->prefix . 'saoupack_leads';
$wpdb->query("DROP TABLE IF EXISTS {$table}");

$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'saoupack_b2b_%'");
