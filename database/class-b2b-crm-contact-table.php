<?php

if (!defined('ABSPATH')) {
    exit;
}

class B2B_CRM_Contact_Table
{
    public static function table_name()
    {
        global $wpdb;
        return $wpdb->prefix . 'b2b_contacts';
    }

    public static function create_table()
    {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $table = self::table_name();
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            full_name varchar(190) NOT NULL,
            company varchar(190) DEFAULT '' NOT NULL,
            role varchar(190) DEFAULT '' NOT NULL,
            email varchar(190) DEFAULT '' NOT NULL,
            phone varchar(40) DEFAULT '' NOT NULL,
            city varchar(190) DEFAULT '' NOT NULL,
            status enum('active','inactive') NOT NULL DEFAULT 'active',
            notes longtext NULL,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY full_name (full_name),
            KEY city (city),
            KEY status (status)
        ) {$charset};";

        dbDelta($sql);
    }
}
