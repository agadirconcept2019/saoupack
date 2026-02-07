<?php

if (!defined('ABSPATH')) {
    exit;
}

class Saoupack_B2B_Collector_Leads_Table
{
    public static function table_name()
    {
        global $wpdb;
        return $wpdb->prefix . 'saoupack_leads';
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
            city varchar(190) DEFAULT '' NOT NULL,
            phone varchar(40) DEFAULT '' NOT NULL,
            email varchar(190) DEFAULT '' NOT NULL,
            website varchar(190) DEFAULT '' NOT NULL,
            social_links longtext NULL,
            priority enum('high','medium') NOT NULL DEFAULT 'medium',
            crm_status enum('new','qualified','contact_initiated','proposal_sent','negotiation','won','lost') NOT NULL DEFAULT 'new',
            source varchar(190) DEFAULT '' NOT NULL,
            notes longtext NULL,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY company_name (company_name),
            KEY email (email),
            KEY phone (phone),
            KEY priority (priority),
            KEY crm_status (crm_status),
            KEY created_at (created_at)
        ) {$charset};";

        dbDelta($sql);
    }

    public static function maybe_migrate_from_legacy()
    {
        global $wpdb;

        $new_table = self::table_name();
        $legacy_table = $wpdb->prefix . 'b2b_leads';

        $new_exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $new_table));
        if (!$new_exists) {
            return;
        }

        $legacy_exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $legacy_table));
        if (!$legacy_exists) {
            return;
        }

        $new_count = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$new_table}");
        if ($new_count > 0) {
            return;
        }

        $legacy_rows = $wpdb->get_results("SELECT * FROM {$legacy_table}", ARRAY_A);
        if (empty($legacy_rows)) {
            return;
        }

        foreach ($legacy_rows as $row) {
            $status_map = array(
                'new' => 'new',
                'qualified' => 'qualified',
                'contacted' => 'contact_initiated',
                'inactive' => 'lost',
            );
            $priority_map = array(
                'high' => 'high',
                'medium' => 'medium',
                'low' => 'medium',
            );

            $legacy_status = isset($row['status']) ? $row['status'] : 'new';
            $legacy_priority = isset($row['interest_level']) ? $row['interest_level'] : 'medium';

            $crm_status = $status_map[$legacy_status] ?? 'new';
            $priority = $priority_map[$legacy_priority] ?? 'medium';

            $social_links = '';
            if (!empty($row['social_json'])) {
                $decoded = json_decode($row['social_json'], true);
                if (is_array($decoded)) {
                    $filtered = array();
                    foreach ($decoded as $key => $value) {
                        $filtered[$key] = $value;
                    }
                    if (!empty($filtered)) {
                        $social_links = wp_json_encode($filtered);
                    }
                }
            }

            $created_at = !empty($row['created_at']) ? $row['created_at'] : current_time('mysql');
            $updated_at = !empty($row['updated_at']) ? $row['updated_at'] : current_time('mysql');

            $payload = array(
                'company_name' => $row['company_name'] ?? '',
                'city' => $row['city'] ?? '',
                'phone' => $row['phone'] ?? '',
                'email' => $row['email'] ?? '',
                'website' => $row['website'] ?? '',
                'social_links' => $social_links,
                'priority' => $priority,
                'crm_status' => $crm_status,
                'source' => $row['source'] ?? '',
                'notes' => $row['notes'] ?? '',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            );

            $wpdb->insert($new_table, $payload);
        }
    }
}
