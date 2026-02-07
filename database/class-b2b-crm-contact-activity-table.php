<?php

if (!defined('ABSPATH')) {
    exit;
}

class B2B_CRM_Contact_Activity_Table
{
    public static function table_name()
    {
        global $wpdb;
        return $wpdb->prefix . 'b2b_contact_activities';
    }

    public static function create_table()
    {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $table = self::table_name();
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            contact_id bigint(20) unsigned NOT NULL,
            activity_type varchar(50) NOT NULL,
            summary text NOT NULL,
            owner_user_id bigint(20) unsigned DEFAULT 0 NOT NULL,
            happened_at datetime NOT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY contact_id (contact_id),
            KEY activity_type (activity_type),
            KEY happened_at (happened_at)
        ) {$charset};";

        dbDelta($sql);
    }
}
