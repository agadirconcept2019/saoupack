<?php

if (!defined('ABSPATH')) {
    exit;
}

class B2B_CRM_Contact_Activity_Repository
{
    public static function list($contact_id, $type = '')
    {
        global $wpdb;

        $table = B2B_CRM_Contact_Activity_Table::table_name();
        $query = "SELECT * FROM {$table} WHERE contact_id = %d";
        $params = array((int) $contact_id);

        if (!empty($type)) {
            $query .= ' AND activity_type = %s';
            $params[] = sanitize_key($type);
        }

        $query .= ' ORDER BY happened_at DESC, id DESC';

        return $wpdb->get_results($wpdb->prepare($query, $params), ARRAY_A);
    }

    public static function insert(array $data)
    {
        global $wpdb;

        $table = B2B_CRM_Contact_Activity_Table::table_name();
        $now = current_time('mysql');

        $payload = array(
            'contact_id' => absint($data['contact_id'] ?? 0),
            'activity_type' => sanitize_key($data['activity_type'] ?? 'note'),
            'summary' => sanitize_textarea_field($data['summary'] ?? ''),
            'owner_user_id' => absint($data['owner_user_id'] ?? get_current_user_id()),
            'happened_at' => !empty($data['happened_at']) ? sanitize_text_field($data['happened_at']) : $now,
            'created_at' => $now,
        );

        $wpdb->insert($table, $payload);

        return (int) $wpdb->insert_id;
    }
}
