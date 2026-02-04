<?php

if (!defined('ABSPATH')) {
    exit;
}

class B2B_CRM_Actions
{
    public static function register()
    {
        add_action('admin_post_b2b_crm_export_csv', array(__CLASS__, 'export_csv'));
        add_action('admin_post_b2b_crm_delete_lead', array(__CLASS__, 'delete_lead'));
        add_action('admin_post_b2b_crm_run_collect', array(__CLASS__, 'run_collect'));
        add_action('admin_post_b2b_crm_save_sources', array(__CLASS__, 'save_sources'));
        add_action('admin_post_b2b_crm_add_demo_leads', array(__CLASS__, 'add_demo_leads'));
    }

    public static function export_csv()
    {
        if (!current_user_can(B2B_CRM_MAROC_CAP)) {
            wp_die(__('Accès refusé.', 'b2b-crm-maroc'));
        }

        check_admin_referer('b2b_crm_export_csv');

        $filters = array(
            'search' => isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '',
            'status' => isset($_GET['status']) ? sanitize_key($_GET['status']) : '',
            'city' => isset($_GET['city']) ? sanitize_text_field(wp_unslash($_GET['city'])) : '',
            'sector' => isset($_GET['sector']) ? sanitize_text_field(wp_unslash($_GET['sector'])) : '',
            'interest_level' => isset($_GET['interest_level']) ? sanitize_key($_GET['interest_level']) : '',
        );

        $rows = B2B_CRM_Lead_Repository::list_all($filters);

        nocache_headers();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=b2b-crm-leads.csv');

        $output = fopen('php://output', 'w');
        fputcsv($output, array(
            'ID',
            'Société',
            'Secteur',
            'Ville',
            'Contact',
            'Fonction',
            'Téléphone',
            'GSM',
            'Email',
            'Site web',
            'Statut',
            'Intérêt',
            'Source',
            'Collecté',
        ));

        foreach ($rows as $row) {
            fputcsv($output, array(
                $row['id'],
                $row['company_name'],
                $row['sector'],
                $row['city'],
                $row['contact_name'],
                $row['contact_role'],
                $row['phone'],
                $row['phone_mobile'],
                $row['email'],
                $row['website'],
                $row['status'],
                $row['interest_level'],
                $row['source'],
                $row['collected_at'],
            ));
        }

        fclose($output);
        exit;
    }

    public static function delete_lead()
    {
        if (!current_user_can(B2B_CRM_MAROC_CAP)) {
            wp_die(__('Accès refusé.', 'b2b-crm-maroc'));
        }

        check_admin_referer('b2b_crm_delete_lead');

        $lead_id = isset($_GET['lead_id']) ? absint($_GET['lead_id']) : 0;
        if ($lead_id) {
            B2B_CRM_Lead_Repository::delete($lead_id);
            add_settings_error('b2b-crm-maroc', 'lead_deleted', __('Lead supprimé.', 'b2b-crm-maroc'), 'updated');
        }

        wp_safe_redirect(admin_url('admin.php?page=b2b-crm-maroc&tab=base'));
        exit;
    }

    public static function run_collect()
    {
        if (!current_user_can(B2B_CRM_MAROC_CAP)) {
            wp_die(__('Accès refusé.', 'b2b-crm-maroc'));
        }

        check_admin_referer('b2b_crm_run_collect');

        $city = isset($_POST['city']) ? sanitize_text_field(wp_unslash($_POST['city'])) : '';
        $sector = isset($_POST['sector']) ? sanitize_text_field(wp_unslash($_POST['sector'])) : '';
        $precision = isset($_POST['precision']) ? sanitize_key($_POST['precision']) : 'standard';
        $sources = isset($_POST['sources']) && is_array($_POST['sources']) ? array_map('sanitize_key', wp_unslash($_POST['sources'])) : array();

        update_option('b2b_crm_collect_config', array(
            'city' => $city,
            'sector' => $sector,
            'precision' => in_array($precision, array('standard', 'deep'), true) ? $precision : 'standard',
            'sources' => $sources,
        ));

        $sources_config = get_option('b2b_crm_sources_config', array());
        $results = B2B_CRM_Collector::run($city, $sector, $precision, $sources_config, $sources);

        $summary_parts = array();
        foreach ($results['counts'] as $source_key => $count) {
            $summary_parts[] = sprintf('%s: %d', $source_key, $count);
        }

        $message = __('Collecte lancée. Configuration enregistrée.', 'b2b-crm-maroc');
        if (!empty($summary_parts)) {
            $message .= ' ' . __('Résultats', 'b2b-crm-maroc') . ': ' . implode(', ', $summary_parts) . '.';
        }

        add_settings_error('b2b-crm-maroc', 'collect_started', $message, 'updated');

        foreach ($results['errors'] as $error_message) {
            add_settings_error('b2b-crm-maroc', 'collect_error_' . md5($error_message), $error_message, 'error');
        }
        wp_safe_redirect(admin_url('admin.php?page=b2b-crm-maroc&tab=collect'));
        exit;
    }

    public static function save_sources()
    {
        if (!current_user_can(B2B_CRM_MAROC_CAP)) {
            wp_die(__('Accès refusé.', 'b2b-crm-maroc'));
        }

        check_admin_referer('b2b_crm_save_sources');

        $raw_sources = isset($_POST['sources']) && is_array($_POST['sources']) ? wp_unslash($_POST['sources']) : array();
        $source_keys = array(
            'google_maps',
            'directories',
            'social',
            'domains',
            'institutions',
        );
        $clean_sources = array();

        foreach ($source_keys as $key) {
            $source = isset($raw_sources[$key]) && is_array($raw_sources[$key]) ? $raw_sources[$key] : array();
            $clean_sources[$key] = array(
                'enabled' => !empty($source['enabled']),
                'api_key' => isset($source['api_key']) ? sanitize_text_field($source['api_key']) : '',
                'endpoint' => isset($source['endpoint']) ? esc_url_raw($source['endpoint']) : '',
                'ai_model' => isset($source['ai_model']) ? sanitize_text_field($source['ai_model']) : '',
                'notes' => isset($source['notes']) ? sanitize_textarea_field($source['notes']) : '',
                'options' => isset($source['options']) ? sanitize_textarea_field($source['options']) : '',
            );
        }

        update_option('b2b_crm_sources_config', $clean_sources);

        add_settings_error('b2b-crm-maroc', 'sources_saved', __('Sources enregistrées.', 'b2b-crm-maroc'), 'updated');
        wp_safe_redirect(admin_url('admin.php?page=b2b-crm-maroc&tab=sources'));
        exit;
    }

    public static function add_demo_leads()
    {
        if (!current_user_can(B2B_CRM_MAROC_CAP)) {
            wp_die(__('Accès refusé.', 'b2b-crm-maroc'));
        }

        check_admin_referer('b2b_crm_add_demo_leads');

        $demo_leads = array(
            array(
                'company_name' => 'Atlas Architectes',
                'sector' => 'Architecte',
                'city' => 'Casablanca',
                'contact_name' => 'Salma El Amrani',
                'contact_role' => 'Directrice',
                'phone' => '+212 522-123456',
                'phone_mobile' => '+212 661-000111',
                'email' => 'contact@atlas-architectes.ma',
                'website' => 'https://atlas-architectes.ma',
                'social_json' => wp_json_encode(array(
                    'facebook' => 'https://facebook.com/atlasarchitectes',
                    'instagram' => 'https://instagram.com/atlasarchitectes',
                    'linkedin' => 'https://linkedin.com/company/atlasarchitectes',
                )),
                'status' => 'qualified',
                'interest_level' => 'high',
                'source' => 'google_maps',
                'collected_method' => 'Google Places API',
            ),
            array(
                'company_name' => 'Sahara Hôtel',
                'sector' => 'Hôtel & Tourisme',
                'city' => 'Marrakech',
                'contact_name' => 'Youssef Benali',
                'contact_role' => 'Responsable Marketing',
                'phone' => '+212 524-555000',
                'phone_mobile' => '+212 662-456789',
                'email' => 'marketing@sahara-hotel.ma',
                'website' => 'https://sahara-hotel.ma',
                'social_json' => wp_json_encode(array(
                    'facebook' => 'https://facebook.com/saharahotel',
                    'instagram' => 'https://instagram.com/saharahotel',
                )),
                'status' => 'new',
                'interest_level' => 'medium',
                'source' => 'directories',
                'collected_method' => 'Annuaire Maroc',
            ),
            array(
                'company_name' => 'Médina Santé',
                'sector' => 'Clinique Privée',
                'city' => 'Rabat',
                'contact_name' => 'Dr. Hanae Idrissi',
                'contact_role' => 'Direction médicale',
                'phone' => '+212 537-220220',
                'phone_mobile' => '+212 660-889900',
                'email' => 'contact@medinasante.ma',
                'website' => 'https://medinasante.ma',
                'social_json' => wp_json_encode(array(
                    'linkedin' => 'https://linkedin.com/company/medinasante',
                )),
                'status' => 'contacted',
                'interest_level' => 'high',
                'source' => 'social',
                'collected_method' => 'Social scraping',
            ),
            array(
                'company_name' => 'Garage Al Atlas',
                'sector' => 'Garage Automobile',
                'city' => 'Agadir',
                'contact_name' => 'Noureddine Ait',
                'contact_role' => 'Gérant',
                'phone' => '+212 528-339900',
                'phone_mobile' => '+212 663-990011',
                'email' => 'contact@garageatlas.ma',
                'website' => 'https://garageatlas.ma',
                'social_json' => wp_json_encode(array(
                    'facebook' => 'https://facebook.com/garageatlas',
                )),
                'status' => 'qualified',
                'interest_level' => 'medium',
                'source' => 'domains',
                'collected_method' => 'WHOIS scan',
            ),
            array(
                'company_name' => 'Notaires du Nord',
                'sector' => 'Notaire',
                'city' => 'Tanger',
                'contact_name' => 'Khadija Benjelloun',
                'contact_role' => 'Associée',
                'phone' => '+212 539-901122',
                'phone_mobile' => '+212 664-112233',
                'email' => 'contact@notairesnord.ma',
                'website' => 'https://notairesnord.ma',
                'social_json' => wp_json_encode(array(
                    'linkedin' => 'https://linkedin.com/company/notairesnord',
                )),
                'status' => 'new',
                'interest_level' => 'low',
                'source' => 'institutions',
                'collected_method' => 'Portail institutionnel',
            ),
        );

        foreach ($demo_leads as $lead) {
            B2B_CRM_Lead_Repository::upsert($lead);
        }

        add_settings_error('b2b-crm-maroc', 'demo_added', __('Données de démonstration ajoutées.', 'b2b-crm-maroc'), 'updated');
        wp_safe_redirect(admin_url('admin.php?page=b2b-crm-maroc&tab=base'));
        exit;
    }
}
