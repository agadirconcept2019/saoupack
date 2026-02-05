<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once B2B_CRM_MAROC_PATH . 'database/class-b2b-crm-lead-table.php';
require_once B2B_CRM_MAROC_PATH . 'database/class-b2b-crm-interaction-table.php';
require_once B2B_CRM_MAROC_PATH . 'database/class-b2b-crm-account-table.php';
require_once B2B_CRM_MAROC_PATH . 'database/class-b2b-crm-contact-table.php';
require_once B2B_CRM_MAROC_PATH . 'database/class-b2b-crm-module-item-table.php';
require_once B2B_CRM_MAROC_PATH . 'services/class-b2b-crm-lead-repository.php';
require_once B2B_CRM_MAROC_PATH . 'services/class-b2b-crm-account-repository.php';
require_once B2B_CRM_MAROC_PATH . 'services/class-b2b-crm-contact-repository.php';
require_once B2B_CRM_MAROC_PATH . 'services/class-b2b-crm-module-item-repository.php';
require_once B2B_CRM_MAROC_PATH . 'services/class-b2b-crm-collector.php';
require_once B2B_CRM_MAROC_PATH . 'services/class-b2b-crm-email-service.php';
require_once B2B_CRM_MAROC_PATH . 'services/class-b2b-crm-interaction-repository.php';
require_once B2B_CRM_MAROC_PATH . 'frontend/class-b2b-crm-shortcode.php';
require_once B2B_CRM_MAROC_PATH . 'admin/class-b2b-crm-admin-menu.php';
require_once B2B_CRM_MAROC_PATH . 'admin/class-b2b-crm-views.php';
require_once B2B_CRM_MAROC_PATH . 'admin/class-b2b-crm-actions.php';
require_once B2B_CRM_MAROC_PATH . 'admin/class-b2b-crm-assets.php';
require_once B2B_CRM_MAROC_PATH . 'api/class-b2b-crm-ajax.php';
require_once B2B_CRM_MAROC_PATH . 'utils/class-b2b-crm-sanitizer.php';

class B2B_CRM_Bootstrap
{
    private static $duplicate_plugins = array();

    public static function init()
    {
        B2B_CRM_Shortcode::register();

        if (is_admin()) {
            self::maybe_detect_duplicate_installs();
            if (!current_user_can(B2B_CRM_MAROC_ACCESS_CAP)) {
                return;
            }

            B2B_CRM_Admin_Menu::register();
            B2B_CRM_Actions::register();
            B2B_CRM_Assets::register();
            B2B_CRM_Ajax::register();
        }
    }

    public static function activate()
    {
        B2B_CRM_Lead_Table::create_table();
        B2B_CRM_Interaction_Table::create_table();
        B2B_CRM_Account_Table::create_table();
        B2B_CRM_Contact_Table::create_table();
        B2B_CRM_Module_Item_Table::create_table();
        self::register_roles();
        self::deactivate_duplicate_plugins();
        self::maybe_upgrade();
        B2B_CRM_Shortcode::register_portal_route();
        flush_rewrite_rules();
    }

    public static function deactivate()
    {
        flush_rewrite_rules();
    }

    private static function register_roles()
    {
        $admin = get_role('administrator');
        if ($admin) {
            $admin->add_cap(B2B_CRM_MAROC_ACCESS_CAP);
            $admin->add_cap(B2B_CRM_MAROC_LEADS_CAP);
            $admin->add_cap(B2B_CRM_MAROC_SETTINGS_CAP);
            $admin->add_cap(B2B_CRM_MAROC_SOURCES_CAP);
            $admin->add_cap(B2B_CRM_MAROC_EMAIL_CAP);
        }

        add_role(
            'b2b_crm_admin',
            __('CRM Admin', 'b2b-crm-maroc'),
            array(
                'read' => true,
                B2B_CRM_MAROC_ACCESS_CAP => true,
                B2B_CRM_MAROC_LEADS_CAP => true,
                B2B_CRM_MAROC_SETTINGS_CAP => true,
                B2B_CRM_MAROC_SOURCES_CAP => true,
                B2B_CRM_MAROC_EMAIL_CAP => true,
            )
        );

        add_role(
            'b2b_crm_agent',
            __('CRM Agent', 'b2b-crm-maroc'),
            array(
                'read' => true,
                B2B_CRM_MAROC_ACCESS_CAP => true,
                B2B_CRM_MAROC_LEADS_CAP => true,
                B2B_CRM_MAROC_EMAIL_CAP => true,
            )
        );
    }


    private static function deactivate_duplicate_plugins()
    {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $current_plugin = plugin_basename(B2B_CRM_MAROC_PATH . 'saoupack-crm.php');

        foreach (get_plugins() as $plugin_file => $plugin_data) {
            if (($plugin_data['Name'] ?? '') !== 'CRM Saoupack' || $plugin_file === $current_plugin) {
                continue;
            }

            if (is_plugin_active($plugin_file)) {
                deactivate_plugins($plugin_file, true);
            }
        }
    }

    private static function maybe_detect_duplicate_installs()
    {
        if (!current_user_can('activate_plugins')) {
            return;
        }

        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $current_plugin = plugin_basename(B2B_CRM_MAROC_PATH . 'saoupack-crm.php');
        $duplicates = array();

        foreach (get_plugins() as $plugin_file => $plugin_data) {
            if (($plugin_data['Name'] ?? '') === 'CRM Saoupack' && $plugin_file !== $current_plugin) {
                $duplicates[] = $plugin_file;
            }
        }

        if (empty($duplicates)) {
            return;
        }

        self::$duplicate_plugins = $duplicates;
        add_action('admin_notices', array(__CLASS__, 'render_duplicate_notice'));
    }

    public static function render_duplicate_notice()
    {
        if (empty(self::$duplicate_plugins) || !current_user_can('activate_plugins')) {
            return;
        }

        echo '<div class="notice notice-warning"><p><strong>' . esc_html__('CRM Saoupack : installation dupliquée détectée.', 'b2b-crm-maroc') . '</strong></p>';
        echo '<p>' . esc_html__("WordPress a trouvé plusieurs dossiers du plugin. Conservez uniquement saoupack-crm/ pour éviter l'exécution d'une ancienne version.", 'b2b-crm-maroc') . '</p>';
        echo '<ul style="list-style:disc;padding-left:20px;">';
        foreach (self::$duplicate_plugins as $plugin_file) {
            echo '<li><code>' . esc_html($plugin_file) . '</code></li>';
        }
        echo '</ul>';
        echo '<p><a class="button button-secondary" href="' . esc_url(admin_url('plugins.php')) . '">' . esc_html__('Ouvrir la page Extensions', 'b2b-crm-maroc') . '</a></p></div>';
    }

    private static function maybe_upgrade()
    {
        $current = get_option('b2b_crm_db_version', '0.0.0');
        if (version_compare($current, B2B_CRM_MAROC_VERSION, '>=')) {
            return;
        }
        B2B_CRM_Lead_Table::create_table();
        B2B_CRM_Interaction_Table::create_table();
        B2B_CRM_Module_Item_Table::create_table();
        update_option('b2b_crm_db_version', B2B_CRM_MAROC_VERSION);
    }
}
