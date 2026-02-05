<?php

if (!defined('ABSPATH')) {
    exit;
}

class B2B_CRM_Lead_Table
{
    public static function table_name()
    {
        global $wpdb;
        return $wpdb->prefix . 'b2b_leads';
    }

    public static function create_table()
    {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $table = self::table_name();
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            company_name varchar(190) NOT NULL,
            sector varchar(190) DEFAULT '' NOT NULL,
            city varchar(190) DEFAULT '' NOT NULL,
            contact_name varchar(190) DEFAULT '' NOT NULL,
            contact_role varchar(190) DEFAULT '' NOT NULL,
            phone varchar(40) DEFAULT '' NOT NULL,
            phone_mobile varchar(40) DEFAULT '' NOT NULL,
            email varchar(190) DEFAULT '' NOT NULL,
            website varchar(190) DEFAULT '' NOT NULL,
            social_json longtext NULL,
            status enum('new','qualified','contacted','inactive') NOT NULL DEFAULT 'new',
            stage varchar(100) DEFAULT '' NOT NULL,
            owner_user_id bigint(20) unsigned NOT NULL DEFAULT 0,
            last_contact datetime NULL,
            next_action text NULL,
            follow_up_date date NULL,
            interest_level enum('low','medium','high') NOT NULL DEFAULT 'low',
            tags text NULL,
            notes longtext NULL,
            source varchar(190) DEFAULT '' NOT NULL,
            collected_at datetime NOT NULL,
            collected_method varchar(190) DEFAULT '' NOT NULL,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY status (status),
            KEY stage (stage),
            KEY owner_user_id (owner_user_id),
            KEY city (city),
            KEY sector (sector),
            KEY company_name (company_name),
            KEY email (email),
            KEY phone (phone)
        ) {$charset};";

        dbDelta($sql);
    }
}
