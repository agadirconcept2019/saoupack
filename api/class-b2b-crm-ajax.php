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
        if (!B2B_CRM_Capabilities::can_manage_leads()) {
            wp_send_json_error(array('message' => __('Accès refusé.', 'b2b-crm-maroc')));
        }

        check_ajax_referer('b2b_crm_maroc_nonce', 'nonce');

        $lead_id = isset($_POST['lead_id']) ? absint($_POST['lead_id']) : 0;
        $field = isset($_POST['field']) ? sanitize_key($_POST['field']) : '';
        $raw_value = isset($_POST['value']) ? wp_unslash($_POST['value']) : '';
        $value = $field === 'stage' ? sanitize_text_field($raw_value) : sanitize_key($raw_value);
        $allowed = array(
            'status' => B2B_CRM_Sanitizer::allowed_statuses(),
            'interest_level' => B2B_CRM_Sanitizer::allowed_interest_levels(),
            'stage' => B2B_CRM_Sanitizer::allowed_stages(),
        );

        if (!$lead_id || !isset($allowed[$field])) {
            wp_send_json_error(array('message' => __('Données invalides.', 'b2b-crm-maroc')));
        }

        if ($field === 'stage') {
            if (!B2B_CRM_Sanitizer::is_valid_stage($value, $allowed['stage'])) {
                wp_send_json_error(array('message' => __('Étape invalide.', 'b2b-crm-maroc')));
            }
        } elseif (!in_array($value, $allowed[$field], true)) {
            wp_send_json_error(array('message' => __('Données invalides.', 'b2b-crm-maroc')));
        }

        $key_values = get_option('b2b_crm_key_values', array());
        $locked_statuses = isset($key_values['status_locked']) && is_array($key_values['status_locked'])
            ? array_map('sanitize_key', $key_values['status_locked'])
            : array();
        if ($field === 'status' && in_array($value, $locked_statuses, true)) {
            wp_send_json_error(array('message' => __('Ce statut est verrouillé.', 'b2b-crm-maroc')));
        }

        B2B_CRM_Lead_Repository::update($lead_id, array($field => $value));

        wp_send_json_success(array('message' => __('Mise à jour effectuée.', 'b2b-crm-maroc')));
    }
}
