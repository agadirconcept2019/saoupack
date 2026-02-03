<?php

if (!defined('ABSPATH')) {
    exit;
}

class B2B_CRM_Lead_Repository
{
    public static function list(array $filters = array(), $paged = 1, $per_page = 20)
    {
        global $wpdb;

        $table = B2B_CRM_Lead_Table::table_name();
        $where = array();
        $params = array();

        if (!empty($filters['search'])) {
            $like = '%' . $wpdb->esc_like($filters['search']) . '%';
            $where[] = '(company_name LIKE %s OR email LIKE %s OR phone LIKE %s OR city LIKE %s)';
            array_push($params, $like, $like, $like, $like);
        }

        if (!empty($filters['status'])) {
            $where[] = 'status = %s';
            $params[] = $filters['status'];
        }

        if (!empty($filters['city'])) {
            $where[] = 'city = %s';
            $params[] = $filters['city'];
        }

        if (!empty($filters['sector'])) {
            $where[] = 'sector = %s';
            $params[] = $filters['sector'];
        }

        if (!empty($filters['interest_level'])) {
            $where[] = 'interest_level = %s';
            $params[] = $filters['interest_level'];
        }

        $where_sql = '';
        if (!empty($where)) {
            $where_sql = 'WHERE ' . implode(' AND ', $where);
        }

        $offset = max(0, ($paged - 1) * $per_page);
        $limit = $per_page;

        $query = "SELECT * FROM {$table} {$where_sql} ORDER BY updated_at DESC LIMIT %d OFFSET %d";
        $params[] = $limit;
        $params[] = $offset;

        $prepared = $wpdb->prepare($query, $params);
        $items = $wpdb->get_results($prepared, ARRAY_A);

        $count_query = "SELECT COUNT(*) FROM {$table} {$where_sql}";
        $count_params = array_slice($params, 0, max(0, count($params) - 2));
        if (!empty($count_params)) {
            $count_query = $wpdb->prepare($count_query, $count_params);
        }
        $total = (int) $wpdb->get_var($count_query);

        return array(
            'items' => $items,
            'total' => $total,
        );
    }

    public static function get($lead_id)
    {
        global $wpdb;
        $table = B2B_CRM_Lead_Table::table_name();
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $lead_id), ARRAY_A);
    }

    public static function upsert(array $data)
    {
        global $wpdb;

        $table = B2B_CRM_Lead_Table::table_name();
        $now = current_time('mysql');

        $defaults = array(
            'company_name' => '',
            'sector' => '',
            'city' => '',
            'contact_name' => '',
            'contact_role' => '',
            'phone' => '',
            'email' => '',
            'social_json' => null,
            'status' => 'new',
            'last_contact' => null,
            'next_action' => null,
            'follow_up_date' => null,
            'interest_level' => 'low',
            'notes' => null,
            'source' => '',
            'collected_at' => $now,
            'collected_method' => '',
        );

        $payload = array_merge($defaults, $data);
        $payload['updated_at'] = $now;

        $lead_id = self::find_duplicate($payload);

        if ($lead_id) {
            $wpdb->update($table, $payload, array('id' => $lead_id));
            return $lead_id;
        }

        $payload['created_at'] = $now;
        $wpdb->insert($table, $payload);

        return (int) $wpdb->insert_id;
    }

    public static function update($lead_id, array $data)
    {
        global $wpdb;

        $table = B2B_CRM_Lead_Table::table_name();
        $data['updated_at'] = current_time('mysql');

        return $wpdb->update($table, $data, array('id' => $lead_id));
    }

    public static function add_interaction($lead_id, $type, $content)
    {
        return B2B_CRM_Interaction_Repository::add($lead_id, $type, $content);
    }

    private static function find_duplicate(array $payload)
    {
        global $wpdb;

        $table = B2B_CRM_Lead_Table::table_name();
        $checks = array();
        $params = array();

        if (!empty($payload['email'])) {
            $checks[] = 'email = %s';
            $params[] = $payload['email'];
        }

        if (!empty($payload['phone'])) {
            $checks[] = 'phone = %s';
            $params[] = $payload['phone'];
        }

        if (!empty($payload['company_name']) && !empty($payload['city'])) {
            $checks[] = '(company_name = %s AND city = %s)';
            $params[] = $payload['company_name'];
            $params[] = $payload['city'];
        }

        if (empty($checks)) {
            return 0;
        }

        $where = implode(' OR ', $checks);
        $query = "SELECT id FROM {$table} WHERE {$where} ORDER BY updated_at DESC LIMIT 1";
        $found = $wpdb->get_var($wpdb->prepare($query, $params));

        return (int) $found;
    }
}
