<?php

if (!defined('ABSPATH')) {
    exit;
}

class B2B_CRM_Contact_Repository
{
    public static function list(array $filters = array(), $paged = 1, $per_page = 20)
    {
        global $wpdb;

        $table = B2B_CRM_Contact_Table::table_name();
        $where = array('1=1');
        $params = array();

        if (!empty($filters['search'])) {
            $like = '%' . $wpdb->esc_like($filters['search']) . '%';
            $where[] = '(full_name LIKE %s OR email LIKE %s OR phone LIKE %s OR company LIKE %s)';
            array_push($params, $like, $like, $like, $like);
        }

        if (!empty($filters['status'])) {
            $where[] = 'status = %s';
            $params[] = $filters['status'];
        }

        if (!empty($filters['relationship_status'])) {
            $where[] = 'relationship_status = %s';
            $params[] = $filters['relationship_status'];
        }

        if (!empty($filters['owner_user_id'])) {
            $where[] = 'owner_user_id = %d';
            $params[] = (int) $filters['owner_user_id'];
        }

        if (!empty($filters['source'])) {
            $where[] = 'source = %s';
            $params[] = $filters['source'];
        }

        if (!empty($filters['followup'])) {
            $today_start = current_time('Y-m-d 00:00:00');
            $today_end = current_time('Y-m-d 23:59:59');
            $next_seven = date('Y-m-d 23:59:59', strtotime(current_time('mysql') . ' +7 days'));
            if ($filters['followup'] === 'overdue') {
                $where[] = "next_followup_at IS NOT NULL AND next_followup_at < %s";
                $params[] = $today_start;
            } elseif ($filters['followup'] === 'today') {
                $where[] = "next_followup_at BETWEEN %s AND %s";
                $params[] = $today_start;
                $params[] = $today_end;
            } elseif ($filters['followup'] === 'week') {
                $where[] = "next_followup_at BETWEEN %s AND %s";
                $params[] = $today_start;
                $params[] = $next_seven;
            }
        }

        $where_sql = implode(' AND ', $where);
        $offset = max(0, ($paged - 1) * $per_page);

        $count_sql = "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}";
        $total = (int) $wpdb->get_var(!empty($params) ? $wpdb->prepare($count_sql, $params) : $count_sql);

        $data_sql = "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY updated_at DESC LIMIT %d OFFSET %d";
        $params_with_pagination = array_merge($params, array($per_page, $offset));
        $items = $wpdb->get_results($wpdb->prepare($data_sql, $params_with_pagination), ARRAY_A);

        return array(
            'items' => $items,
            'total' => $total,
        );
    }

    public static function get($contact_id)
    {
        global $wpdb;
        $table = B2B_CRM_Contact_Table::table_name();
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $contact_id), ARRAY_A);
    }

    public static function find_duplicate($email, $phone, $exclude_id = 0)
    {
        global $wpdb;

        $table = B2B_CRM_Contact_Table::table_name();
        $checks = array();
        $params = array();

        if (!empty($email)) {
            $checks[] = 'email = %s';
            $params[] = strtolower(trim($email));
        }

        if (!empty($phone)) {
            $checks[] = 'phone = %s';
            $params[] = preg_replace('/\D+/', '', (string) $phone);
        }

        if (empty($checks)) {
            return 0;
        }

        $where = '(' . implode(' OR ', $checks) . ')';
        if ($exclude_id > 0) {
            $where .= ' AND id <> %d';
            $params[] = (int) $exclude_id;
        }

        $query = "SELECT id FROM {$table} WHERE {$where} ORDER BY updated_at DESC LIMIT 1";
        return (int) $wpdb->get_var($wpdb->prepare($query, $params));
    }

    public static function insert(array $data)
    {
        global $wpdb;

        $table = B2B_CRM_Contact_Table::table_name();
        $now = current_time('mysql');

        $payload = self::normalize_payload($data);
        $payload['created_at'] = $now;
        $payload['updated_at'] = $now;

        $wpdb->insert($table, $payload);

        return (int) $wpdb->insert_id;
    }

    public static function update($contact_id, array $data)
    {
        global $wpdb;

        $table = B2B_CRM_Contact_Table::table_name();
        $payload = self::normalize_payload($data);
        $payload['updated_at'] = current_time('mysql');

        return $wpdb->update($table, $payload, array('id' => $contact_id));
    }

    public static function delete($contact_id)
    {
        global $wpdb;
        $table = B2B_CRM_Contact_Table::table_name();
        return $wpdb->delete($table, array('id' => $contact_id));
    }

    public static function relationship_statuses()
    {
        return array(
            'prospect' => __('Prospect', 'b2b-crm-maroc'),
            'client' => __('Client', 'b2b-crm-maroc'),
            'partner' => __('Partenaire', 'b2b-crm-maroc'),
            'supplier' => __('Fournisseur', 'b2b-crm-maroc'),
        );
    }

    public static function source_options()
    {
        return array(
            'manual' => __('Manuel', 'b2b-crm-maroc'),
            'import_csv' => __('Import CSV', 'b2b-crm-maroc'),
            'google_maps' => __('Google Maps', 'b2b-crm-maroc'),
            'linkedin' => __('LinkedIn', 'b2b-crm-maroc'),
            'other' => __('Autre', 'b2b-crm-maroc'),
        );
    }

    private static function normalize_payload(array $data)
    {
        $tags = isset($data['tags']) && is_array($data['tags']) ? $data['tags'] : array();
        $tags = array_slice(array_values(array_filter(array_map('sanitize_text_field', $tags))), 0, 20);

        $full_name = trim((string) ($data['full_name'] ?? ''));
        $first_name = trim((string) ($data['first_name'] ?? ''));
        $last_name = trim((string) ($data['last_name'] ?? ''));

        if ($full_name === '' && ($first_name !== '' || $last_name !== '')) {
            $full_name = trim($first_name . ' ' . $last_name);
        }

        return array(
            'full_name' => $full_name,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'company' => sanitize_text_field($data['company'] ?? ''),
            'role' => sanitize_text_field($data['role'] ?? ''),
            'email' => strtolower(trim(sanitize_email($data['email'] ?? ''))),
            'phone' => preg_replace('/\D+/', '', (string) ($data['phone'] ?? '')),
            'whatsapp' => preg_replace('/\D+/', '', (string) ($data['whatsapp'] ?? '')),
            'linkedin_url' => esc_url_raw($data['linkedin_url'] ?? ''),
            'city' => sanitize_text_field($data['city'] ?? ''),
            'source' => sanitize_key($data['source'] ?? 'manual'),
            'relationship_status' => sanitize_key($data['relationship_status'] ?? 'prospect'),
            'owner_user_id' => absint($data['owner_user_id'] ?? 0),
            'status' => sanitize_key($data['status'] ?? 'active'),
            'tags_json' => !empty($tags) ? wp_json_encode($tags) : null,
            'notes' => sanitize_textarea_field($data['notes'] ?? ''),
            'last_contact_at' => !empty($data['last_contact_at']) ? sanitize_text_field($data['last_contact_at']) : null,
            'next_action' => sanitize_text_field($data['next_action'] ?? ''),
            'next_followup_at' => !empty($data['next_followup_at']) ? sanitize_text_field($data['next_followup_at']) : null,
        );
    }
}
