<?php

if (!defined('ABSPATH')) {
    exit;
}

class B2B_CRM_Shortcode
{
    const QUERY_VAR = 'b2b_crm_portal';

    public static function register()
    {
        add_shortcode('mon_plugin_crm', array(__CLASS__, 'render'));
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_assets'));
        add_action('init', array(__CLASS__, 'register_portal_route'));
        add_filter('query_vars', array(__CLASS__, 'register_query_var'));
        add_action('template_redirect', array(__CLASS__, 'maybe_render_portal'));
    }

    public static function register_portal_route()
    {
        $slug = self::portal_slug();
        add_rewrite_rule('^' . preg_quote($slug, '/') . '/?$', 'index.php?' . self::QUERY_VAR . '=1', 'top');
    }

    public static function register_query_var($vars)
    {
        $vars[] = self::QUERY_VAR;
        return $vars;
    }

    public static function portal_slug()
    {
        $settings = get_option('b2b_crm_settings', array());
        $slug = !empty($settings['portal_slug']) ? sanitize_title($settings['portal_slug']) : 'crm';

        return $slug ?: 'crm';
    }

    public static function portal_url()
    {
        $pretty_url = home_url('/' . self::portal_slug() . '/');
        if ('' !== (string) get_option('permalink_structure')) {
            return $pretty_url;
        }

        return add_query_arg(self::QUERY_VAR, '1', home_url('/'));
    }

    public static function is_portal_request()
    {
        if ((bool) get_query_var(self::QUERY_VAR)) {
            return true;
        }

        if (isset($_GET[self::QUERY_VAR]) && sanitize_key(wp_unslash($_GET[self::QUERY_VAR])) === '1') {
            return true;
        }

        $request_path = trim((string) wp_parse_url(add_query_arg(array()), PHP_URL_PATH), '/');
        return $request_path === self::portal_slug();
    }

    public static function enqueue_assets()
    {
        $should_enqueue = self::is_portal_request();

        if (!$should_enqueue && is_singular()) {
            global $post;
            $should_enqueue = $post && has_shortcode($post->post_content, 'mon_plugin_crm');
        }

        if (!$should_enqueue) {
            return;
        }

        wp_enqueue_style('dashicons');

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
        $frontend_data = array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('b2b_crm_maroc_nonce'),
        );
        wp_localize_script('b2b-crm-admin', 'B2BCRM', $frontend_data);
        wp_localize_script('b2b-crm-admin', 'b2bCrmMaroc', $frontend_data);
    }

    public static function maybe_render_portal()
    {
        if (!self::is_portal_request()) {
            return;
        }

        self::enqueue_assets();

        status_header(200);
        nocache_headers();
        ?>
        <!doctype html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta charset="<?php bloginfo('charset'); ?>">
            <meta name="viewport" content="width=device-width, initial-scale=1" />
            <?php wp_head(); ?>
        </head>
        <body <?php body_class('b2b-crm-portal crm-app'); ?>>
            <main class="crm-app b2b-crm-app b2b-crm-portal__main">
                <?php echo self::render_portal_content(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </main>
            <?php wp_footer(); ?>
        </body>
        </html>
        <?php
        exit;
    }

    public static function render($atts = array())
    {
        $atts = shortcode_atts(array(
            'mode' => 'embedded',
        ), $atts, 'mon_plugin_crm');

        if ($atts['mode'] === 'portal') {
            return sprintf(
                '<p><a class="button button-primary" href="%s">%s</a></p>',
                esc_url(self::portal_url()),
                esc_html__('Ouvrir le portail CRM', 'b2b-crm-maroc')
            );
        }

        if (!is_user_logged_in()) {
            return sprintf(
                '<div class="b2b-crm-embed"><p>%s</p><p><a class="button" href="%s">%s</a></p></div>',
                esc_html__('Vous devez vous connecter pour accéder au CRM.', 'b2b-crm-maroc'),
                esc_url(wp_login_url(self::portal_url())),
                esc_html__('Se connecter', 'b2b-crm-maroc')
            );
        }

        if (!current_user_can(B2B_CRM_MAROC_ACCESS_CAP)) {
            return '<div class="b2b-crm-embed"><p>' . esc_html__('Accès refusé.', 'b2b-crm-maroc') . '</p></div>';
        }

        return sprintf(
            '<div class="b2b-crm-embed"><p>%s</p><p><a class="button button-primary" href="%s">%s</a></p></div>',
            esc_html__('Le CRM complet s’ouvre dans le portail dédié pour une interface optimale.', 'b2b-crm-maroc'),
            esc_url(self::portal_url()),
            esc_html__('Ouvrir le portail CRM', 'b2b-crm-maroc')
        );
    }

    private static function render_portal_content()
    {
        if (!is_user_logged_in()) {
            return sprintf(
                '<div class="b2b-crm-embed"><h2>%s</h2><p><a class="button button-primary" href="%s">%s</a></p></div>',
                esc_html__('Vous devez vous connecter.', 'b2b-crm-maroc'),
                esc_url(wp_login_url(self::portal_url())),
                esc_html__('Se connecter', 'b2b-crm-maroc')
            );
        }

        if (!current_user_can(B2B_CRM_MAROC_ACCESS_CAP)) {
            return '<div class="b2b-crm-embed"><h2>' . esc_html__('Accès refusé.', 'b2b-crm-maroc') . '</h2></div>';
        }

        ob_start();
        $tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'dashboard';
        B2B_CRM_Lead_List_Page::render($tab);
        return ob_get_clean();
    }
}
