<?php

if (!defined('ABSPATH')) {
    exit;
}

class B2B_CRM_Lead_List_Page
{
    public static function render($tab = 'dashboard')
    {
        $lead_id = isset($_GET['lead_id']) ? absint($_GET['lead_id']) : 0;
        if ($lead_id) {
            B2B_CRM_Lead_Detail_Page::render($lead_id);
            return;
        }

        $filters = array(
            'search' => isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '',
            'status' => isset($_GET['status']) ? sanitize_key($_GET['status']) : '',
            'stage' => isset($_GET['stage']) ? sanitize_text_field(wp_unslash($_GET['stage'])) : '',
            'city' => isset($_GET['city']) ? sanitize_text_field(wp_unslash($_GET['city'])) : '',
            'sector' => isset($_GET['sector']) ? sanitize_text_field(wp_unslash($_GET['sector'])) : '',
            'interest_level' => isset($_GET['interest_level']) ? sanitize_key($_GET['interest_level']) : '',
        );
        $filters['owner_user_id'] = isset($_GET['owner']) && $_GET['owner'] === 'me' ? get_current_user_id() : 0;

        $paged = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
        $per_page = 20;
        $data = B2B_CRM_Lead_Repository::list($filters, $paged, $per_page);
        $total_pages = (int) ceil($data['total'] / $per_page);

        $tabs = array(
            'dashboard' => __('Accueil', 'b2b-crm-maroc'),
            'accounts' => __('Entreprise', 'b2b-crm-maroc'),
            'contacts' => __('Contacts', 'b2b-crm-maroc'),
            'base' => __('Prospects', 'b2b-crm-maroc'),
            'tasks' => __('Activités', 'b2b-crm-maroc'),
            'emails' => __('Emails', 'b2b-crm-maroc'),
            'collect' => __('Collecte', 'b2b-crm-maroc'),
            'sources' => __('Sources', 'b2b-crm-maroc'),
            'pipeline' => __('Pipeline', 'b2b-crm-maroc'),
            'settings' => __('Paramétrage', 'b2b-crm-maroc'),
        );
        $modules_config = self::modules_config();
        $modules_state = get_option('b2b_crm_modules_config', array());
        $modules_state = wp_parse_args($modules_state, $modules_config);
        $settings = get_option('b2b_crm_settings', array());
        $workspace_name = !empty($settings['workspace_name']) ? $settings['workspace_name'] : __('CRM Saoupack', 'b2b-crm-maroc');

        $nav_sections = array(
            array(
                'label' => __('CRM', 'b2b-crm-maroc'),
                'items' => array(
                    array('key' => 'contacts', 'icon' => 'dashicons-id'),
                    array('key' => 'base', 'icon' => 'dashicons-groups'),
                    array('key' => 'pipeline', 'icon' => 'dashicons-chart-line'),
                ),
            ),
            array(
                'label' => __('Activités', 'b2b-crm-maroc'),
                'items' => array(
                    array('key' => 'emails', 'icon' => 'dashicons-email'),
                    array('key' => 'tasks', 'icon' => 'dashicons-yes-alt'),
                ),
            ),
            array(
                'label' => __('Outils', 'b2b-crm-maroc'),
                'items' => array(
                    array('key' => 'collect', 'icon' => 'dashicons-filter'),
                    array('key' => 'sources', 'icon' => 'dashicons-admin-links'),
                    array('key' => 'settings', 'icon' => 'dashicons-admin-generic'),
                ),
            ),
        );

        $current_label = $tabs[$tab] ?? $tabs['dashboard'];
        $base_url = self::base_url();

        $is_admin_user = current_user_can('manage_options');
        if ($tab !== 'dashboard' && $tab !== 'settings' && $tab !== 'accounts' && isset($modules_state[$tab]) && empty($modules_state[$tab]) && !$is_admin_user) {
            $tab = 'module-disabled';
            $current_label = __('Module désactivé', 'b2b-crm-maroc');
        }
        ?>
        <div class="wrap crm-app b2b-crm b2b-crm--app">
            <?php settings_errors('b2b-crm-maroc'); ?>
            <div class="b2b-crm__shell">
                <aside class="crm-sidebar b2b-crm__sidebar">
                    <div class="b2b-crm__sidebar-brand">
                        <span class="b2b-crm__logo">
                            <img src="<?php echo esc_url(B2B_CRM_MAROC_URL . 'assets/images/saoupack-icon.svg'); ?>" alt="<?php echo esc_attr__('CRM Saoupack', 'b2b-crm-maroc'); ?>" />
                        </span>
                        <div>
                            <strong><?php echo esc_html($workspace_name); ?></strong>
                            <div class="b2b-crm__subtitle"><?php echo esc_html__('Workspace CRM', 'b2b-crm-maroc'); ?></div>
                        </div>
                    </div>
                    <nav class="b2b-crm__sidebar-nav">
                        <a class="b2b-crm__nav-link <?php echo $tab === 'dashboard' ? 'is-active' : ''; ?>" href="<?php echo esc_url(add_query_arg(array('page' => 'b2b-crm-maroc', 'tab' => 'dashboard'), $base_url)); ?>">
                            <span class="dashicons dashicons-admin-home" aria-hidden="true"></span>
                            <?php echo esc_html($tabs['dashboard']); ?>
                        </a>
                        <?php foreach ($nav_sections as $section) : ?>
                            <div class="b2b-crm__nav-section">
                                <span class="b2b-crm__nav-title"><?php echo esc_html($section['label']); ?></span>
                                <?php foreach ($section['items'] as $item) : ?>
                                    <?php
                                    $key = $item['key'];
                                    $label = $tabs[$key] ?? $key;
                                    $is_enabled = !empty($modules_state[$key]);
                                    $link_target = $is_enabled ? add_query_arg(array('page' => 'b2b-crm-maroc', 'tab' => $key), $base_url) : add_query_arg(array('page' => 'b2b-crm-maroc', 'tab' => 'settings'), $base_url);
                                    ?>
                                    <a class="b2b-crm__nav-link <?php echo $tab === $key ? 'is-active' : ''; ?> <?php echo $is_enabled ? '' : 'is-disabled'; ?>" href="<?php echo esc_url($link_target); ?>" <?php echo $is_enabled ? '' : 'aria-disabled="true"'; ?>>
                                        <span class="dashicons <?php echo esc_attr($item['icon']); ?>" aria-hidden="true"></span>
                                        <?php echo esc_html($label); ?>
                                        <?php if (!$is_enabled) : ?>
                                            <span class="b2b-crm__nav-badge"><?php echo esc_html__('Off', 'b2b-crm-maroc'); ?></span>
                                        <?php endif; ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </nav>
                </aside>
                <div class="crm-content b2b-crm__content">
                    <div class="crm-topbar b2b-crm__topbar">
                        <div class="b2b-crm__page-title">
                            <h1><?php echo esc_html($current_label); ?></h1>
                            <p><?php echo esc_html__('Workspace CRM', 'b2b-crm-maroc'); ?></p>
                            <div class="b2b-crm__breadcrumb">CRM / <?php echo esc_html($current_label); ?></div>
                        </div>
                        <div class="b2b-crm__topbar-actions">
                            <button class="b2b-crm__settings-link b2b-crm__sidebar-toggle" type="button" aria-label="<?php echo esc_attr__('Réduire menu', 'b2b-crm-maroc'); ?>">
                                <span class="dashicons dashicons-menu"></span>
                            </button>
                            <label class="b2b-crm__search">
                                <span class="dashicons dashicons-search" aria-hidden="true"></span>
                                <input type="search" placeholder="<?php echo esc_attr__('Recherche globale', 'b2b-crm-maroc'); ?>" />
                            </label>
                            <button class="b2b-crm__settings-link" type="button" aria-label="<?php echo esc_attr__('Notifications', 'b2b-crm-maroc'); ?>">
                                <span class="dashicons dashicons-bell" aria-hidden="true"></span>
                            </button>
                            <a class="b2b-crm__settings-link" href="<?php echo esc_url(add_query_arg(array('page' => 'b2b-crm-maroc', 'tab' => 'settings'), $base_url)); ?>" aria-label="<?php echo esc_attr__('Paramétrage', 'b2b-crm-maroc'); ?>">
                                <span class="dashicons dashicons-admin-generic" aria-hidden="true"></span>
                            </a>
                            <div class="b2b-crm__user-pill">
                                <span class="dashicons dashicons-admin-users" aria-hidden="true"></span>
                                <span><?php echo esc_html(wp_get_current_user()->display_name ?: __('Utilisateur CRM', 'b2b-crm-maroc')); ?></span>
                            </div>
                        </div>
                    </div>

                    <?php if ($tab === 'dashboard') : ?>
                        <?php self::render_dashboard($data['items']); ?>
                    <?php elseif ($tab === 'accounts') : ?>
                        <?php B2B_CRM_Views::render_settings('company'); ?>
                    <?php elseif ($tab === 'contacts') : ?>
                        <?php self::render_contacts(); ?>
                    <?php elseif ($tab === 'opportunities') : ?>
                        <?php self::render_module_items(self::module_config_opportunities()); ?>
                    <?php elseif ($tab === 'emails') : ?>
                        <?php self::render_module_items(self::module_config_emails()); ?>
                    <?php elseif ($tab === 'tasks') : ?>
                        <?php self::render_module_items(self::module_config_tasks()); ?>
                    <?php elseif ($tab === 'collect') : ?>
                        <?php B2B_CRM_Views::render_collect(); ?>
                    <?php elseif ($tab === 'pipeline') : ?>
                        <?php self::render_pipeline($filters, $data, $total_pages, $paged); ?>
                    <?php elseif ($tab === 'sources') : ?>
                        <?php B2B_CRM_Views::render_sources(); ?>
                    <?php elseif ($tab === 'settings') : ?>
                        <?php B2B_CRM_Views::render_settings(); ?>
                    <?php elseif ($tab === 'base') : ?>
                        <?php self::render_base($filters, $data, $total_pages, $paged); ?>
                    <?php elseif ($tab === 'module-disabled') : ?>
                        <?php self::render_placeholder(__('Module désactivé', 'b2b-crm-maroc'), false); ?>
                    <?php else : ?>
                        <?php self::render_placeholder($current_label, !empty($modules_state[$tab])); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }

    private static function render_dashboard(array $items)
    {
        $stats = array(
            array('icon' => '🗂️', 'value' => count($items), 'label' => __('Base de leads', 'b2b-crm-maroc')),
            array('icon' => '⭐', 'value' => self::count_interest($items, 'high'), 'label' => __('Priorité haute', 'b2b-crm-maroc')),
            array('icon' => '⚙️', 'value' => 0, 'label' => __('Doublons SQL', 'b2b-crm-maroc')),
            array('icon' => '✅', 'value' => self::count_status($items, 'contacted'), 'label' => __('Leads gagnés', 'b2b-crm-maroc')),
        );

        $recent = array();
        foreach (array_slice($items, 0, 6) as $item) {
            $recent[] = array(
                'initial' => strtoupper(substr($item['company_name'], 0, 1)),
                'company_name' => $item['company_name'],
                'meta' => trim($item['city'] . ' · ' . $item['sector'], ' ·'),
                'status' => self::statuses()[$item['status']] ?? $item['status'],
            );
        }

        B2B_CRM_Views::render_dashboard($stats, $recent);
    }

    private static function render_pipeline($filters, $data, $total_pages, $paged)
    {
        $stages = B2B_CRM_Lead_Repository::stages();
        if (empty($stages)) {
            echo '<div class="notice notice-warning"><p>' . esc_html__('Définissez les étapes du pipeline dans Paramétrage > Opportunités.', 'b2b-crm-maroc') . '</p></div>';
        }

        self::render_base($filters, $data, $total_pages, $paged);
    }

    private static function render_placeholder($label, $is_enabled)
    {
        ?>
        <div class="b2b-crm__section">
            <div class="b2b-crm__card">
                <h2><?php echo esc_html($label); ?></h2>
                <p class="b2b-crm__muted">
                    <?php if ($is_enabled) : ?>
                        <?php echo esc_html__('Ce module est en cours de configuration pour votre CRM.', 'b2b-crm-maroc'); ?>
                    <?php else : ?>
                        <?php echo esc_html__('Ce module est désactivé. Activez-le depuis Paramétrage.', 'b2b-crm-maroc'); ?>
                    <?php endif; ?>
                </p>
            </div>
        </div>
        <?php
    }

    private static function modules_config()
    {
        $config = array(
            'accounts' => true,
            'contacts' => true,
            'base' => true,
            'emails' => true,
            'tasks' => true,
            'collect' => true,
            'sources' => true,
            'pipeline' => true,
            'settings' => true,
        );
        return apply_filters('b2b_crm_modules_config', $config);
    }

    public static function base_url()
    {
        if (is_admin()) {
            return admin_url('admin.php');
        }

        if (class_exists('B2B_CRM_Shortcode')) {
            return B2B_CRM_Shortcode::portal_url();
        }

        $url = get_permalink();
        return $url ? $url : home_url('/');
    }

    private static function render_base($filters, $data, $total_pages, $paged)
    {
        $import_payload = get_transient('b2b_crm_import_' . get_current_user_id());
        $mapping_fields = array(
            '' => __('Ignorer', 'b2b-crm-maroc'),
            'company_name' => __('Société', 'b2b-crm-maroc'),
            'sector' => __('Secteur', 'b2b-crm-maroc'),
            'city' => __('Ville', 'b2b-crm-maroc'),
            'contact_name' => __('Contact', 'b2b-crm-maroc'),
            'contact_role' => __('Fonction', 'b2b-crm-maroc'),
            'phone' => __('Téléphone', 'b2b-crm-maroc'),
            'phone_mobile' => __('GSM', 'b2b-crm-maroc'),
            'email' => __('Email', 'b2b-crm-maroc'),
            'website' => __('Site Web', 'b2b-crm-maroc'),
            'status' => __('Statut CRM', 'b2b-crm-maroc'),
            'stage' => __('Étape', 'b2b-crm-maroc'),
            'interest_level' => __('Priorité', 'b2b-crm-maroc'),
            'source' => __('Source', 'b2b-crm-maroc'),
            'notes' => __('Commentaires', 'b2b-crm-maroc'),
        );
        ?>
        <div class="b2b-crm__section b2b-crm__section--row">
            <div>
                <h2><?php echo esc_html__('Gestion des Leads', 'b2b-crm-maroc'); ?></h2>
            </div>
            <div class="b2b-crm__section-actions">
                <form method="get" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="b2b_crm_export_csv" />
                    <input type="hidden" name="s" value="<?php echo esc_attr($filters['search']); ?>" />
                    <input type="hidden" name="status" value="<?php echo esc_attr($filters['status']); ?>" />
                    <input type="hidden" name="stage" value="<?php echo esc_attr($filters['stage']); ?>" />
                    <input type="hidden" name="city" value="<?php echo esc_attr($filters['city']); ?>" />
                    <input type="hidden" name="sector" value="<?php echo esc_attr($filters['sector']); ?>" />
                    <input type="hidden" name="interest_level" value="<?php echo esc_attr($filters['interest_level']); ?>" />
                    <input type="hidden" name="owner" value="<?php echo esc_attr(isset($_GET['owner']) ? sanitize_key($_GET['owner']) : ''); ?>" />
                    <?php wp_nonce_field('b2b_crm_export_csv'); ?>
                    <button class="b2b-crm__export" type="submit"><?php echo esc_html__('Exporter en CSV', 'b2b-crm-maroc'); ?></button>
                </form>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="b2b_crm_add_demo_leads" />
                    <?php wp_nonce_field('b2b_crm_add_demo_leads'); ?>
                    <button class="b2b-crm__ghost" type="submit"><?php echo esc_html__('Ajouter des données de démonstration', 'b2b-crm-maroc'); ?></button>
                </form>
                <form method="post" enctype="multipart/form-data" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="b2b_crm_import_csv" />
                    <input type="hidden" name="import_step" value="upload" />
                    <?php wp_nonce_field('b2b_crm_import_csv'); ?>
                    <input type="file" name="csv_file" accept=".csv" />
                    <button class="b2b-crm__ghost" type="submit"><?php echo esc_html__('Importer CSV', 'b2b-crm-maroc'); ?></button>
                </form>
            </div>
        </div>

        <?php if (!empty($_GET['import']) && $_GET['import'] === 'preview' && !empty($import_payload['headers'])) : ?>
            <div class="b2b-crm__card">
                <h3><?php echo esc_html__('Prévisualisation import CSV', 'b2b-crm-maroc'); ?></h3>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="b2b_crm_import_csv" />
                    <input type="hidden" name="import_step" value="confirm" />
                    <?php wp_nonce_field('b2b_crm_confirm_import'); ?>
                    <table class="b2b-crm__table">
                        <thead>
                            <tr>
                                <?php foreach ($import_payload['headers'] as $header) : ?>
                                    <th><?php echo esc_html($header); ?></th>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <?php foreach ($import_payload['headers'] as $header) : ?>
                                    <th>
                                        <select name="mapping[<?php echo esc_attr($header); ?>]">
                                            <?php foreach ($mapping_fields as $key => $label) : ?>
                                                <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($import_payload['rows'], 0, 5) as $row) : ?>
                                <tr>
                                    <?php foreach ($row as $cell) : ?>
                                        <td><?php echo esc_html($cell); ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <button class="b2b-crm__button" type="submit"><?php echo esc_html__('Confirmer l’import', 'b2b-crm-maroc'); ?></button>
                </form>
            </div>
        <?php endif; ?>

        <div class="b2b-crm__table-card">
            <div class="b2b-crm__toolbar">
                <form method="get" class="b2b-crm__filters">
                    <input type="hidden" name="page" value="b2b-crm-maroc" />
                    <input type="hidden" name="tab" value="base" />
                    <input type="search" name="s" placeholder="<?php echo esc_attr__('Recherche rapide', 'b2b-crm-maroc'); ?>" value="<?php echo esc_attr($filters['search']); ?>" />
                    <select name="status">
                        <option value=""><?php echo esc_html__('Statut', 'b2b-crm-maroc'); ?></option>
                        <?php foreach (self::statuses() as $key => $label) : ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected($filters['status'], $key); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="stage">
                        <option value=""><?php echo esc_html__('Étape', 'b2b-crm-maroc'); ?></option>
                        <?php foreach (B2B_CRM_Lead_Repository::stages() as $stage) : ?>
                            <option value="<?php echo esc_attr($stage); ?>" <?php selected($filters['stage'], $stage); ?>><?php echo esc_html($stage); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="interest_level">
                        <option value=""><?php echo esc_html__('Priorité', 'b2b-crm-maroc'); ?></option>
                        <?php foreach (self::interests() as $key => $label) : ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected($filters['interest_level'], $key); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="owner">
                        <option value=""><?php echo esc_html__('Responsable', 'b2b-crm-maroc'); ?></option>
                        <option value="me" <?php selected(isset($_GET['owner']) ? sanitize_key($_GET['owner']) : '', 'me'); ?>><?php echo esc_html__('Mes leads', 'b2b-crm-maroc'); ?></option>
                    </select>
                    <button class="b2b-crm__ghost"><?php echo esc_html__('Filtrer', 'b2b-crm-maroc'); ?></button>
                </form>
            </div>

            <table class="b2b-crm__table">
                <thead>
                    <tr>
                        <th><?php echo esc_html__('Société', 'b2b-crm-maroc'); ?></th>
                        <th><?php echo esc_html__('E-mail', 'b2b-crm-maroc'); ?></th>
                        <th><?php echo esc_html__('Téléphone', 'b2b-crm-maroc'); ?></th>
                        <th><?php echo esc_html__('GSM', 'b2b-crm-maroc'); ?></th>
                        <th><?php echo esc_html__('Site Web', 'b2b-crm-maroc'); ?></th>
                        <th><?php echo esc_html__('Réseaux Sociaux', 'b2b-crm-maroc'); ?></th>
                        <th><?php echo esc_html__('Priorité', 'b2b-crm-maroc'); ?></th>
                        <th><?php echo esc_html__('Étape', 'b2b-crm-maroc'); ?></th>
                        <th><?php echo esc_html__('Statut CRM', 'b2b-crm-maroc'); ?></th>
                        <th><?php echo esc_html__('Actions', 'b2b-crm-maroc'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['items'])) : ?>
                        <tr>
                            <td colspan="10"><?php echo esc_html__('Aucun lead pour le moment.', 'b2b-crm-maroc'); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($data['items'] as $lead) : ?>
                            <?php $social_links = self::social_links($lead); ?>
                            <tr>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(array('page' => 'b2b-crm-maroc', 'lead_id' => $lead['id']), $base_url)); ?>">
                                        <?php echo esc_html($lead['company_name']); ?>
                                    </a>
                                    <div class="b2b-crm__sub"><?php echo esc_html(trim($lead['city'] . ' · ' . $lead['sector'], ' ·')); ?></div>
                                </td>
                                <td>
                                    <div class="b2b-crm__contact-main"><?php echo esc_html($lead['email']); ?></div>
                                </td>
                                <td><?php echo esc_html($lead['phone']); ?></td>
                                <td><?php echo esc_html($lead['phone_mobile']); ?></td>
                                <td class="b2b-crm__icons">
                                    <?php if (!empty($lead['website'])) : ?>
                                        <?php self::render_icon_link($lead['website'], 'dashicons-admin-site', __('Site web', 'b2b-crm-maroc')); ?>
                                    <?php else : ?>
                                        <span class="dashicons dashicons-minus"></span>
                                    <?php endif; ?>
                                </td>
                                <td class="b2b-crm__icons">
                                    <?php if (empty($social_links)) : ?>
                                        <span class="dashicons dashicons-minus"></span>
                                    <?php else : ?>
                                        <?php foreach ($social_links as $link) : ?>
                                            <?php self::render_icon_link($link['url'], $link['icon'], $link['label']); ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="b2b-crm__pill b2b-crm__pill--<?php echo esc_attr($lead['interest_level']); ?>"><?php echo esc_html(self::interests()[$lead['interest_level']] ?? $lead['interest_level']); ?></span>
                                </td>
                                <td><?php echo esc_html($lead['stage']); ?></td>
                                <td>
                                    <span class="b2b-crm__pill b2b-crm__pill--status"><?php echo esc_html(self::statuses()[$lead['status']] ?? $lead['status']); ?></span>
                                </td>
                                <td class="b2b-crm__actions">
                                    <a href="<?php echo esc_url(add_query_arg(array('page' => 'b2b-crm-maroc', 'lead_id' => $lead['id']), $base_url)); ?>"><span class="dashicons dashicons-edit"></span></a>
                                    <a href="<?php echo esc_url(wp_nonce_url(add_query_arg(array('action' => 'b2b_crm_delete_lead', 'lead_id' => $lead['id']), admin_url('admin-post.php')), 'b2b_crm_delete_lead')); ?>"><span class="dashicons dashicons-trash"></span></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ($total_pages > 1) : ?>
                <div class="tablenav">
                    <div class="tablenav-pages">
                        <?php
                        echo paginate_links(array(
                            'base' => add_query_arg('paged', '%#%'),
                            'format' => '',
                            'prev_text' => __('«', 'b2b-crm-maroc'),
                            'next_text' => __('»', 'b2b-crm-maroc'),
                            'total' => $total_pages,
                            'current' => $paged,
                        ));
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    public static function render_accounts()
    {
        $data = B2B_CRM_Account_Repository::list(array(), 1, 1);
        $account = !empty($data['items'][0]) ? $data['items'][0] : array();

        ?>
        <div class="b2b-crm__section b2b-crm__section--row">
            <div>
                <h2><?php echo esc_html__('Entreprise', 'b2b-crm-maroc'); ?></h2>
                <p class="b2b-crm__muted"><?php echo esc_html__('Renseignez les informations de votre entreprise (un seul compte utilisé).', 'b2b-crm-maroc'); ?></p>
            </div>
        </div>

        <div class="b2b-crm__card">
            <form method="post" class="b2b-crm__form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('b2b_crm_add_account'); ?>
                <input type="hidden" name="action" value="b2b_crm_add_account" />
                <input type="hidden" name="account_id" value="<?php echo esc_attr($account['id'] ?? 0); ?>" />
                <div class="b2b-crm__grid">
                    <label>
                        <span><?php echo esc_html__('Nom de l’entreprise', 'b2b-crm-maroc'); ?></span>
                        <input type="text" name="name" value="<?php echo esc_attr($account['name'] ?? ''); ?>" required />
                        <span class="b2b-crm__field-hint"><?php echo esc_html__('Nom officiel de votre entreprise.', 'b2b-crm-maroc'); ?></span>
                    </label>
                    <label>
                        <span><?php echo esc_html__('Secteur', 'b2b-crm-maroc'); ?></span>
                        <input type="text" name="industry" value="<?php echo esc_attr($account['industry'] ?? ''); ?>" />
                        <span class="b2b-crm__field-hint"><?php echo esc_html__("Secteur d'activité principal.", 'b2b-crm-maroc'); ?></span>
                    </label>
                    <label>
                        <span><?php echo esc_html__('Ville', 'b2b-crm-maroc'); ?></span>
                        <input type="text" name="city" value="<?php echo esc_attr($account['city'] ?? ''); ?>" />
                        <span class="b2b-crm__field-hint"><?php echo esc_html__('Ville principale de votre entreprise.', 'b2b-crm-maroc'); ?></span>
                    </label>
                    <label>
                        <span><?php echo esc_html__('Email', 'b2b-crm-maroc'); ?></span>
                        <input type="email" name="email" value="<?php echo esc_attr($account['email'] ?? ''); ?>" />
                        <span class="b2b-crm__field-hint"><?php echo esc_html__('Email professionnel principal de l’entreprise.', 'b2b-crm-maroc'); ?></span>
                    </label>
                    <label>
                        <span><?php echo esc_html__('Téléphone', 'b2b-crm-maroc'); ?></span>
                        <input type="text" name="phone" value="<?php echo esc_attr($account['phone'] ?? ''); ?>" />
                        <span class="b2b-crm__field-hint"><?php echo esc_html__('Téléphone professionnel principal de l’entreprise.', 'b2b-crm-maroc'); ?></span>
                    </label>
                    <label>
                        <span><?php echo esc_html__('Site web', 'b2b-crm-maroc'); ?></span>
                        <input type="url" name="website" value="<?php echo esc_attr($account['website'] ?? ''); ?>" />
                        <span class="b2b-crm__field-hint"><?php echo esc_html__('Site web officiel de l’entreprise.', 'b2b-crm-maroc'); ?></span>
                    </label>
                    <label>
                        <span><?php echo esc_html__('Responsable', 'b2b-crm-maroc'); ?></span>
                        <input type="text" name="owner" value="<?php echo esc_attr($account['owner'] ?? ''); ?>" />
                        <span class="b2b-crm__field-hint"><?php echo esc_html__('Responsable interne de l’entreprise.', 'b2b-crm-maroc'); ?></span>
                    </label>
                    <label>
                        <span><?php echo esc_html__('Statut', 'b2b-crm-maroc'); ?></span>
                        <select name="status">
                            <option value="active" <?php selected($account['status'] ?? 'active', 'active'); ?>><?php echo esc_html__('Actif', 'b2b-crm-maroc'); ?></option>
                            <option value="inactive" <?php selected($account['status'] ?? 'active', 'inactive'); ?>><?php echo esc_html__('Inactif', 'b2b-crm-maroc'); ?></option>
                        </select>
                        <span class="b2b-crm__field-hint"><?php echo esc_html__('Statut opérationnel de l’entreprise.', 'b2b-crm-maroc'); ?></span>
                    </label>
                </div>
                <label>
                    <span><?php echo esc_html__('Notes', 'b2b-crm-maroc'); ?></span>
                    <textarea name="notes" rows="3"><?php echo esc_textarea($account['notes'] ?? ''); ?></textarea>
                    <span class="b2b-crm__field-hint"><?php echo esc_html__('Notes internes sur l’entreprise.', 'b2b-crm-maroc'); ?></span>
                </label>
                <button class="b2b-crm__button" type="submit"><?php echo esc_html__('Enregistrer l’entreprise', 'b2b-crm-maroc'); ?></button>
            </form>
        </div>
        <?php
    }

    private static function render_contacts()
    {
        $filters = array(
            'search' => isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '',
            'status' => isset($_GET['status']) ? sanitize_key($_GET['status']) : '',
        );
        $paged = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
        $per_page = 20;
        $data = B2B_CRM_Contact_Repository::list($filters, $paged, $per_page);
        $total_pages = (int) ceil($data['total'] / $per_page);

        ?>
        <div class="b2b-crm__section b2b-crm__section--row">
            <div>
                <h2><?php echo esc_html__('Contacts', 'b2b-crm-maroc'); ?></h2>
                <p class="b2b-crm__muted"><?php echo esc_html__('Gérez les contacts clés de l’entreprise.', 'b2b-crm-maroc'); ?></p>
            </div>
        </div>

        <div class="b2b-crm__card">
            <form method="post" class="b2b-crm__form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('b2b_crm_add_contact'); ?>
                <input type="hidden" name="action" value="b2b_crm_add_contact" />
                <div class="b2b-crm__grid">
                    <label>
                        <span><?php echo esc_html__('Nom complet', 'b2b-crm-maroc'); ?></span>
                        <input type="text" name="full_name" required />
                        <span class="b2b-crm__field-hint"><?php echo esc_html__('Nom complet du contact.', 'b2b-crm-maroc'); ?></span>
                    </label>
                    <label>
                        <span><?php echo esc_html__('Entreprise', 'b2b-crm-maroc'); ?></span>
                        <input type="text" name="company" />
                        <span class="b2b-crm__field-hint"><?php echo esc_html__('Entreprise à laquelle le contact est rattaché.', 'b2b-crm-maroc'); ?></span>
                    </label>
                    <label>
                        <span><?php echo esc_html__('Fonction', 'b2b-crm-maroc'); ?></span>
                        <input type="text" name="role" />
                        <span class="b2b-crm__field-hint"><?php echo esc_html__('Rôle ou fonction du contact.', 'b2b-crm-maroc'); ?></span>
                    </label>
                    <label>
                        <span><?php echo esc_html__('Email', 'b2b-crm-maroc'); ?></span>
                        <input type="email" name="email" />
                        <span class="b2b-crm__field-hint"><?php echo esc_html__('Email professionnel du contact.', 'b2b-crm-maroc'); ?></span>
                    </label>
                    <label>
                        <span><?php echo esc_html__('Téléphone', 'b2b-crm-maroc'); ?></span>
                        <input type="text" name="phone" />
                        <span class="b2b-crm__field-hint"><?php echo esc_html__('Téléphone professionnel du contact.', 'b2b-crm-maroc'); ?></span>
                    </label>
                    <label>
                        <span><?php echo esc_html__('Ville', 'b2b-crm-maroc'); ?></span>
                        <input type="text" name="city" />
                        <span class="b2b-crm__field-hint"><?php echo esc_html__('Ville du contact.', 'b2b-crm-maroc'); ?></span>
                    </label>
                    <label>
                        <span><?php echo esc_html__('Statut', 'b2b-crm-maroc'); ?></span>
                        <select name="status">
                            <option value="active"><?php echo esc_html__('Actif', 'b2b-crm-maroc'); ?></option>
                            <option value="inactive"><?php echo esc_html__('Inactif', 'b2b-crm-maroc'); ?></option>
                        </select>
                        <span class="b2b-crm__field-hint"><?php echo esc_html__('Statut actif ou inactif.', 'b2b-crm-maroc'); ?></span>
                    </label>
                </div>
                <label>
                    <span><?php echo esc_html__('Notes', 'b2b-crm-maroc'); ?></span>
                    <textarea name="notes" rows="3"></textarea>
                    <span class="b2b-crm__field-hint"><?php echo esc_html__('Notes internes sur le contact.', 'b2b-crm-maroc'); ?></span>
                </label>
                <button class="b2b-crm__button" type="submit"><?php echo esc_html__('Ajouter un contact', 'b2b-crm-maroc'); ?></button>
            </form>
        </div>

        <div class="b2b-crm__table-card">
            <div class="b2b-crm__toolbar">
                <form method="get" class="b2b-crm__filters">
                    <input type="hidden" name="page" value="b2b-crm-maroc" />
                    <input type="hidden" name="tab" value="contacts" />
                    <input type="search" name="s" placeholder="<?php echo esc_attr__('Recherche', 'b2b-crm-maroc'); ?>" value="<?php echo esc_attr($filters['search']); ?>" />
                    <select name="status">
                        <option value=""><?php echo esc_html__('Statut', 'b2b-crm-maroc'); ?></option>
                        <option value="active" <?php selected($filters['status'], 'active'); ?>><?php echo esc_html__('Actif', 'b2b-crm-maroc'); ?></option>
                        <option value="inactive" <?php selected($filters['status'], 'inactive'); ?>><?php echo esc_html__('Inactif', 'b2b-crm-maroc'); ?></option>
                    </select>
                    <button class="b2b-crm__ghost"><?php echo esc_html__('Filtrer', 'b2b-crm-maroc'); ?></button>
                </form>
            </div>

            <table class="b2b-crm__table">
                <thead>
                    <tr>
                        <th><?php echo esc_html__('Nom', 'b2b-crm-maroc'); ?></th>
                        <th><?php echo esc_html__('Entreprise', 'b2b-crm-maroc'); ?></th>
                        <th><?php echo esc_html__('Fonction', 'b2b-crm-maroc'); ?></th>
                        <th><?php echo esc_html__('Email', 'b2b-crm-maroc'); ?></th>
                        <th><?php echo esc_html__('Téléphone', 'b2b-crm-maroc'); ?></th>
                        <th><?php echo esc_html__('Statut', 'b2b-crm-maroc'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['items'])) : ?>
                        <tr>
                            <td colspan="6"><?php echo esc_html__('Aucun contact pour le moment.', 'b2b-crm-maroc'); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($data['items'] as $contact) : ?>
                            <tr>
                                <td><?php echo esc_html($contact['full_name']); ?></td>
                                <td><?php echo esc_html($contact['company']); ?></td>
                                <td><?php echo esc_html($contact['role']); ?></td>
                                <td><?php echo esc_html($contact['email']); ?></td>
                                <td><?php echo esc_html($contact['phone']); ?></td>
                                <td><?php echo esc_html($contact['status']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1) : ?>
            <div class="b2b-crm__pagination">
                <?php
                echo paginate_links(array(
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'total' => $total_pages,
                    'current' => $paged,
                ));
                ?>
            </div>
        <?php endif; ?>
        <?php
    }

    private static function render_module_items(array $config)
    {
        $filters = array(
            'search' => isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '',
            'status' => isset($_GET['status']) ? sanitize_key($_GET['status']) : '',
        );
        $paged = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
        $per_page = 20;
        $data = B2B_CRM_Module_Item_Repository::list($config['key'], $filters, $paged, $per_page);
        $total_pages = (int) ceil($data['total'] / $per_page);
        $stats = self::module_item_stats($data['items'], $config['statuses']);
        $recent = array_slice($data['items'], 0, 5);
        $lead_suggestions = self::lead_key_suggestions();

        ?>
        <div class="b2b-crm__section b2b-crm__section--row">
            <div>
                <h2><?php echo esc_html($config['title']); ?></h2>
                <p class="b2b-crm__muted"><?php echo esc_html($config['description']); ?></p>
                <?php if (!empty($config['details'])) : ?>
                    <details class="b2b-crm__module-details">
                        <summary><?php echo esc_html__('Afficher la description', 'b2b-crm-maroc'); ?></summary>
                        <p class="b2b-crm__muted"><?php echo esc_html($config['details']); ?></p>
                    </details>
                <?php endif; ?>
            </div>
        </div>

        <div class="b2b-crm__section">
            <div class="b2b-crm__stats">
                <div class="b2b-crm__stat-card">
                    <div class="b2b-crm__stat-icon"><span>📦</span></div>
                    <div class="b2b-crm__stat-value"><?php echo esc_html($stats['total']); ?></div>
                    <div class="b2b-crm__stat-label"><?php echo esc_html__('Total', 'b2b-crm-maroc'); ?></div>
                </div>
                <?php foreach ($stats['status_counts'] as $status => $count) : ?>
                    <div class="b2b-crm__stat-card">
                        <div class="b2b-crm__stat-icon"><span>⚡</span></div>
                        <div class="b2b-crm__stat-value"><?php echo esc_html($count); ?></div>
                        <div class="b2b-crm__stat-label"><?php echo esc_html($config['statuses'][$status] ?? $status); ?></div>
                    </div>
                <?php endforeach; ?>
                <div class="b2b-crm__stat-card">
                    <div class="b2b-crm__stat-icon"><span>👤</span></div>
                    <div class="b2b-crm__stat-value"><?php echo esc_html($stats['owners']); ?></div>
                    <div class="b2b-crm__stat-label"><?php echo esc_html__('Responsables', 'b2b-crm-maroc'); ?></div>
                </div>
                <?php if ($stats['amount_total'] > 0) : ?>
                    <div class="b2b-crm__stat-card">
                        <div class="b2b-crm__stat-icon"><span>💰</span></div>
                        <div class="b2b-crm__stat-value"><?php echo esc_html(number_format_i18n($stats['amount_total'], 2)); ?></div>
                        <div class="b2b-crm__stat-label"><?php echo esc_html__('Montant', 'b2b-crm-maroc'); ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($recent)) : ?>
            <div class="b2b-crm__section">
                <div class="b2b-crm__card b2b-crm__card--large">
                    <h3><?php echo esc_html__('Activité récente', 'b2b-crm-maroc'); ?></h3>
                    <div class="b2b-crm__recent-grid">
                        <?php foreach ($recent as $item) : ?>
                            <div class="b2b-crm__recent-item">
                                <div class="b2b-crm__avatar"><?php echo esc_html(strtoupper(substr($item['title'], 0, 1))); ?></div>
                                <div>
                                    <div class="b2b-crm__recent-name"><?php echo esc_html($item['title']); ?></div>
                                    <div class="b2b-crm__recent-meta"><?php echo esc_html($item['owner']); ?></div>
                                </div>
                                <span class="b2b-crm__pill b2b-crm__pill--status"><?php echo esc_html($config['statuses'][$item['status']] ?? $item['status']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="b2b-crm__card">
            <form method="post" class="b2b-crm__form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('b2b_crm_add_module_item'); ?>
                <input type="hidden" name="action" value="b2b_crm_add_module_item" />
                <input type="hidden" name="module_key" value="<?php echo esc_attr($config['key']); ?>" />
                <div class="b2b-crm__grid">
                    <?php $datalists = array(); ?>
                    <?php foreach ($config['fields'] as $field) : ?>
                        <?php
                        $list_id = '';
                        $list_values = array();
                        if ($field['name'] === 'meta[account]' || $field['name'] === 'meta[customer]') {
                            $list_id = 'b2b-crm-module-company';
                            $list_values = $lead_suggestions['company_name'] ?? array();
                        } elseif ($field['name'] === 'meta[recipient]') {
                            $list_id = 'b2b-crm-module-email';
                            $list_values = $lead_suggestions['email'] ?? array();
                        } elseif ($field['name'] === 'meta[contact]') {
                            $list_id = 'b2b-crm-module-contact';
                            $list_values = $lead_suggestions['contact_name'] ?? array();
                        } elseif ($field['name'] === 'meta[phone]') {
                            $list_id = 'b2b-crm-module-phone';
                            $list_values = $lead_suggestions['phone'] ?? array();
                        } elseif ($field['name'] === 'meta[priority]') {
                            $list_id = 'b2b-crm-module-priority';
                            $list_values = $lead_suggestions['priority'] ?? array_values(self::interests());
                        }
                        if ($list_id) {
                            $datalists[$list_id] = $list_values;
                        }
                        ?>
                        <label>
                            <span><?php echo esc_html($field['label']); ?></span>
                            <?php if ($field['type'] === 'select') : ?>
                                <select class="b2b-crm__input" name="<?php echo esc_attr($field['name']); ?>">
                                    <?php foreach ($field['options'] as $option_value => $option_label) : ?>
                                        <option value="<?php echo esc_attr($option_value); ?>"><?php echo esc_html($option_label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php elseif ($field['type'] === 'textarea') : ?>
                                <textarea class="b2b-crm__input b2b-crm__input--area" name="<?php echo esc_attr($field['name']); ?>" rows="2"></textarea>
                            <?php else : ?>
                                <input class="b2b-crm__input" type="<?php echo esc_attr($field['type']); ?>" name="<?php echo esc_attr($field['name']); ?>" <?php echo $list_id ? 'list="' . esc_attr($list_id) . '"' : ''; ?> <?php echo !empty($field['required']) ? 'required' : ''; ?> />
                            <?php endif; ?>
                            <?php if (!empty($field['hint'])) : ?>
                                <span class="b2b-crm__field-hint"><?php echo esc_html($field['hint']); ?></span>
                            <?php endif; ?>
                        </label>
                    <?php endforeach; ?>
                </div>
                <?php foreach ($datalists as $list_id => $values) : ?>
                    <?php if (!empty($values)) : ?>
                        <datalist id="<?php echo esc_attr($list_id); ?>">
                            <?php foreach ($values as $value) : ?>
                                <option value="<?php echo esc_attr($value); ?>"></option>
                            <?php endforeach; ?>
                        </datalist>
                    <?php endif; ?>
                <?php endforeach; ?>
                <button class="b2b-crm__button" type="submit"><?php echo esc_html($config['button_label']); ?></button>
            </form>
        </div>

        <div class="b2b-crm__table-card">
            <div class="b2b-crm__toolbar">
                <form method="get" class="b2b-crm__filters">
                    <input type="hidden" name="page" value="b2b-crm-maroc" />
                    <input type="hidden" name="tab" value="<?php echo esc_attr($config['key']); ?>" />
                    <input type="search" name="s" placeholder="<?php echo esc_attr__('Recherche', 'b2b-crm-maroc'); ?>" value="<?php echo esc_attr($filters['search']); ?>" />
                    <select name="status">
                        <option value=""><?php echo esc_html__('Statut', 'b2b-crm-maroc'); ?></option>
                        <?php foreach ($config['statuses'] as $status_key => $status_label) : ?>
                            <option value="<?php echo esc_attr($status_key); ?>" <?php selected($filters['status'], $status_key); ?>><?php echo esc_html($status_label); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="b2b-crm__ghost"><?php echo esc_html__('Filtrer', 'b2b-crm-maroc'); ?></button>
                </form>
            </div>

            <table class="b2b-crm__table">
                <thead>
                    <tr>
                        <?php foreach ($config['columns'] as $column) : ?>
                            <th><?php echo esc_html($column['label']); ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['items'])) : ?>
                        <tr>
                            <td colspan="<?php echo esc_attr(count($config['columns'])); ?>"><?php echo esc_html__('Aucun élément pour le moment.', 'b2b-crm-maroc'); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($data['items'] as $item) : ?>
                            <?php $meta = !empty($item['meta_json']) ? json_decode($item['meta_json'], true) : array(); ?>
                            <tr>
                                <?php foreach ($config['columns'] as $column) : ?>
                                    <?php $value = self::module_item_value($item, $meta, $column['key']); ?>
                                    <td>
                                        <?php if ($column['key'] === 'status') : ?>
                                            <span class="b2b-crm__pill b2b-crm__pill--status"><?php echo esc_html($config['statuses'][$value] ?? $value); ?></span>
                                        <?php else : ?>
                                            <?php echo esc_html($value); ?>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1) : ?>
            <div class="b2b-crm__pagination">
                <?php
                echo paginate_links(array(
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'total' => $total_pages,
                    'current' => $paged,
                ));
                ?>
            </div>
        <?php endif; ?>
        <?php
    }

    private static function count_interest(array $items, $level)
    {
        return count(array_filter($items, function ($item) use ($level) {
            return $item['interest_level'] === $level;
        }));
    }

    private static function count_status(array $items, $status)
    {
        return count(array_filter($items, function ($item) use ($status) {
            return $item['status'] === $status;
        }));
    }

    private static function statuses()
    {
        return array(
            'new' => __('Nouveau', 'b2b-crm-maroc'),
            'qualified' => __('Qualifié', 'b2b-crm-maroc'),
            'contacted' => __('Contacté', 'b2b-crm-maroc'),
            'inactive' => __('Inactif', 'b2b-crm-maroc'),
        );
    }

    private static function module_item_value(array $item, array $meta, $key)
    {
        if (isset($item[$key])) {
            if ($key === 'amount') {
                return number_format_i18n((float) $item[$key], 2);
            }
            return $item[$key];
        }
        return $meta[$key] ?? '';
    }

    private static function module_item_stats(array $items, array $statuses)
    {
        $status_counts = array();
        foreach ($statuses as $key => $label) {
            $status_counts[$key] = 0;
        }

        $owners = array();
        $amount_total = 0;

        foreach ($items as $item) {
            if (!empty($item['status']) && array_key_exists($item['status'], $status_counts)) {
                $status_counts[$item['status']]++;
            }
            if (!empty($item['owner'])) {
                $owners[$item['owner']] = true;
            }
            if (!empty($item['amount'])) {
                $amount_total += (float) $item['amount'];
            }
        }

        return array(
            'total' => count($items),
            'status_counts' => $status_counts,
            'owners' => count($owners),
            'amount_total' => $amount_total,
        );
    }

    private static function module_config_opportunities()
    {
        return array(
            'key' => 'opportunities',
            'title' => __('Opportunités', 'b2b-crm-maroc'),
            'description' => __('Suivez les opportunités commerciales en cours.', 'b2b-crm-maroc'),
            'details' => __('Centralisez les informations commerciales clés (montant, étape, prochaines actions) pour piloter chaque opportunité jusqu’à sa conclusion.', 'b2b-crm-maroc'),
            'button_label' => __('Ajouter une opportunité', 'b2b-crm-maroc'),
            'statuses' => array(
                'open' => __('Ouverte', 'b2b-crm-maroc'),
                'won' => __('Gagnée', 'b2b-crm-maroc'),
                'lost' => __('Perdue', 'b2b-crm-maroc'),
            ),
            'fields' => array(
                array('name' => 'title', 'label' => __('Nom', 'b2b-crm-maroc'), 'type' => 'text', 'required' => true, 'hint' => __('Nom de l\'opportunité.', 'b2b-crm-maroc')),
                array('name' => 'meta[account]', 'label' => __('Entreprise', 'b2b-crm-maroc'), 'type' => 'text', 'hint' => __('Entreprise associée.', 'b2b-crm-maroc')),
                array('name' => 'amount', 'label' => __('Montant', 'b2b-crm-maroc'), 'type' => 'number', 'hint' => __('Montant estimé de l\'opportunité.', 'b2b-crm-maroc')),
                array('name' => 'meta[stage]', 'label' => __('Étape', 'b2b-crm-maroc'), 'type' => 'text', 'hint' => __('Étape actuelle du pipeline.', 'b2b-crm-maroc')),
                array('name' => 'meta[probability]', 'label' => __('Probabilité (%)', 'b2b-crm-maroc'), 'type' => 'number', 'hint' => __('Probabilité de réussite.', 'b2b-crm-maroc')),
                array('name' => 'meta[next_step]', 'label' => __('Prochaine action', 'b2b-crm-maroc'), 'type' => 'text', 'hint' => __('Prochaine action commerciale.', 'b2b-crm-maroc')),
                array('name' => 'due_date', 'label' => __('Date de clôture', 'b2b-crm-maroc'), 'type' => 'date', 'hint' => __('Date de clôture prévue.', 'b2b-crm-maroc')),
                array('name' => 'owner', 'label' => __('Responsable', 'b2b-crm-maroc'), 'type' => 'text', 'hint' => __('Responsable du suivi.', 'b2b-crm-maroc')),
                array('name' => 'status', 'label' => __('Statut', 'b2b-crm-maroc'), 'type' => 'select', 'hint' => __('Statut global de l\'opportunité.', 'b2b-crm-maroc'), 'options' => array(
                    'open' => __('Ouverte', 'b2b-crm-maroc'),
                    'won' => __('Gagnée', 'b2b-crm-maroc'),
                    'lost' => __('Perdue', 'b2b-crm-maroc'),
                )),
            ),
            'columns' => array(
                array('key' => 'title', 'label' => __('Nom', 'b2b-crm-maroc')),
                array('key' => 'account', 'label' => __('Entreprise', 'b2b-crm-maroc')),
                array('key' => 'amount', 'label' => __('Montant', 'b2b-crm-maroc')),
                array('key' => 'stage', 'label' => __('Étape', 'b2b-crm-maroc')),
                array('key' => 'probability', 'label' => __('Probabilité', 'b2b-crm-maroc')),
                array('key' => 'next_step', 'label' => __('Prochaine action', 'b2b-crm-maroc')),
                array('key' => 'status', 'label' => __('Statut', 'b2b-crm-maroc')),
            ),
        );
    }

    private static function module_config_tasks()
    {
        return array(
            'key' => 'tasks',
            'title' => __('Activités', 'b2b-crm-maroc'),
            'description' => __('Suivez les activités et actions internes.', 'b2b-crm-maroc'),
            'details' => __('Planifiez les actions à réaliser, suivez leur échéance et clarifiez les responsabilités pour garder le rythme des opérations.', 'b2b-crm-maroc'),
            'button_label' => __('Ajouter une activité', 'b2b-crm-maroc'),
            'statuses' => array(
                'todo' => __('À faire', 'b2b-crm-maroc'),
                'doing' => __('En cours', 'b2b-crm-maroc'),
                'done' => __('Terminée', 'b2b-crm-maroc'),
            ),
            'fields' => array(
                array('name' => 'title', 'label' => __('Titre', 'b2b-crm-maroc'), 'type' => 'text', 'required' => true, 'hint' => __('Titre de la tâche.', 'b2b-crm-maroc')),
                array('name' => 'owner', 'label' => __('Responsable', 'b2b-crm-maroc'), 'type' => 'text', 'hint' => __('Responsable de la tâche.', 'b2b-crm-maroc')),
                array('name' => 'meta[account]', 'label' => __('Entreprise', 'b2b-crm-maroc'), 'type' => 'text', 'hint' => __('Entreprise concernée.', 'b2b-crm-maroc')),
                array('name' => 'due_date', 'label' => __('Échéance', 'b2b-crm-maroc'), 'type' => 'date', 'hint' => __('Date d\'échéance.', 'b2b-crm-maroc')),
                array('name' => 'meta[priority]', 'label' => __('Priorité', 'b2b-crm-maroc'), 'type' => 'text', 'hint' => __('Priorité.', 'b2b-crm-maroc')),
                array('name' => 'meta[channel]', 'label' => __('Canal', 'b2b-crm-maroc'), 'type' => 'text', 'hint' => __('Canal d\'origine.', 'b2b-crm-maroc')),
                array('name' => 'meta[description]', 'label' => __('Description', 'b2b-crm-maroc'), 'type' => 'textarea', 'hint' => __('Description détaillée.', 'b2b-crm-maroc')),
                array('name' => 'status', 'label' => __('Statut', 'b2b-crm-maroc'), 'type' => 'select', 'hint' => __('Statut d\'avancement.', 'b2b-crm-maroc'), 'options' => array(
                    'todo' => __('À faire', 'b2b-crm-maroc'),
                    'doing' => __('En cours', 'b2b-crm-maroc'),
                    'done' => __('Terminée', 'b2b-crm-maroc'),
                )),
            ),
            'columns' => array(
                array('key' => 'title', 'label' => __('Titre', 'b2b-crm-maroc')),
                array('key' => 'owner', 'label' => __('Responsable', 'b2b-crm-maroc')),
                array('key' => 'account', 'label' => __('Entreprise', 'b2b-crm-maroc')),
                array('key' => 'priority', 'label' => __('Priorité', 'b2b-crm-maroc')),
                array('key' => 'due_date', 'label' => __('Échéance', 'b2b-crm-maroc')),
                array('key' => 'status', 'label' => __('Statut', 'b2b-crm-maroc')),
            ),
        );
    }

    private static function module_config_tickets()
    {
        return array(
            'key' => 'tickets',
            'title' => __('Tickets', 'b2b-crm-maroc'),
            'description' => __('Suivez les demandes et incidents clients.', 'b2b-crm-maroc'),
            'details' => __('Centralisez les demandes clients, leur priorité et le canal d’entrée afin de garantir un suivi réactif.', 'b2b-crm-maroc'),
            'button_label' => __('Ajouter un ticket', 'b2b-crm-maroc'),
            'statuses' => array(
                'open' => __('Ouvert', 'b2b-crm-maroc'),
                'pending' => __('En attente', 'b2b-crm-maroc'),
                'closed' => __('Clôturé', 'b2b-crm-maroc'),
            ),
            'fields' => array(
                array('name' => 'title', 'label' => __('Sujet', 'b2b-crm-maroc'), 'type' => 'text', 'required' => true, 'hint' => __('Sujet du ticket.', 'b2b-crm-maroc')),
                array('name' => 'meta[customer]', 'label' => __('Client', 'b2b-crm-maroc'), 'type' => 'text', 'hint' => __('Client concerné.', 'b2b-crm-maroc')),
                array('name' => 'meta[channel]', 'label' => __('Canal', 'b2b-crm-maroc'), 'type' => 'text', 'hint' => __('Canal de support.', 'b2b-crm-maroc')),
                array('name' => 'meta[category]', 'label' => __('Catégorie', 'b2b-crm-maroc'), 'type' => 'text', 'hint' => __('Catégorie du ticket.', 'b2b-crm-maroc')),
                array('name' => 'owner', 'label' => __('Responsable', 'b2b-crm-maroc'), 'type' => 'text', 'hint' => __('Responsable du suivi.', 'b2b-crm-maroc')),
                array('name' => 'meta[priority]', 'label' => __('Priorité', 'b2b-crm-maroc'), 'type' => 'text', 'hint' => __('Priorité du ticket.', 'b2b-crm-maroc')),
                array('name' => 'meta[description]', 'label' => __('Description', 'b2b-crm-maroc'), 'type' => 'textarea', 'hint' => __('Description détaillée.', 'b2b-crm-maroc')),
                array('name' => 'status', 'label' => __('Statut', 'b2b-crm-maroc'), 'type' => 'select', 'hint' => __('Statut de traitement.', 'b2b-crm-maroc'), 'options' => array(
                    'open' => __('Ouvert', 'b2b-crm-maroc'),
                    'pending' => __('En attente', 'b2b-crm-maroc'),
                    'closed' => __('Clôturé', 'b2b-crm-maroc'),
                )),
            ),
            'columns' => array(
                array('key' => 'title', 'label' => __('Sujet', 'b2b-crm-maroc')),
                array('key' => 'customer', 'label' => __('Client', 'b2b-crm-maroc')),
                array('key' => 'channel', 'label' => __('Canal', 'b2b-crm-maroc')),
                array('key' => 'category', 'label' => __('Catégorie', 'b2b-crm-maroc')),
                array('key' => 'priority', 'label' => __('Priorité', 'b2b-crm-maroc')),
                array('key' => 'owner', 'label' => __('Responsable', 'b2b-crm-maroc')),
                array('key' => 'status', 'label' => __('Statut', 'b2b-crm-maroc')),
            ),
        );
    }

    private static function module_config_documents()
    {
        return array(
            'key' => 'documents',
            'title' => __('Documents', 'b2b-crm-maroc'),
            'description' => __('Centralisez les documents et fichiers clients.', 'b2b-crm-maroc'),
            'details' => __('Référencez les fichiers importants, leurs tags et leur visibilité pour les retrouver rapidement et les partager au bon niveau.', 'b2b-crm-maroc'),
            'button_label' => __('Ajouter un document', 'b2b-crm-maroc'),
            'statuses' => array(
                'draft' => __('Brouillon', 'b2b-crm-maroc'),
                'published' => __('Publié', 'b2b-crm-maroc'),
            ),
            'fields' => array(
                array('name' => 'title', 'label' => __('Titre', 'b2b-crm-maroc'), 'type' => 'text', 'required' => true, 'hint' => __('Titre du document.', 'b2b-crm-maroc')),
                array('name' => 'meta[category]', 'label' => __('Catégorie', 'b2b-crm-maroc'), 'type' => 'text', 'hint' => __('Catégorie du document.', 'b2b-crm-maroc')),
                array('name' => 'meta[tags]', 'label' => __('Tags', 'b2b-crm-maroc'), 'type' => 'text', 'hint' => __('Tags de classement.', 'b2b-crm-maroc')),
                array('name' => 'meta[file_url]', 'label' => __('Lien du fichier', 'b2b-crm-maroc'), 'type' => 'url', 'hint' => __('Lien du fichier.', 'b2b-crm-maroc')),
                array('name' => 'meta[visibility]', 'label' => __('Visibilité', 'b2b-crm-maroc'), 'type' => 'text', 'hint' => __('Visibilité interne/externe.', 'b2b-crm-maroc')),
                array('name' => 'owner', 'label' => __('Responsable', 'b2b-crm-maroc'), 'type' => 'text', 'hint' => __('Responsable du document.', 'b2b-crm-maroc')),
                array('name' => 'status', 'label' => __('Statut', 'b2b-crm-maroc'), 'type' => 'select', 'hint' => __('Statut de publication.', 'b2b-crm-maroc'), 'options' => array(
                    'draft' => __('Brouillon', 'b2b-crm-maroc'),
                    'published' => __('Publié', 'b2b-crm-maroc'),
                )),
            ),
            'columns' => array(
                array('key' => 'title', 'label' => __('Titre', 'b2b-crm-maroc')),
                array('key' => 'category', 'label' => __('Catégorie', 'b2b-crm-maroc')),
                array('key' => 'tags', 'label' => __('Tags', 'b2b-crm-maroc')),
                array('key' => 'file_url', 'label' => __('Fichier', 'b2b-crm-maroc')),
                array('key' => 'visibility', 'label' => __('Visibilité', 'b2b-crm-maroc')),
                array('key' => 'owner', 'label' => __('Responsable', 'b2b-crm-maroc')),
                array('key' => 'status', 'label' => __('Statut', 'b2b-crm-maroc')),
            ),
        );
    }

    private static function module_config_knowledge()
    {
        return array(
            'key' => 'knowledge',
            'title' => __('Base de connaissance', 'b2b-crm-maroc'),
            'description' => __('Organisez les articles internes et procédures.', 'b2b-crm-maroc'),
            'details' => __('Structurez les contenus internes, résumés et catégories pour faciliter l’onboarding et la résolution rapide des questions.', 'b2b-crm-maroc'),
            'button_label' => __('Ajouter un article', 'b2b-crm-maroc'),
            'statuses' => array(
                'draft' => __('Brouillon', 'b2b-crm-maroc'),
                'published' => __('Publié', 'b2b-crm-maroc'),
            ),
            'fields' => array(
                array('name' => 'title', 'label' => __('Titre', 'b2b-crm-maroc'), 'type' => 'text', 'required' => true, 'hint' => __('Titre de l\'article.', 'b2b-crm-maroc')),
                array('name' => 'meta[category]', 'label' => __('Catégorie', 'b2b-crm-maroc'), 'type' => 'text', 'hint' => __('Catégorie de la base de connaissance.', 'b2b-crm-maroc')),
                array('name' => 'meta[tags]', 'label' => __('Tags', 'b2b-crm-maroc'), 'type' => 'text', 'hint' => __('Tags de recherche.', 'b2b-crm-maroc')),
                array('name' => 'meta[summary]', 'label' => __('Résumé', 'b2b-crm-maroc'), 'type' => 'textarea', 'hint' => __('Résumé synthétique.', 'b2b-crm-maroc')),
                array('name' => 'owner', 'label' => __('Responsable', 'b2b-crm-maroc'), 'type' => 'text', 'hint' => __('Responsable de publication.', 'b2b-crm-maroc')),
                array('name' => 'status', 'label' => __('Statut', 'b2b-crm-maroc'), 'type' => 'select', 'hint' => __('Statut de l\'article.', 'b2b-crm-maroc'), 'options' => array(
                    'draft' => __('Brouillon', 'b2b-crm-maroc'),
                    'published' => __('Publié', 'b2b-crm-maroc'),
                )),
            ),
            'columns' => array(
                array('key' => 'title', 'label' => __('Titre', 'b2b-crm-maroc')),
                array('key' => 'category', 'label' => __('Catégorie', 'b2b-crm-maroc')),
                array('key' => 'tags', 'label' => __('Tags', 'b2b-crm-maroc')),
                array('key' => 'owner', 'label' => __('Responsable', 'b2b-crm-maroc')),
                array('key' => 'status', 'label' => __('Statut', 'b2b-crm-maroc')),
            ),
        );
    }

    private static function module_config_sales()
    {
        return array(
            'key' => 'sales',
            'title' => __('Sales & Purchases', 'b2b-crm-maroc'),
            'description' => __('Suivez les ventes et achats en cours.', 'b2b-crm-maroc'),
            'details' => __('Regroupez les transactions, leurs montants et références pour suivre les flux commerciaux et financiers.', 'b2b-crm-maroc'),
            'button_label' => __('Ajouter une transaction', 'b2b-crm-maroc'),
            'statuses' => array(
                'open' => __('Ouverte', 'b2b-crm-maroc'),
                'closed' => __('Clôturée', 'b2b-crm-maroc'),
            ),
            'fields' => array(
                array('name' => 'title', 'label' => __('Libellé', 'b2b-crm-maroc'), 'type' => 'text', 'required' => true, 'hint' => __('Libellé de la transaction.', 'b2b-crm-maroc')),
                array('name' => 'amount', 'label' => __('Montant', 'b2b-crm-maroc'), 'type' => 'number', 'hint' => __('Montant financier.', 'b2b-crm-maroc')),
                array('name' => 'meta[type]', 'label' => __('Type', 'b2b-crm-maroc'), 'type' => 'text', 'hint' => __('Type (vente/achat).', 'b2b-crm-maroc')),
                array('name' => 'meta[account]', 'label' => __('Entreprise', 'b2b-crm-maroc'), 'type' => 'text', 'hint' => __('Entreprise concernée.', 'b2b-crm-maroc')),
                array('name' => 'meta[channel]', 'label' => __('Canal', 'b2b-crm-maroc'), 'type' => 'text', 'hint' => __('Canal (web, direct, etc.).', 'b2b-crm-maroc')),
                array('name' => 'meta[reference]', 'label' => __('Référence', 'b2b-crm-maroc'), 'type' => 'text', 'hint' => __('Référence interne/externe.', 'b2b-crm-maroc')),
                array('name' => 'owner', 'label' => __('Responsable', 'b2b-crm-maroc'), 'type' => 'text', 'hint' => __('Responsable de la transaction.', 'b2b-crm-maroc')),
                array('name' => 'status', 'label' => __('Statut', 'b2b-crm-maroc'), 'type' => 'select', 'hint' => __('Statut d\'avancement.', 'b2b-crm-maroc'), 'options' => array(
                    'open' => __('Ouverte', 'b2b-crm-maroc'),
                    'closed' => __('Clôturée', 'b2b-crm-maroc'),
                )),
            ),
            'columns' => array(
                array('key' => 'title', 'label' => __('Libellé', 'b2b-crm-maroc')),
                array('key' => 'type', 'label' => __('Type', 'b2b-crm-maroc')),
                array('key' => 'amount', 'label' => __('Montant', 'b2b-crm-maroc')),
                array('key' => 'account', 'label' => __('Entreprise', 'b2b-crm-maroc')),
                array('key' => 'reference', 'label' => __('Référence', 'b2b-crm-maroc')),
                array('key' => 'owner', 'label' => __('Responsable', 'b2b-crm-maroc')),
                array('key' => 'status', 'label' => __('Statut', 'b2b-crm-maroc')),
            ),
        );
    }

    private static function module_config_emails()
    {
        return array(
            'key' => 'emails',
            'title' => __('Emails', 'b2b-crm-maroc'),
            'description' => __('Journalisez les emails envoyés.', 'b2b-crm-maroc'),
            'details' => __('Historisez les échanges clés avec les destinataires, la direction et le statut d’envoi pour garder un fil de communication clair.', 'b2b-crm-maroc'),
            'button_label' => __('Ajouter un email', 'b2b-crm-maroc'),
            'statuses' => array(
                'draft' => __('Brouillon', 'b2b-crm-maroc'),
                'sent' => __('Envoyé', 'b2b-crm-maroc'),
            ),
            'fields' => array(
                array('name' => 'title', 'label' => __('Sujet', 'b2b-crm-maroc'), 'type' => 'text', 'required' => true, 'hint' => __('Sujet de l\'email.', 'b2b-crm-maroc')),
                array('name' => 'meta[recipient]', 'label' => __('Destinataire', 'b2b-crm-maroc'), 'type' => 'email', 'hint' => __('Destinataire principal.', 'b2b-crm-maroc')),
                array('name' => 'meta[account]', 'label' => __('Entreprise', 'b2b-crm-maroc'), 'type' => 'text', 'hint' => __('Entreprise liée.', 'b2b-crm-maroc')),
                array('name' => 'meta[channel]', 'label' => __('Canal', 'b2b-crm-maroc'), 'type' => 'text', 'hint' => __('Canal d\'envoi.', 'b2b-crm-maroc')),
                array('name' => 'meta[direction]', 'label' => __('Direction', 'b2b-crm-maroc'), 'type' => 'text', 'hint' => __('Direction (entrant/sortant).', 'b2b-crm-maroc')),
                array('name' => 'owner', 'label' => __('Responsable', 'b2b-crm-maroc'), 'type' => 'text', 'hint' => __('Responsable de l\'email.', 'b2b-crm-maroc')),
                array('name' => 'status', 'label' => __('Statut', 'b2b-crm-maroc'), 'type' => 'select', 'hint' => __('Statut (brouillon/envoyé).', 'b2b-crm-maroc'), 'options' => array(
                    'draft' => __('Brouillon', 'b2b-crm-maroc'),
                    'sent' => __('Envoyé', 'b2b-crm-maroc'),
                )),
            ),
            'columns' => array(
                array('key' => 'title', 'label' => __('Sujet', 'b2b-crm-maroc')),
                array('key' => 'recipient', 'label' => __('Destinataire', 'b2b-crm-maroc')),
                array('key' => 'account', 'label' => __('Entreprise', 'b2b-crm-maroc')),
                array('key' => 'direction', 'label' => __('Direction', 'b2b-crm-maroc')),
                array('key' => 'owner', 'label' => __('Responsable', 'b2b-crm-maroc')),
                array('key' => 'status', 'label' => __('Statut', 'b2b-crm-maroc')),
            ),
        );
    }

    private static function module_config_calendar()
    {
        return array(
            'key' => 'calendar',
            'title' => __('Calendrier', 'b2b-crm-maroc'),
            'description' => __('Planifiez les événements du CRM.', 'b2b-crm-maroc'),
            'details' => __('Planifiez les événements importants, leur durée et leur lieu pour coordonner les équipes efficacement.', 'b2b-crm-maroc'),
            'button_label' => __('Ajouter un événement', 'b2b-crm-maroc'),
            'statuses' => array(
                'planned' => __('Planifié', 'b2b-crm-maroc'),
                'done' => __('Terminé', 'b2b-crm-maroc'),
            ),
            'fields' => array(
                array('name' => 'title', 'label' => __('Titre', 'b2b-crm-maroc'), 'type' => 'text', 'required' => true, 'hint' => __('Titre de l\'événement.', 'b2b-crm-maroc')),
                array('name' => 'due_date', 'label' => __('Date', 'b2b-crm-maroc'), 'type' => 'date', 'hint' => __('Date de l\'événement.', 'b2b-crm-maroc')),
                array('name' => 'meta[duration]', 'label' => __('Durée (min)', 'b2b-crm-maroc'), 'type' => 'number', 'hint' => __('Durée prévue.', 'b2b-crm-maroc')),
                array('name' => 'meta[location]', 'label' => __('Lieu', 'b2b-crm-maroc'), 'type' => 'text', 'hint' => __('Lieu de l\'événement.', 'b2b-crm-maroc')),
                array('name' => 'owner', 'label' => __('Responsable', 'b2b-crm-maroc'), 'type' => 'text', 'hint' => __('Responsable de l\'événement.', 'b2b-crm-maroc')),
                array('name' => 'status', 'label' => __('Statut', 'b2b-crm-maroc'), 'type' => 'select', 'hint' => __('Statut de planification.', 'b2b-crm-maroc'), 'options' => array(
                    'planned' => __('Planifié', 'b2b-crm-maroc'),
                    'done' => __('Terminé', 'b2b-crm-maroc'),
                )),
            ),
            'columns' => array(
                array('key' => 'title', 'label' => __('Titre', 'b2b-crm-maroc')),
                array('key' => 'due_date', 'label' => __('Date', 'b2b-crm-maroc')),
                array('key' => 'duration', 'label' => __('Durée', 'b2b-crm-maroc')),
                array('key' => 'location', 'label' => __('Lieu', 'b2b-crm-maroc')),
                array('key' => 'owner', 'label' => __('Responsable', 'b2b-crm-maroc')),
                array('key' => 'status', 'label' => __('Statut', 'b2b-crm-maroc')),
            ),
        );
    }

    private static function module_config_meetings()
    {
        return array(
            'key' => 'meetings',
            'title' => __('Rendez-vous', 'b2b-crm-maroc'),
            'description' => __('Programmez les réunions et rendez-vous.', 'b2b-crm-maroc'),
            'details' => __('Organisez les rendez-vous, participants et lieux pour un suivi précis des échanges clients.', 'b2b-crm-maroc'),
            'button_label' => __('Ajouter un rendez-vous', 'b2b-crm-maroc'),
            'statuses' => array(
                'planned' => __('Planifié', 'b2b-crm-maroc'),
                'done' => __('Terminé', 'b2b-crm-maroc'),
            ),
            'fields' => array(
                array('name' => 'title', 'label' => __('Sujet', 'b2b-crm-maroc'), 'type' => 'text', 'required' => true, 'hint' => __('Sujet du rendez-vous.', 'b2b-crm-maroc')),
                array('name' => 'due_date', 'label' => __('Date', 'b2b-crm-maroc'), 'type' => 'date', 'hint' => __('Date de rendez-vous.', 'b2b-crm-maroc')),
                array('name' => 'meta[location]', 'label' => __('Lieu', 'b2b-crm-maroc'), 'type' => 'text', 'hint' => __('Lieu de rendez-vous.', 'b2b-crm-maroc')),
                array('name' => 'meta[participants]', 'label' => __('Participants', 'b2b-crm-maroc'), 'type' => 'text', 'hint' => __('Participants clés.', 'b2b-crm-maroc')),
                array('name' => 'meta[duration]', 'label' => __('Durée (min)', 'b2b-crm-maroc'), 'type' => 'number', 'hint' => __('Durée estimée.', 'b2b-crm-maroc')),
                array('name' => 'owner', 'label' => __('Responsable', 'b2b-crm-maroc'), 'type' => 'text', 'hint' => __('Responsable de la réunion.', 'b2b-crm-maroc')),
                array('name' => 'status', 'label' => __('Statut', 'b2b-crm-maroc'), 'type' => 'select', 'hint' => __('Statut de suivi.', 'b2b-crm-maroc'), 'options' => array(
                    'planned' => __('Planifié', 'b2b-crm-maroc'),
                    'done' => __('Terminé', 'b2b-crm-maroc'),
                )),
            ),
            'columns' => array(
                array('key' => 'title', 'label' => __('Sujet', 'b2b-crm-maroc')),
                array('key' => 'location', 'label' => __('Lieu', 'b2b-crm-maroc')),
                array('key' => 'participants', 'label' => __('Participants', 'b2b-crm-maroc')),
                array('key' => 'duration', 'label' => __('Durée', 'b2b-crm-maroc')),
                array('key' => 'due_date', 'label' => __('Date', 'b2b-crm-maroc')),
                array('key' => 'owner', 'label' => __('Responsable', 'b2b-crm-maroc')),
            ),
        );
    }

    private static function module_config_calls()
    {
        return array(
            'key' => 'calls',
            'title' => __('Appels', 'b2b-crm-maroc'),
            'description' => __('Suivez les appels effectués.', 'b2b-crm-maroc'),
            'details' => __('Tracez les appels, leurs contacts et durées pour garder un historique des échanges téléphoniques.', 'b2b-crm-maroc'),
            'button_label' => __('Ajouter un appel', 'b2b-crm-maroc'),
            'statuses' => array(
                'planned' => __('Planifié', 'b2b-crm-maroc'),
                'done' => __('Terminé', 'b2b-crm-maroc'),
            ),
            'fields' => array(
                array('name' => 'title', 'label' => __('Sujet', 'b2b-crm-maroc'), 'type' => 'text', 'required' => true, 'hint' => __('Sujet de l\'appel.', 'b2b-crm-maroc')),
                array('name' => 'meta[phone]', 'label' => __('Téléphone', 'b2b-crm-maroc'), 'type' => 'text', 'hint' => __('Numéro appelé.', 'b2b-crm-maroc')),
                array('name' => 'meta[duration]', 'label' => __('Durée (min)', 'b2b-crm-maroc'), 'type' => 'number', 'hint' => __('Durée de l\'appel.', 'b2b-crm-maroc')),
                array('name' => 'meta[contact]', 'label' => __('Contact', 'b2b-crm-maroc'), 'type' => 'text', 'hint' => __('Contact concerné.', 'b2b-crm-maroc')),
                array('name' => 'due_date', 'label' => __('Date', 'b2b-crm-maroc'), 'type' => 'date', 'hint' => __('Date prévue.', 'b2b-crm-maroc')),
                array('name' => 'owner', 'label' => __('Responsable', 'b2b-crm-maroc'), 'type' => 'text', 'hint' => __('Responsable.', 'b2b-crm-maroc')),
                array('name' => 'status', 'label' => __('Statut', 'b2b-crm-maroc'), 'type' => 'select', 'hint' => __('Statut de l\'appel.', 'b2b-crm-maroc'), 'options' => array(
                    'planned' => __('Planifié', 'b2b-crm-maroc'),
                    'done' => __('Terminé', 'b2b-crm-maroc'),
                )),
            ),
            'columns' => array(
                array('key' => 'title', 'label' => __('Sujet', 'b2b-crm-maroc')),
                array('key' => 'phone', 'label' => __('Téléphone', 'b2b-crm-maroc')),
                array('key' => 'contact', 'label' => __('Contact', 'b2b-crm-maroc')),
                array('key' => 'duration', 'label' => __('Durée', 'b2b-crm-maroc')),
                array('key' => 'due_date', 'label' => __('Date', 'b2b-crm-maroc')),
                array('key' => 'owner', 'label' => __('Responsable', 'b2b-crm-maroc')),
            ),
        );
    }

    private static function interests()
    {
        return array(
            'low' => __('Faible', 'b2b-crm-maroc'),
            'medium' => __('Moyen', 'b2b-crm-maroc'),
            'high' => __('Fort', 'b2b-crm-maroc'),
        );
    }

    private static function lead_key_suggestions()
    {
        static $cache = null;

        if ($cache === null) {
            $cache = B2B_CRM_Lead_Repository::key_values(
                array('company_name', 'contact_name', 'email', 'phone', 'phone_mobile', 'website', 'priority', 'status')
            );
        }

        return $cache;
    }

    private static function social_links(array $lead)
    {
        if (empty($lead['social_json'])) {
            return array();
        }

        $decoded = json_decode($lead['social_json'], true);
        if (!is_array($decoded)) {
            return array();
        }

        $map = array(
            'website' => array('icon' => 'dashicons-admin-site', 'label' => __('Site web', 'b2b-crm-maroc')),
            'facebook' => array('icon' => 'dashicons-facebook', 'label' => __('Facebook', 'b2b-crm-maroc')),
            'instagram' => array('icon' => 'dashicons-instagram', 'label' => __('Instagram', 'b2b-crm-maroc')),
            'linkedin' => array('icon' => 'dashicons-linkedin', 'label' => __('LinkedIn', 'b2b-crm-maroc')),
        );

        $links = array();
        foreach ($map as $key => $meta) {
            if (empty($decoded[$key])) {
                continue;
            }
            $links[] = array(
                'url' => $decoded[$key],
                'icon' => $meta['icon'],
                'label' => $meta['label'],
            );
        }

        return $links;
    }

    private static function render_icon_link($url, $icon, $label)
    {
        ?>
        <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr($label); ?>">
            <span class="dashicons <?php echo esc_attr($icon); ?>" aria-hidden="true"></span>
        </a>
        <?php
    }
}
