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

        if (!empty($filters['stage'])) {
            $where[] = 'stage = %s';
            $params[] = $filters['stage'];
        }

        if (!empty($filters['owner_user_id'])) {
            $where[] = 'owner_user_id = %d';
            $params[] = (int) $filters['owner_user_id'];
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

    public static function list_all(array $filters = array())
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

        if (!empty($filters['stage'])) {
            $where[] = 'stage = %s';
            $params[] = $filters['stage'];
        }

        if (!empty($filters['owner_user_id'])) {
            $where[] = 'owner_user_id = %d';
            $params[] = (int) $filters['owner_user_id'];
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

        $query = "SELECT * FROM {$table} {$where_sql} ORDER BY updated_at DESC";
        if (!empty($params)) {
            $query = $wpdb->prepare($query, $params);
        }

        return $wpdb->get_results($query, ARRAY_A);
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
            'phone_mobile' => '',
            'email' => '',
            'website' => '',
            'social_json' => null,
            'status' => 'new',
            'stage' => '',
            'owner_user_id' => 0,
            'last_contact' => null,
            'next_action' => null,
            'follow_up_date' => null,
            'interest_level' => 'low',
            'tags' => null,
            'notes' => null,
            'source' => '',
            'collected_at' => $now,
            'collected_method' => '',
        );

        $payload = array_merge($defaults, $data);
        $payload['email'] = self::normalize_email($payload['email']);
        $payload['phone'] = self::normalize_phone($payload['phone']);
        $payload['phone_mobile'] = self::normalize_phone($payload['phone_mobile']);
        if (empty($payload['stage'])) {
            $stages = self::stages();
            $payload['stage'] = $stages ? $stages[0] : '';
        }
        if (empty($payload['owner_user_id'])) {
            $payload['owner_user_id'] = get_current_user_id();
        }
        $payload['updated_at'] = $now;

        $lead_id = self::find_duplicate_id($payload);

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
        if (isset($data['email'])) {
            $data['email'] = self::normalize_email($data['email']);
        }
        if (isset($data['phone'])) {
            $data['phone'] = self::normalize_phone($data['phone']);
        }
        if (isset($data['phone_mobile'])) {
            $data['phone_mobile'] = self::normalize_phone($data['phone_mobile']);
        }
        $data['updated_at'] = current_time('mysql');

        return $wpdb->update($table, $data, array('id' => $lead_id));
    }

    public static function delete($lead_id)
    {
        global $wpdb;
        $table = B2B_CRM_Lead_Table::table_name();
        return $wpdb->delete($table, array('id' => $lead_id));
    }

    public static function add_interaction($lead_id, $type, $content)
    {
        return B2B_CRM_Interaction_Repository::add($lead_id, $type, $content);
    }

    public static function key_values(array $fields, $limit = 50)
    {
        global $wpdb;

        $table = B2B_CRM_Lead_Table::table_name();
        $allowed_fields = array('company_name', 'contact_name', 'email', 'phone', 'phone_mobile', 'website', 'status', 'interest_level');
        $sanitized_fields = array_values(array_intersect($fields, $allowed_fields));
        $values = array();
        $reference = get_option('b2b_crm_key_values', array());

        foreach ($sanitized_fields as $field) {
            $query = "SELECT DISTINCT {$field} FROM {$table} WHERE {$field} <> '' ORDER BY updated_at DESC LIMIT %d";
            $results = $wpdb->get_col($wpdb->prepare($query, $limit));
            $values[$field] = array_values(array_filter($results));
            if (!empty($reference[$field]) && is_array($reference[$field])) {
                $values[$field] = array_values(array_unique(array_merge($reference[$field], $values[$field])));
            }
        }

        foreach ($reference as $field => $items) {
            if (!in_array($field, $fields, true) || isset($values[$field])) {
                continue;
            }
            $values[$field] = is_array($items) ? array_values(array_unique($items)) : array();
        }

        return $values;
    }

    public static function find_duplicate_id(array $payload)
    {
        global $wpdb;

        $table = B2B_CRM_Lead_Table::table_name();
        $checks = array();
        $params = array();

        $module_settings = get_option('b2b_crm_module_settings', array());
        $raw_fields = isset($module_settings['dedup_fields']) ? (string) $module_settings['dedup_fields'] : 'email,phone,company_city';
        $enabled_fields = array_filter(array_map('trim', explode(',', $raw_fields)));
        if (empty($enabled_fields)) {
            $enabled_fields = array('email', 'phone', 'company_city');
        }

        if (in_array('email', $enabled_fields, true) && !empty($payload['email'])) {
            $checks[] = 'email = %s';
            $params[] = $payload['email'];
        }

        if (in_array('phone', $enabled_fields, true) && !empty($payload['phone'])) {
            $checks[] = 'phone = %s';
            $params[] = $payload['phone'];
        }

        if (in_array('company_city', $enabled_fields, true) && !empty($payload['company_name']) && !empty($payload['city'])) {
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

    public static function stages()
    {
        $settings = get_option('b2b_crm_module_settings', array());
        $raw = isset($settings['opportunities_stages']) ? $settings['opportunities_stages'] : '';
        if (!$raw) {
            return array();
        }
        $parts = array_filter(array_map('trim', explode(',', $raw)));
        return array_values(array_unique($parts));
    }

    private static function normalize_email($value)
    {
        return $value ? strtolower(trim($value)) : '';
    }

    private static function normalize_phone($value)
    {
        if (empty($value)) {
            return '';
        }
        $digits = preg_replace('/\D+/', '', (string) $value);
        return $digits ?: trim($value);
    }
}
