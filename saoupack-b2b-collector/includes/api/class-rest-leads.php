<?php

if (!defined('ABSPATH')) {
    exit;
}

class Saoupack_B2B_Collector_Rest_Leads
{
    public static function register()
    {
        add_action('rest_api_init', array(__CLASS__, 'register_routes'));
    }

    public static function register_routes()
    {
        register_rest_route(
            'saoupack-b2b/v1',
            '/leads',
            array(
                array(
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => array(__CLASS__, 'list_leads'),
                    'permission_callback' => array(__CLASS__, 'permission_check'),
                ),
                array(
                    'methods' => WP_REST_Server::CREATABLE,
                    'callback' => array(__CLASS__, 'create_lead'),
                    'permission_callback' => array(__CLASS__, 'permission_check'),
                ),
            )
        );

        register_rest_route(
            'saoupack-b2b/v1',
            '/leads/(?P<id>\d+)',
            array(
                array(
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => array(__CLASS__, 'get_lead'),
                    'permission_callback' => array(__CLASS__, 'permission_check'),
                ),
                array(
                    'methods' => WP_REST_Server::EDITABLE,
                    'callback' => array(__CLASS__, 'update_lead'),
                    'permission_callback' => array(__CLASS__, 'permission_check'),
                ),
                array(
                    'methods' => WP_REST_Server::DELETABLE,
                    'callback' => array(__CLASS__, 'delete_lead'),
                    'permission_callback' => array(__CLASS__, 'permission_check'),
                ),
            )
        );

        register_rest_route(
            'saoupack-b2b/v1',
            '/leads/(?P<id>\d+)/send-email',
            array(
                array(
                    'methods' => WP_REST_Server::CREATABLE,
                    'callback' => array(__CLASS__, 'send_email'),
                    'permission_callback' => array(__CLASS__, 'permission_check'),
                ),
            )
        );
    }

    public static function permission_check()
    {
        return current_user_can(SAOUPACK_B2B_COLLECTOR_CAP);
    }

    public static function list_leads(WP_REST_Request $request)
    {
        $filters = array(
            'search' => sanitize_text_field($request->get_param('search') ?? ''),
            'crm_status' => sanitize_key($request->get_param('status') ?? ''),
            'priority' => sanitize_key($request->get_param('priority') ?? ''),
        );

        if ($filters['crm_status'] && !Saoupack_B2B_Collector_Sanitizer::valid_status($filters['crm_status'])) {
            $filters['crm_status'] = '';
        }

        if ($filters['priority'] && !Saoupack_B2B_Collector_Sanitizer::valid_priority($filters['priority'])) {
            $filters['priority'] = '';
        }

        $page = absint($request->get_param('page') ?? 1);
        $per_page = absint($request->get_param('per_page') ?? 20);
        $per_page = min(100, max(1, $per_page));

        $result = Saoupack_B2B_Collector_Leads_Service::list($filters, $page, $per_page);

        return new WP_REST_Response($result, 200);
    }

    public static function create_lead(WP_REST_Request $request)
    {
        $raw = $request->get_json_params();
        if (!is_array($raw)) {
            $raw = $request->get_params();
        }

        $data = Saoupack_B2B_Collector_Sanitizer::sanitize_lead($raw);

        if (empty($data['company_name'])) {
            return new WP_REST_Response(array('message' => __('company_name requis.', 'saoupack-b2b-collector')), 400);
        }

        if (!empty($data['priority']) && !Saoupack_B2B_Collector_Sanitizer::valid_priority($data['priority'])) {
            return new WP_REST_Response(array('message' => __('Priorité invalide.', 'saoupack-b2b-collector')), 400);
        }

        if (!empty($data['crm_status']) && !Saoupack_B2B_Collector_Sanitizer::valid_status($data['crm_status'])) {
            return new WP_REST_Response(array('message' => __('Statut CRM invalide.', 'saoupack-b2b-collector')), 400);
        }

        $id = Saoupack_B2B_Collector_Leads_Service::create($data);

        return new WP_REST_Response(Saoupack_B2B_Collector_Leads_Service::get($id), 201);
    }

    public static function get_lead(WP_REST_Request $request)
    {
        $id = Saoupack_B2B_Collector_Sanitizer::sanitize_id($request['id']);
        $lead = Saoupack_B2B_Collector_Leads_Service::get($id);

        if (!$lead) {
            return new WP_REST_Response(array('message' => __('Lead introuvable.', 'saoupack-b2b-collector')), 404);
        }

        return new WP_REST_Response($lead, 200);
    }

    public static function update_lead(WP_REST_Request $request)
    {
        $id = Saoupack_B2B_Collector_Sanitizer::sanitize_id($request['id']);
        $lead = Saoupack_B2B_Collector_Leads_Service::get($id);

        if (!$lead) {
            return new WP_REST_Response(array('message' => __('Lead introuvable.', 'saoupack-b2b-collector')), 404);
        }

        $raw = $request->get_json_params();
        if (!is_array($raw)) {
            $raw = $request->get_params();
        }

        $data = Saoupack_B2B_Collector_Sanitizer::sanitize_lead($raw, true);

        if (isset($data['priority']) && $data['priority'] && !Saoupack_B2B_Collector_Sanitizer::valid_priority($data['priority'])) {
            return new WP_REST_Response(array('message' => __('Priorité invalide.', 'saoupack-b2b-collector')), 400);
        }

        if (isset($data['crm_status']) && $data['crm_status'] && !Saoupack_B2B_Collector_Sanitizer::valid_status($data['crm_status'])) {
            return new WP_REST_Response(array('message' => __('Statut CRM invalide.', 'saoupack-b2b-collector')), 400);
        }

        Saoupack_B2B_Collector_Leads_Service::update($id, $data);

        return new WP_REST_Response(Saoupack_B2B_Collector_Leads_Service::get($id), 200);
    }

    public static function delete_lead(WP_REST_Request $request)
    {
        $id = Saoupack_B2B_Collector_Sanitizer::sanitize_id($request['id']);
        $lead = Saoupack_B2B_Collector_Leads_Service::get($id);

        if (!$lead) {
            return new WP_REST_Response(array('message' => __('Lead introuvable.', 'saoupack-b2b-collector')), 404);
        }

        Saoupack_B2B_Collector_Leads_Service::delete($id);

        return new WP_REST_Response(array('deleted' => true), 200);
    }

    public static function send_email(WP_REST_Request $request)
    {
        $id = Saoupack_B2B_Collector_Sanitizer::sanitize_id($request['id']);
        $raw = $request->get_json_params();
        if (!is_array($raw)) {
            $raw = $request->get_params();
        }

        $result = Saoupack_B2B_Collector_Leads_Service::send_email($id, $raw);

        if (is_wp_error($result)) {
            return new WP_REST_Response(array('message' => $result->get_error_message()), 400);
        }

        return new WP_REST_Response(array('sent' => true), 200);
    }
}
