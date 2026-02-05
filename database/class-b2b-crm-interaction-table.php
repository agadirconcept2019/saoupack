<?php

if (!defined('ABSPATH')) {
    exit;
}

class B2B_CRM_Interaction_Table
{
    public static function table_name()
    {
        global $wpdb;
        return $wpdb->prefix . 'b2b_lead_interactions';
    }

    public static function create_table()
    {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $table = self::table_name();
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            lead_id bigint(20) unsigned NOT NULL,
            user_id bigint(20) unsigned NOT NULL DEFAULT 0,
            interaction_type varchar(50) NOT NULL,
            content longtext NOT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY lead_id (lead_id),
            KEY user_id (user_id),
            KEY interaction_type (interaction_type)
        ) {$charset};";

        dbDelta($sql);
    }
}
