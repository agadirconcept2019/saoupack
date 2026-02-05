<?php

if (!defined('ABSPATH')) {
    exit;
}

class B2B_CRM_Interaction_Repository
{
    public static function add($lead_id, $type, $content, $user_id = 0)
    {
        global $wpdb;

        $table = B2B_CRM_Interaction_Table::table_name();
        $wpdb->insert(
            $table,
            array(
                'lead_id' => $lead_id,
                'user_id' => (int) $user_id,
                'interaction_type' => $type,
                'content' => $content,
                'created_at' => current_time('mysql'),
            )
        );

        return (int) $wpdb->insert_id;
    }

    public static function list($lead_id)
    {
        global $wpdb;

        $table = B2B_CRM_Interaction_Table::table_name();
        $query = $wpdb->prepare("SELECT * FROM {$table} WHERE lead_id = %d ORDER BY created_at DESC", $lead_id);

        return $wpdb->get_results($query, ARRAY_A);
    }
}
