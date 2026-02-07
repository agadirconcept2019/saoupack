<?php

if (!defined('ABSPATH')) {
    exit;
}

class Saoupack_B2B_Collector_Leads_Service
{
    public static function list(array $filters = array(), $page = 1, $per_page = 20)
    {
        global $wpdb;

        $table = Saoupack_B2B_Collector_Leads_Table::table_name();
        $where = array();
        $params = array();

        if (!empty($filters['search'])) {
            $like = '%' . $wpdb->esc_like($filters['search']) . '%';
            $where[] = '(company_name LIKE %s OR email LIKE %s OR phone LIKE %s OR city LIKE %s)';
            array_push($params, $like, $like, $like, $like);
        }

        if (!empty($filters['crm_status'])) {
            $where[] = 'crm_status = %s';
            $params[] = $filters['crm_status'];
        }

        if (!empty($filters['priority'])) {
            $where[] = 'priority = %s';
            $params[] = $filters['priority'];
        }

        $where_sql = '';
        if (!empty($where)) {
            $where_sql = 'WHERE ' . implode(' AND ', $where);
        }

        $page = max(1, (int) $page);
        $per_page = min(100, max(1, (int) $per_page));
        $offset = ($page - 1) * $per_page;

        $query = "SELECT * FROM {$table} {$where_sql} ORDER BY updated_at DESC LIMIT %d OFFSET %d";
        $params[] = $per_page;
        $params[] = $offset;

        $items = $wpdb->get_results($wpdb->prepare($query, $params), ARRAY_A);

        $count_query = "SELECT COUNT(*) FROM {$table} {$where_sql}";
        $count_params = array_slice($params, 0, max(0, count($params) - 2));
        if (!empty($count_params)) {
            $count_query = $wpdb->prepare($count_query, $count_params);
        }
        $total = (int) $wpdb->get_var($count_query);

        return array(
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page,
        );
    }

    public static function get($id)
    {
        global $wpdb;

        $table = Saoupack_B2B_Collector_Leads_Table::table_name();
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $id), ARRAY_A);
    }

    public static function create(array $data)
    {
        global $wpdb;

        $table = Saoupack_B2B_Collector_Leads_Table::table_name();
        $now = current_time('mysql');

        $payload = array_merge(
            array(
                'company_name' => '',
                'city' => '',
                'phone' => '',
                'email' => '',
                'website' => '',
                'social_links' => '',
                'priority' => 'medium',
                'crm_status' => 'new',
                'source' => '',
                'notes' => '',
            ),
            $data
        );

        $payload['created_at'] = $now;
        $payload['updated_at'] = $now;

        $wpdb->insert($table, $payload);

        return (int) $wpdb->insert_id;
    }

    public static function update($id, array $data)
    {
        global $wpdb;

        $table = Saoupack_B2B_Collector_Leads_Table::table_name();
        $data['updated_at'] = current_time('mysql');

        return (bool) $wpdb->update($table, $data, array('id' => $id));
    }

    public static function delete($id)
    {
        global $wpdb;

        $table = Saoupack_B2B_Collector_Leads_Table::table_name();
        return (bool) $wpdb->delete($table, array('id' => $id));
    }

    public static function send_email($id, array $payload)
    {
        $lead = self::get($id);
        if (empty($lead) || empty($lead['email'])) {
            return new WP_Error('saoupack_b2b_missing_email', __('Lead introuvable ou email manquant.', 'saoupack-b2b-collector'));
        }

        $subject = sanitize_text_field($payload['subject'] ?? '');
        $message = wp_kses_post($payload['message'] ?? '');

        if ($subject === '' || $message === '') {
            return new WP_Error('saoupack_b2b_invalid_email', __('Sujet et message requis.', 'saoupack-b2b-collector'));
        }

        $sent = wp_mail($lead['email'], $subject, $message);
        if (!$sent) {
            return new WP_Error('saoupack_b2b_email_failed', __('Échec de l’envoi de l’email.', 'saoupack-b2b-collector'));
        }

        return true;
    }
}
