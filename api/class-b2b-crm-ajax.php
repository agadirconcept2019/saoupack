<?php

if (!defined('ABSPATH')) {
    exit;
}

class B2B_CRM_Ajax
{
    public static function register()
    {
        add_action('wp_ajax_b2b_crm_quick_update', array(__CLASS__, 'quick_update'));
    }

    public static function quick_update()
    {
        if (!current_user_can(B2B_CRM_MAROC_CAP)) {
            wp_send_json_error(array('message' => __('Accès refusé.', 'b2b-crm-maroc')));
        }

        check_ajax_referer('b2b_crm_maroc_nonce', 'nonce');

        $lead_id = isset($_POST['lead_id']) ? absint($_POST['lead_id']) : 0;
        $field = isset($_POST['field']) ? sanitize_key($_POST['field']) : '';
        $value = isset($_POST['value']) ? sanitize_text_field(wp_unslash($_POST['value'])) : '';

        if (!$lead_id || !in_array($field, array('status', 'interest_level'), true)) {
            wp_send_json_error(array('message' => __('Données invalides.', 'b2b-crm-maroc')));
        }

        B2B_CRM_Lead_Repository::update($lead_id, array($field => $value));

        wp_send_json_success(array('message' => __('Mise à jour effectuée.', 'b2b-crm-maroc')));
    }
}
