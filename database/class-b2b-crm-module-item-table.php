<?php

if (!defined('ABSPATH')) {
    exit;
}

class B2B_CRM_Module_Item_Table
{
    public static function table_name()
    {
        global $wpdb;
        return $wpdb->prefix . 'b2b_module_items';
    }

    public static function create_table()
    {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $table = self::table_name();
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            module_key varchar(60) NOT NULL,
            title varchar(190) NOT NULL,
            status varchar(50) DEFAULT '' NOT NULL,
            owner varchar(190) DEFAULT '' NOT NULL,
            amount decimal(12,2) DEFAULT 0 NOT NULL,
            due_date date NULL,
            meta_json longtext NULL,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY module_key (module_key),
            KEY status (status)
        ) {$charset};";

        dbDelta($sql);
    }
}
