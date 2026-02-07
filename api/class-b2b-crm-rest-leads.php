<?php

if (!defined('ABSPATH')) {
    exit;
}

class B2B_CRM_Rest_Leads
{
    public static function register()
    {
        add_action('rest_api_init', array(__CLASS__, 'register_routes'));
    }

    public static function register_routes()
    {
        register_rest_route(
            'b2b-crm/v1',
            '/leads',
            array(
                array(
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => array(__CLASS__, 'list_leads'),
                    'permission_callback' => array(__CLASS__, 'can_access'),
                ),
                array(
                    'methods' => WP_REST_Server::CREATABLE,
                    'callback' => array(__CLASS__, 'create_lead'),
                    'permission_callback' => array(__CLASS__, 'can_access'),
                ),
            )
        );

        register_rest_route(
            'b2b-crm/v1',
            '/leads/(?P<id>\d+)',
            array(
                array(
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => array(__CLASS__, 'get_lead'),
                    'permission_callback' => array(__CLASS__, 'can_access'),
                ),
                array(
                    'methods' => WP_REST_Server::EDITABLE,
                    'callback' => array(__CLASS__, 'update_lead'),
                    'permission_callback' => array(__CLASS__, 'can_access'),
                ),
                array(
                    'methods' => WP_REST_Server::DELETABLE,
                    'callback' => array(__CLASS__, 'delete_lead'),
                    'permission_callback' => array(__CLASS__, 'can_access'),
                ),
            )
        );

        register_rest_route(
            'b2b-crm/v1',
            '/leads/(?P<id>\d+)/send-email',
            array(
                array(
                    'methods' => WP_REST_Server::CREATABLE,
                    'callback' => array(__CLASS__, 'send_email'),
                    'permission_callback' => array(__CLASS__, 'can_access'),
                ),
            )
        );
    }

    public static function can_access()
    {
        return B2B_CRM_Capabilities::can_access_admin();
    }

    public static function list_leads(WP_REST_Request $request)
    {
        $filters = array(
            'search' => sanitize_text_field($request->get_param('search') ?? ''),
            'status' => sanitize_key($request->get_param('status') ?? ''),
            'interest_level' => sanitize_key($request->get_param('interest_level') ?? ''),
            'stage' => sanitize_text_field($request->get_param('stage') ?? ''),
        );

        if ($filters['status'] && !B2B_CRM_Sanitizer::is_valid_status($filters['status'])) {
            return new WP_REST_Response(array('message' => __('Statut invalide.', 'b2b-crm-maroc')), 400);
        }

        if ($filters['interest_level'] && !B2B_CRM_Sanitizer::is_valid_interest_level($filters['interest_level'])) {
            return new WP_REST_Response(array('message' => __('Priorité invalide.', 'b2b-crm-maroc')), 400);
        }

        if ($filters['stage'] && !B2B_CRM_Sanitizer::is_valid_stage($filters['stage'])) {
            return new WP_REST_Response(array('message' => __('Étape invalide.', 'b2b-crm-maroc')), 400);
        }

        $page = absint($request->get_param('page') ?? 1);
        $per_page = absint($request->get_param('per_page') ?? 20);
        $per_page = min(100, max(1, $per_page));

        $result = B2B_CRM_Lead_Repository::list($filters, $page, $per_page);

        return new WP_REST_Response($result, 200);
    }

    public static function create_lead(WP_REST_Request $request)
    {
        $raw = $request->get_json_params();
        if (!is_array($raw)) {
            $raw = $request->get_params();
        }

        $validation = self::validate_enums($raw);
        if (is_wp_error($validation)) {
            return new WP_REST_Response(array('message' => $validation->get_error_message()), 400);
        }

        $data = B2B_CRM_Sanitizer::lead_fields($raw);
        if (empty($data['company_name'])) {
            return new WP_REST_Response(array('message' => __('company_name requis.', 'b2b-crm-maroc')), 400);
        }

        $lead_id = B2B_CRM_Lead_Repository::upsert($data);
        $lead = B2B_CRM_Lead_Repository::get($lead_id);

        return new WP_REST_Response($lead, 201);
    }

    public static function get_lead(WP_REST_Request $request)
    {
        $lead_id = absint($request['id']);
        $lead = B2B_CRM_Lead_Repository::get($lead_id);

        if (!$lead) {
            return new WP_REST_Response(array('message' => __('Lead introuvable.', 'b2b-crm-maroc')), 404);
        }

        return new WP_REST_Response($lead, 200);
    }

    public static function update_lead(WP_REST_Request $request)
    {
        $lead_id = absint($request['id']);
        $lead = B2B_CRM_Lead_Repository::get($lead_id);

        if (!$lead) {
            return new WP_REST_Response(array('message' => __('Lead introuvable.', 'b2b-crm-maroc')), 404);
        }

        $raw = $request->get_json_params();
        if (!is_array($raw)) {
            $raw = $request->get_params();
        }

        $validation = self::validate_enums($raw);
        if (is_wp_error($validation)) {
            return new WP_REST_Response(array('message' => $validation->get_error_message()), 400);
        }

        $data = B2B_CRM_Sanitizer::lead_fields($raw);
        if (empty($data)) {
            return new WP_REST_Response(array('message' => __('Aucune donnée valide à mettre à jour.', 'b2b-crm-maroc')), 400);
        }

        B2B_CRM_Lead_Repository::update($lead_id, $data);

        return new WP_REST_Response(B2B_CRM_Lead_Repository::get($lead_id), 200);
    }

    public static function delete_lead(WP_REST_Request $request)
    {
        $lead_id = absint($request['id']);
        $lead = B2B_CRM_Lead_Repository::get($lead_id);

        if (!$lead) {
            return new WP_REST_Response(array('message' => __('Lead introuvable.', 'b2b-crm-maroc')), 404);
        }

        B2B_CRM_Lead_Repository::delete($lead_id);

        return new WP_REST_Response(array('deleted' => true), 200);
    }

    public static function send_email(WP_REST_Request $request)
    {
        if (!B2B_CRM_Capabilities::can_send_email()) {
            return new WP_REST_Response(array('message' => __('Accès refusé.', 'b2b-crm-maroc')), 403);
        }

        $lead_id = absint($request['id']);
        $lead = B2B_CRM_Lead_Repository::get($lead_id);

        if (!$lead) {
            return new WP_REST_Response(array('message' => __('Lead introuvable.', 'b2b-crm-maroc')), 404);
        }

        $raw = $request->get_json_params();
        if (!is_array($raw)) {
            $raw = $request->get_params();
        }

        $subject = sanitize_text_field($raw['subject'] ?? '');
        $message = wp_kses_post($raw['message'] ?? '');

        if ($subject === '' || $message === '') {
            return new WP_REST_Response(array('message' => __('Sujet et message requis.', 'b2b-crm-maroc')), 400);
        }

        $result = B2B_CRM_Email_Service::send($lead, $subject, $message);
        if (is_wp_error($result)) {
            return new WP_REST_Response(array('message' => $result->get_error_message()), 400);
        }

        return new WP_REST_Response(array('sent' => true), 200);
    }

    private static function validate_enums(array $raw)
    {
        if (isset($raw['status']) && $raw['status'] !== '' && !B2B_CRM_Sanitizer::is_valid_status($raw['status'])) {
            return new WP_Error('b2b_crm_invalid_status', __('Statut invalide.', 'b2b-crm-maroc'));
        }

        if (isset($raw['interest_level']) && $raw['interest_level'] !== '' && !B2B_CRM_Sanitizer::is_valid_interest_level($raw['interest_level'])) {
            return new WP_Error('b2b_crm_invalid_interest', __('Priorité invalide.', 'b2b-crm-maroc'));
        }

        if (isset($raw['stage']) && $raw['stage'] !== '' && !B2B_CRM_Sanitizer::is_valid_stage($raw['stage'])) {
            return new WP_Error('b2b_crm_invalid_stage', __('Étape invalide.', 'b2b-crm-maroc'));
        }

        if (isset($raw['status'])) {
            $key_values = get_option('b2b_crm_key_values', array());
            $locked_statuses = isset($key_values['status_locked']) && is_array($key_values['status_locked'])
                ? array_map('sanitize_key', $key_values['status_locked'])
                : array();
            if (in_array(sanitize_key($raw['status']), $locked_statuses, true)) {
                return new WP_Error('b2b_crm_locked_status', __('Ce statut est verrouillé.', 'b2b-crm-maroc'));
            }
        }

        return true;
    }
}
