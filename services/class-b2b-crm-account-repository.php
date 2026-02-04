<?php

if (!defined('ABSPATH')) {
    exit;
}

class B2B_CRM_Account_Repository
{
    public static function list(array $filters = array(), $paged = 1, $per_page = 20)
    {
        global $wpdb;

        $table = B2B_CRM_Account_Table::table_name();
        $where = array('1=1');
        $params = array();

        if (!empty($filters['search'])) {
            $like = '%' . $wpdb->esc_like($filters['search']) . '%';
            $where[] = '(name LIKE %s OR email LIKE %s OR phone LIKE %s OR city LIKE %s)';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        if (!empty($filters['status'])) {
            $where[] = 'status = %s';
            $params[] = $filters['status'];
        }

        $where_sql = implode(' AND ', $where);
        $offset = ($paged - 1) * $per_page;

        $count_sql = "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}";
        $total = (int) $wpdb->get_var($wpdb->prepare($count_sql, $params));

        $data_sql = "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $params_with_pagination = array_merge($params, array($per_page, $offset));
        $items = $wpdb->get_results($wpdb->prepare($data_sql, $params_with_pagination), ARRAY_A);

        return array(
            'items' => $items,
            'total' => $total,
        );
    }

    public static function insert(array $data)
    {
        global $wpdb;

        $table = B2B_CRM_Account_Table::table_name();
        $now = current_time('mysql');

        $payload = array(
            'name' => $data['name'],
            'industry' => $data['industry'],
            'city' => $data['city'],
            'website' => $data['website'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'owner' => $data['owner'],
            'status' => $data['status'],
            'notes' => $data['notes'],
            'created_at' => $now,
            'updated_at' => $now,
        );

        $wpdb->insert($table, $payload);

        return (int) $wpdb->insert_id;
    }
}
