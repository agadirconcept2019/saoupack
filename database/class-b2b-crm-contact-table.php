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
            first_name varchar(190) DEFAULT '' NOT NULL,
            last_name varchar(190) DEFAULT '' NOT NULL,
            company varchar(190) DEFAULT '' NOT NULL,
            role varchar(190) DEFAULT '' NOT NULL,
            email varchar(190) DEFAULT '' NOT NULL,
            phone varchar(40) DEFAULT '' NOT NULL,
            whatsapp varchar(40) DEFAULT '' NOT NULL,
            linkedin_url varchar(255) DEFAULT '' NOT NULL,
            city varchar(190) DEFAULT '' NOT NULL,
            source varchar(100) DEFAULT 'manual' NOT NULL,
            relationship_status varchar(60) DEFAULT 'prospect' NOT NULL,
            owner_user_id bigint(20) unsigned DEFAULT 0 NOT NULL,
            status enum('active','inactive') NOT NULL DEFAULT 'active',
            tags_json longtext NULL,
            notes longtext NULL,
            last_contact_at datetime NULL,
            next_action varchar(190) DEFAULT '' NOT NULL,
            next_followup_at datetime NULL,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY full_name (full_name),
            KEY email (email),
            KEY phone (phone),
            KEY owner_user_id (owner_user_id),
            KEY next_followup_at (next_followup_at),
            KEY relationship_status (relationship_status),
            KEY city (city),
            KEY status (status)
        ) {$charset};";

        dbDelta($sql);
    }
}
