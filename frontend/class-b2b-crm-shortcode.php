<?php

if (!defined('ABSPATH')) {
    exit;
}

class B2B_CRM_Shortcode
{
    public static function register()
    {
        add_shortcode('mon_plugin_crm', array(__CLASS__, 'render'));
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_assets'));
    }

    public static function enqueue_assets()
    {
        if (!is_singular()) {
            return;
        }

        global $post;
        if ($post && has_shortcode($post->post_content, 'mon_plugin_crm')) {
            wp_enqueue_style(
                'b2b-crm-admin',
                B2B_CRM_MAROC_URL . 'assets/css/admin.css',
                array(),
                B2B_CRM_MAROC_VERSION
            );
            wp_enqueue_script(
                'b2b-crm-admin',
                B2B_CRM_MAROC_URL . 'assets/js/admin.js',
                array('jquery'),
                B2B_CRM_MAROC_VERSION,
                true
            );
            wp_localize_script('b2b-crm-admin', 'b2bCrmMaroc', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('b2b_crm_maroc_nonce'),
            ));
        }
    }

    public static function render()
    {
        if (!is_user_logged_in()) {
            return '<p>' . esc_html__('Veuillez vous connecter pour accéder au CRM.', 'b2b-crm-maroc') . '</p>';
        }

        if (!current_user_can(B2B_CRM_MAROC_ACCESS_CAP)) {
            return '<p>' . esc_html__('Accès refusé.', 'b2b-crm-maroc') . '</p>';
        }
        ob_start();
        $tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'base';
        B2B_CRM_Lead_List_Page::render($tab);
        return ob_get_clean();
    }
}
