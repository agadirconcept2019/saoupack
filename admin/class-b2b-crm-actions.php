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
            'Email',
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
                $row['email'],
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

        add_settings_error('b2b-crm-maroc', 'collect_started', __('Collecte lancée. Un rapport sera disponible après exécution.', 'b2b-crm-maroc'), 'updated');
        wp_safe_redirect(admin_url('admin.php?page=b2b-crm-maroc&tab=collect'));
        exit;
    }
}
