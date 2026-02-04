<?php

if (!defined('ABSPATH')) {
    exit;
}

class B2B_CRM_Lead_List_Page
{
    public static function render($tab = 'dashboard')
    {
        $filters = array(
            'search' => isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '',
            'status' => isset($_GET['status']) ? sanitize_key($_GET['status']) : '',
            'city' => isset($_GET['city']) ? sanitize_text_field(wp_unslash($_GET['city'])) : '',
            'sector' => isset($_GET['sector']) ? sanitize_text_field(wp_unslash($_GET['sector'])) : '',
            'interest_level' => isset($_GET['interest_level']) ? sanitize_key($_GET['interest_level']) : '',
        );

        $paged = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
        $per_page = 20;
        $data = B2B_CRM_Lead_Repository::list($filters, $paged, $per_page);
        $total_pages = (int) ceil($data['total'] / $per_page);

        $tabs = array(
            'dashboard' => __('Accueil', 'b2b-crm-maroc'),
            'accounts' => __('Comptes', 'b2b-crm-maroc'),
            'contacts' => __('Contacts', 'b2b-crm-maroc'),
            'base' => __('Prospects', 'b2b-crm-maroc'),
            'opportunities' => __('Opportunités', 'b2b-crm-maroc'),
            'emails' => __('Emails', 'b2b-crm-maroc'),
            'calendar' => __('Calendrier', 'b2b-crm-maroc'),
            'meetings' => __('Rendez-vous', 'b2b-crm-maroc'),
            'calls' => __('Appels', 'b2b-crm-maroc'),
            'tasks' => __('Tâches', 'b2b-crm-maroc'),
            'tickets' => __('Tickets', 'b2b-crm-maroc'),
            'knowledge' => __('Base de connaissance', 'b2b-crm-maroc'),
            'documents' => __('Documents', 'b2b-crm-maroc'),
            'sales' => __('Sales & Purchases', 'b2b-crm-maroc'),
            'collect' => __('Collecte', 'b2b-crm-maroc'),
            'sources' => __('Sources', 'b2b-crm-maroc'),
            'pipeline' => __('CRM Pipeline', 'b2b-crm-maroc'),
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
                    array('key' => 'accounts', 'icon' => 'dashicons-building'),
                    array('key' => 'contacts', 'icon' => 'dashicons-id'),
                    array('key' => 'base', 'icon' => 'dashicons-groups'),
                    array('key' => 'opportunities', 'icon' => 'dashicons-chart-line'),
                ),
            ),
            array(
                'label' => __('Activités', 'b2b-crm-maroc'),
                'items' => array(
                    array('key' => 'emails', 'icon' => 'dashicons-email'),
                    array('key' => 'calendar', 'icon' => 'dashicons-calendar'),
                    array('key' => 'meetings', 'icon' => 'dashicons-calendar-alt'),
                    array('key' => 'calls', 'icon' => 'dashicons-phone'),
                    array('key' => 'tasks', 'icon' => 'dashicons-yes-alt'),
                ),
            ),
            array(
                'label' => __('Support', 'b2b-crm-maroc'),
                'items' => array(
                    array('key' => 'tickets', 'icon' => 'dashicons-sos'),
                    array('key' => 'knowledge', 'icon' => 'dashicons-welcome-learn-more'),
                ),
            ),
            array(
                'label' => __('Business', 'b2b-crm-maroc'),
                'items' => array(
                    array('key' => 'documents', 'icon' => 'dashicons-media-document'),
                    array('key' => 'sales', 'icon' => 'dashicons-cart'),
                ),
            ),
            array(
                'label' => __('Outils', 'b2b-crm-maroc'),
                'items' => array(
                    array('key' => 'collect', 'icon' => 'dashicons-filter'),
                    array('key' => 'sources', 'icon' => 'dashicons-admin-links'),
                    array('key' => 'pipeline', 'icon' => 'dashicons-networking'),
                    array('key' => 'settings', 'icon' => 'dashicons-admin-generic'),
                ),
            ),
        );

        $current_label = $tabs[$tab] ?? $tabs['dashboard'];
        ?>
        <div class="wrap b2b-crm b2b-crm--app">
            <?php settings_errors('b2b-crm-maroc'); ?>
            <div class="b2b-crm__shell">
                <aside class="b2b-crm__sidebar">
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
                        <a class="b2b-crm__nav-link <?php echo $tab === 'dashboard' ? 'is-active' : ''; ?>" href="<?php echo esc_url(add_query_arg(array('page' => 'b2b-crm-maroc', 'tab' => 'dashboard'), admin_url('admin.php'))); ?>">
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
                                    $link_target = $is_enabled ? add_query_arg(array('page' => 'b2b-crm-maroc', 'tab' => $key), admin_url('admin.php')) : add_query_arg(array('page' => 'b2b-crm-maroc', 'tab' => 'settings'), admin_url('admin.php'));
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
                <div class="b2b-crm__content">
                    <div class="b2b-crm__topbar">
                        <div class="b2b-crm__page-title">
                            <h1><?php echo esc_html($current_label); ?></h1>
                            <p><?php echo esc_html__('Workspace CRM', 'b2b-crm-maroc'); ?></p>
                        </div>
                        <div class="b2b-crm__topbar-actions">
                            <label class="b2b-crm__search">
                                <span class="dashicons dashicons-search" aria-hidden="true"></span>
                                <input type="search" placeholder="<?php echo esc_attr__('Recherche globale', 'b2b-crm-maroc'); ?>" />
                            </label>
                            <a class="b2b-crm__settings-link" href="<?php echo esc_url(add_query_arg(array('page' => 'b2b-crm-maroc', 'tab' => 'settings'), admin_url('admin.php'))); ?>" aria-label="<?php echo esc_attr__('Paramétrage', 'b2b-crm-maroc'); ?>">
                                <span class="dashicons dashicons-admin-generic" aria-hidden="true"></span>
                            </a>
                        </div>
                    </div>

                    <?php if ($tab === 'dashboard') : ?>
                        <?php self::render_dashboard($data['items']); ?>
                    <?php elseif ($tab === 'accounts') : ?>
                        <?php self::render_accounts(); ?>
                    <?php elseif ($tab === 'contacts') : ?>
                        <?php self::render_contacts(); ?>
                    <?php elseif ($tab === 'opportunities') : ?>
                        <?php self::render_module_items(self::module_config_opportunities()); ?>
                    <?php elseif ($tab === 'emails') : ?>
                        <?php self::render_module_items(self::module_config_emails()); ?>
                    <?php elseif ($tab === 'calendar') : ?>
                        <?php self::render_module_items(self::module_config_calendar()); ?>
                    <?php elseif ($tab === 'meetings') : ?>
                        <?php self::render_module_items(self::module_config_meetings()); ?>
                    <?php elseif ($tab === 'calls') : ?>
                        <?php self::render_module_items(self::module_config_calls()); ?>
                    <?php elseif ($tab === 'tasks') : ?>
                        <?php self::render_module_items(self::module_config_tasks()); ?>
                    <?php elseif ($tab === 'tickets') : ?>
                        <?php self::render_module_items(self::module_config_tickets()); ?>
                    <?php elseif ($tab === 'knowledge') : ?>
                        <?php self::render_module_items(self::module_config_knowledge()); ?>
                    <?php elseif ($tab === 'documents') : ?>
                        <?php self::render_module_items(self::module_config_documents()); ?>
                    <?php elseif ($tab === 'sales') : ?>
                        <?php self::render_module_items(self::module_config_sales()); ?>
                    <?php elseif ($tab === 'collect') : ?>
                        <?php B2B_CRM_Views::render_collect(); ?>
                    <?php elseif ($tab === 'pipeline') : ?>
                        <?php self::render_pipeline($data['items']); ?>
                    <?php elseif ($tab === 'sources') : ?>
                        <?php B2B_CRM_Views::render_sources(); ?>
                    <?php elseif ($tab === 'settings') : ?>
                        <?php B2B_CRM_Views::render_settings(); ?>
                    <?php elseif ($tab === 'base') : ?>
                        <?php self::render_base($filters, $data, $total_pages, $paged); ?>
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

    private static function render_pipeline(array $items)
    {
        $columns = array(
            'new' => __('Nouveau', 'b2b-crm-maroc'),
            'qualified' => __('Qualifié', 'b2b-crm-maroc'),
            'contacted' => __('Contact initié', 'b2b-crm-maroc'),
            'proposal' => __('Proposition envoyée', 'b2b-crm-maroc'),
            'negotiation' => __('Négociation', 'b2b-crm-maroc'),
            'won' => __('Gagné', 'b2b-crm-maroc'),
        );

        $mapped = array();
        foreach ($columns as $key => $label) {
            $mapped[] = array(
                'label' => $label,
                'count' => $key === 'proposal' || $key === 'negotiation' || $key === 'won' ? 0 : self::count_status($items, $key),
                'items' => array(),
            );
        }

        foreach ($items as $item) {
            $status = $item['status'];
            if (!isset($columns[$status])) {
                continue;
            }
            $index = array_search($columns[$status], array_column($mapped, 'label'), true);
            if ($index === false) {
                continue;
            }
            $mapped[$index]['items'][] = array(
                'company_name' => $item['company_name'],
                'interest' => $item['interest_level'],
                'interest_label' => self::interests()[$item['interest_level']] ?? $item['interest_level'],
            );
        }

        B2B_CRM_Views::render_pipeline($mapped);
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
        return array(
            'accounts' => true,
            'contacts' => true,
            'base' => true,
            'opportunities' => true,
            'emails' => true,
            'calendar' => true,
            'meetings' => true,
            'calls' => true,
            'tasks' => true,
            'tickets' => true,
            'knowledge' => true,
            'documents' => true,
            'sales' => true,
            'collect' => true,
            'sources' => true,
            'pipeline' => true,
            'settings' => true,
        );
    }

    private static function render_base($filters, $data, $total_pages, $paged)
    {
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
                    <input type="hidden" name="city" value="<?php echo esc_attr($filters['city']); ?>" />
                    <input type="hidden" name="sector" value="<?php echo esc_attr($filters['sector']); ?>" />
                    <input type="hidden" name="interest_level" value="<?php echo esc_attr($filters['interest_level']); ?>" />
                    <?php wp_nonce_field('b2b_crm_export_csv'); ?>
                    <button class="b2b-crm__export" type="submit"><?php echo esc_html__('Exporter en CSV', 'b2b-crm-maroc'); ?></button>
                </form>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="b2b_crm_add_demo_leads" />
                    <?php wp_nonce_field('b2b_crm_add_demo_leads'); ?>
                    <button class="b2b-crm__ghost" type="submit"><?php echo esc_html__('Ajouter des données de démonstration', 'b2b-crm-maroc'); ?></button>
                </form>
            </div>
        </div>

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
                    <select name="interest_level">
                        <option value=""><?php echo esc_html__('Priorité', 'b2b-crm-maroc'); ?></option>
                        <?php foreach (self::interests() as $key => $label) : ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected($filters['interest_level'], $key); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
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
                        <th><?php echo esc_html__('Statut CRM', 'b2b-crm-maroc'); ?></th>
                        <th><?php echo esc_html__('Actions', 'b2b-crm-maroc'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['items'])) : ?>
                        <tr>
                            <td colspan="9"><?php echo esc_html__('Aucun lead pour le moment.', 'b2b-crm-maroc'); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($data['items'] as $lead) : ?>
                            <?php $social_links = self::social_links($lead); ?>
                            <tr>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(array('page' => 'b2b-crm-maroc', 'lead_id' => $lead['id']), admin_url('admin.php'))); ?>">
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
                                <td>
                                    <span class="b2b-crm__pill b2b-crm__pill--status"><?php echo esc_html(self::statuses()[$lead['status']] ?? $lead['status']); ?></span>
                                </td>
                                <td class="b2b-crm__actions">
                                    <a href="<?php echo esc_url(add_query_arg(array('page' => 'b2b-crm-maroc', 'lead_id' => $lead['id']), admin_url('admin.php'))); ?>"><span class="dashicons dashicons-edit"></span></a>
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

    private static function render_accounts()
    {
        $filters = array(
            'search' => isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '',
            'status' => isset($_GET['status']) ? sanitize_key($_GET['status']) : '',
        );
        $paged = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
        $per_page = 20;
        $data = B2B_CRM_Account_Repository::list($filters, $paged, $per_page);
        $total_pages = (int) ceil($data['total'] / $per_page);

        ?>
        <div class="b2b-crm__section b2b-crm__section--row">
            <div>
                <h2><?php echo esc_html__('Comptes', 'b2b-crm-maroc'); ?></h2>
                <p class="b2b-crm__muted"><?php echo esc_html__('Gérez les entreprises et comptes clients.', 'b2b-crm-maroc'); ?></p>
            </div>
        </div>

        <div class="b2b-crm__card">
            <form method="post" class="b2b-crm__form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('b2b_crm_add_account'); ?>
                <input type="hidden" name="action" value="b2b_crm_add_account" />
                <div class="b2b-crm__grid">
                    <label>
                        <span><?php echo esc_html__('Nom du compte', 'b2b-crm-maroc'); ?></span>
                        <input type="text" name="name" required />
                    </label>
                    <label>
                        <span><?php echo esc_html__('Secteur', 'b2b-crm-maroc'); ?></span>
                        <input type="text" name="industry" />
                    </label>
                    <label>
                        <span><?php echo esc_html__('Ville', 'b2b-crm-maroc'); ?></span>
                        <input type="text" name="city" />
                    </label>
                    <label>
                        <span><?php echo esc_html__('Email', 'b2b-crm-maroc'); ?></span>
                        <input type="email" name="email" />
                    </label>
                    <label>
                        <span><?php echo esc_html__('Téléphone', 'b2b-crm-maroc'); ?></span>
                        <input type="text" name="phone" />
                    </label>
                    <label>
                        <span><?php echo esc_html__('Site web', 'b2b-crm-maroc'); ?></span>
                        <input type="url" name="website" />
                    </label>
                    <label>
                        <span><?php echo esc_html__('Responsable', 'b2b-crm-maroc'); ?></span>
                        <input type="text" name="owner" />
                    </label>
                    <label>
                        <span><?php echo esc_html__('Statut', 'b2b-crm-maroc'); ?></span>
                        <select name="status">
                            <option value="active"><?php echo esc_html__('Actif', 'b2b-crm-maroc'); ?></option>
                            <option value="inactive"><?php echo esc_html__('Inactif', 'b2b-crm-maroc'); ?></option>
                        </select>
                    </label>
                </div>
                <label>
                    <span><?php echo esc_html__('Notes', 'b2b-crm-maroc'); ?></span>
                    <textarea name="notes" rows="3"></textarea>
                </label>
                <button class="b2b-crm__button" type="submit"><?php echo esc_html__('Ajouter un compte', 'b2b-crm-maroc'); ?></button>
            </form>
        </div>

        <div class="b2b-crm__table-card">
            <div class="b2b-crm__toolbar">
                <form method="get" class="b2b-crm__filters">
                    <input type="hidden" name="page" value="b2b-crm-maroc" />
                    <input type="hidden" name="tab" value="accounts" />
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
                        <th><?php echo esc_html__('Secteur', 'b2b-crm-maroc'); ?></th>
                        <th><?php echo esc_html__('Ville', 'b2b-crm-maroc'); ?></th>
                        <th><?php echo esc_html__('Email', 'b2b-crm-maroc'); ?></th>
                        <th><?php echo esc_html__('Téléphone', 'b2b-crm-maroc'); ?></th>
                        <th><?php echo esc_html__('Statut', 'b2b-crm-maroc'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['items'])) : ?>
                        <tr>
                            <td colspan="6"><?php echo esc_html__('Aucun compte pour le moment.', 'b2b-crm-maroc'); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($data['items'] as $account) : ?>
                            <tr>
                                <td><?php echo esc_html($account['name']); ?></td>
                                <td><?php echo esc_html($account['industry']); ?></td>
                                <td><?php echo esc_html($account['city']); ?></td>
                                <td><?php echo esc_html($account['email']); ?></td>
                                <td><?php echo esc_html($account['phone']); ?></td>
                                <td><?php echo esc_html($account['status']); ?></td>
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
                <p class="b2b-crm__muted"><?php echo esc_html__('Gérez les contacts clés des comptes.', 'b2b-crm-maroc'); ?></p>
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
                    </label>
                    <label>
                        <span><?php echo esc_html__('Compte', 'b2b-crm-maroc'); ?></span>
                        <input type="text" name="company" />
                    </label>
                    <label>
                        <span><?php echo esc_html__('Fonction', 'b2b-crm-maroc'); ?></span>
                        <input type="text" name="role" />
                    </label>
                    <label>
                        <span><?php echo esc_html__('Email', 'b2b-crm-maroc'); ?></span>
                        <input type="email" name="email" />
                    </label>
                    <label>
                        <span><?php echo esc_html__('Téléphone', 'b2b-crm-maroc'); ?></span>
                        <input type="text" name="phone" />
                    </label>
                    <label>
                        <span><?php echo esc_html__('Ville', 'b2b-crm-maroc'); ?></span>
                        <input type="text" name="city" />
                    </label>
                    <label>
                        <span><?php echo esc_html__('Statut', 'b2b-crm-maroc'); ?></span>
                        <select name="status">
                            <option value="active"><?php echo esc_html__('Actif', 'b2b-crm-maroc'); ?></option>
                            <option value="inactive"><?php echo esc_html__('Inactif', 'b2b-crm-maroc'); ?></option>
                        </select>
                    </label>
                </div>
                <label>
                    <span><?php echo esc_html__('Notes', 'b2b-crm-maroc'); ?></span>
                    <textarea name="notes" rows="3"></textarea>
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
                        <th><?php echo esc_html__('Compte', 'b2b-crm-maroc'); ?></th>
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

        ?>
        <div class="b2b-crm__section b2b-crm__section--row">
            <div>
                <h2><?php echo esc_html($config['title']); ?></h2>
                <p class="b2b-crm__muted"><?php echo esc_html($config['description']); ?></p>
            </div>
        </div>

        <div class="b2b-crm__card">
            <form method="post" class="b2b-crm__form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('b2b_crm_add_module_item'); ?>
                <input type="hidden" name="action" value="b2b_crm_add_module_item" />
                <input type="hidden" name="module_key" value="<?php echo esc_attr($config['key']); ?>" />
                <div class="b2b-crm__grid">
                    <?php foreach ($config['fields'] as $field) : ?>
                        <label>
                            <span><?php echo esc_html($field['label']); ?></span>
                            <?php if ($field['type'] === 'select') : ?>
                                <select name="<?php echo esc_attr($field['name']); ?>">
                                    <?php foreach ($field['options'] as $option_value => $option_label) : ?>
                                        <option value="<?php echo esc_attr($option_value); ?>"><?php echo esc_html($option_label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else : ?>
                                <input type="<?php echo esc_attr($field['type']); ?>" name="<?php echo esc_attr($field['name']); ?>" <?php echo !empty($field['required']) ? 'required' : ''; ?> />
                            <?php endif; ?>
                        </label>
                    <?php endforeach; ?>
                </div>
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
                                    <td><?php echo esc_html(self::module_item_value($item, $meta, $column['key'])); ?></td>
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
            return $item[$key];
        }
        return $meta[$key] ?? '';
    }

    private static function module_config_opportunities()
    {
        return array(
            'key' => 'opportunities',
            'title' => __('Opportunités', 'b2b-crm-maroc'),
            'description' => __('Suivez les opportunités commerciales en cours.', 'b2b-crm-maroc'),
            'button_label' => __('Ajouter une opportunité', 'b2b-crm-maroc'),
            'statuses' => array(
                'open' => __('Ouverte', 'b2b-crm-maroc'),
                'won' => __('Gagnée', 'b2b-crm-maroc'),
                'lost' => __('Perdue', 'b2b-crm-maroc'),
            ),
            'fields' => array(
                array('name' => 'title', 'label' => __('Nom', 'b2b-crm-maroc'), 'type' => 'text', 'required' => true),
                array('name' => 'meta[account]', 'label' => __('Compte', 'b2b-crm-maroc'), 'type' => 'text'),
                array('name' => 'amount', 'label' => __('Montant', 'b2b-crm-maroc'), 'type' => 'number'),
                array('name' => 'meta[stage]', 'label' => __('Étape', 'b2b-crm-maroc'), 'type' => 'text'),
                array('name' => 'due_date', 'label' => __('Date de clôture', 'b2b-crm-maroc'), 'type' => 'date'),
                array('name' => 'owner', 'label' => __('Responsable', 'b2b-crm-maroc'), 'type' => 'text'),
                array('name' => 'status', 'label' => __('Statut', 'b2b-crm-maroc'), 'type' => 'select', 'options' => array(
                    'open' => __('Ouverte', 'b2b-crm-maroc'),
                    'won' => __('Gagnée', 'b2b-crm-maroc'),
                    'lost' => __('Perdue', 'b2b-crm-maroc'),
                )),
            ),
            'columns' => array(
                array('key' => 'title', 'label' => __('Nom', 'b2b-crm-maroc')),
                array('key' => 'account', 'label' => __('Compte', 'b2b-crm-maroc')),
                array('key' => 'amount', 'label' => __('Montant', 'b2b-crm-maroc')),
                array('key' => 'stage', 'label' => __('Étape', 'b2b-crm-maroc')),
                array('key' => 'status', 'label' => __('Statut', 'b2b-crm-maroc')),
            ),
        );
    }

    private static function module_config_tasks()
    {
        return array(
            'key' => 'tasks',
            'title' => __('Tâches', 'b2b-crm-maroc'),
            'description' => __('Planifiez et assignez les tâches internes.', 'b2b-crm-maroc'),
            'button_label' => __('Ajouter une tâche', 'b2b-crm-maroc'),
            'statuses' => array(
                'todo' => __('À faire', 'b2b-crm-maroc'),
                'doing' => __('En cours', 'b2b-crm-maroc'),
                'done' => __('Terminée', 'b2b-crm-maroc'),
            ),
            'fields' => array(
                array('name' => 'title', 'label' => __('Titre', 'b2b-crm-maroc'), 'type' => 'text', 'required' => true),
                array('name' => 'owner', 'label' => __('Responsable', 'b2b-crm-maroc'), 'type' => 'text'),
                array('name' => 'due_date', 'label' => __('Échéance', 'b2b-crm-maroc'), 'type' => 'date'),
                array('name' => 'meta[priority]', 'label' => __('Priorité', 'b2b-crm-maroc'), 'type' => 'text'),
                array('name' => 'status', 'label' => __('Statut', 'b2b-crm-maroc'), 'type' => 'select', 'options' => array(
                    'todo' => __('À faire', 'b2b-crm-maroc'),
                    'doing' => __('En cours', 'b2b-crm-maroc'),
                    'done' => __('Terminée', 'b2b-crm-maroc'),
                )),
            ),
            'columns' => array(
                array('key' => 'title', 'label' => __('Titre', 'b2b-crm-maroc')),
                array('key' => 'owner', 'label' => __('Responsable', 'b2b-crm-maroc')),
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
            'button_label' => __('Ajouter un ticket', 'b2b-crm-maroc'),
            'statuses' => array(
                'open' => __('Ouvert', 'b2b-crm-maroc'),
                'pending' => __('En attente', 'b2b-crm-maroc'),
                'closed' => __('Clôturé', 'b2b-crm-maroc'),
            ),
            'fields' => array(
                array('name' => 'title', 'label' => __('Sujet', 'b2b-crm-maroc'), 'type' => 'text', 'required' => true),
                array('name' => 'meta[customer]', 'label' => __('Client', 'b2b-crm-maroc'), 'type' => 'text'),
                array('name' => 'owner', 'label' => __('Responsable', 'b2b-crm-maroc'), 'type' => 'text'),
                array('name' => 'meta[priority]', 'label' => __('Priorité', 'b2b-crm-maroc'), 'type' => 'text'),
                array('name' => 'status', 'label' => __('Statut', 'b2b-crm-maroc'), 'type' => 'select', 'options' => array(
                    'open' => __('Ouvert', 'b2b-crm-maroc'),
                    'pending' => __('En attente', 'b2b-crm-maroc'),
                    'closed' => __('Clôturé', 'b2b-crm-maroc'),
                )),
            ),
            'columns' => array(
                array('key' => 'title', 'label' => __('Sujet', 'b2b-crm-maroc')),
                array('key' => 'customer', 'label' => __('Client', 'b2b-crm-maroc')),
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
            'button_label' => __('Ajouter un document', 'b2b-crm-maroc'),
            'statuses' => array(
                'draft' => __('Brouillon', 'b2b-crm-maroc'),
                'published' => __('Publié', 'b2b-crm-maroc'),
            ),
            'fields' => array(
                array('name' => 'title', 'label' => __('Titre', 'b2b-crm-maroc'), 'type' => 'text', 'required' => true),
                array('name' => 'meta[category]', 'label' => __('Catégorie', 'b2b-crm-maroc'), 'type' => 'text'),
                array('name' => 'meta[file_url]', 'label' => __('Lien du fichier', 'b2b-crm-maroc'), 'type' => 'url'),
                array('name' => 'owner', 'label' => __('Responsable', 'b2b-crm-maroc'), 'type' => 'text'),
                array('name' => 'status', 'label' => __('Statut', 'b2b-crm-maroc'), 'type' => 'select', 'options' => array(
                    'draft' => __('Brouillon', 'b2b-crm-maroc'),
                    'published' => __('Publié', 'b2b-crm-maroc'),
                )),
            ),
            'columns' => array(
                array('key' => 'title', 'label' => __('Titre', 'b2b-crm-maroc')),
                array('key' => 'category', 'label' => __('Catégorie', 'b2b-crm-maroc')),
                array('key' => 'file_url', 'label' => __('Fichier', 'b2b-crm-maroc')),
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
            'button_label' => __('Ajouter un article', 'b2b-crm-maroc'),
            'statuses' => array(
                'draft' => __('Brouillon', 'b2b-crm-maroc'),
                'published' => __('Publié', 'b2b-crm-maroc'),
            ),
            'fields' => array(
                array('name' => 'title', 'label' => __('Titre', 'b2b-crm-maroc'), 'type' => 'text', 'required' => true),
                array('name' => 'meta[category]', 'label' => __('Catégorie', 'b2b-crm-maroc'), 'type' => 'text'),
                array('name' => 'owner', 'label' => __('Responsable', 'b2b-crm-maroc'), 'type' => 'text'),
                array('name' => 'status', 'label' => __('Statut', 'b2b-crm-maroc'), 'type' => 'select', 'options' => array(
                    'draft' => __('Brouillon', 'b2b-crm-maroc'),
                    'published' => __('Publié', 'b2b-crm-maroc'),
                )),
            ),
            'columns' => array(
                array('key' => 'title', 'label' => __('Titre', 'b2b-crm-maroc')),
                array('key' => 'category', 'label' => __('Catégorie', 'b2b-crm-maroc')),
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
            'button_label' => __('Ajouter une transaction', 'b2b-crm-maroc'),
            'statuses' => array(
                'open' => __('Ouverte', 'b2b-crm-maroc'),
                'closed' => __('Clôturée', 'b2b-crm-maroc'),
            ),
            'fields' => array(
                array('name' => 'title', 'label' => __('Libellé', 'b2b-crm-maroc'), 'type' => 'text', 'required' => true),
                array('name' => 'amount', 'label' => __('Montant', 'b2b-crm-maroc'), 'type' => 'number'),
                array('name' => 'meta[type]', 'label' => __('Type', 'b2b-crm-maroc'), 'type' => 'text'),
                array('name' => 'owner', 'label' => __('Responsable', 'b2b-crm-maroc'), 'type' => 'text'),
                array('name' => 'status', 'label' => __('Statut', 'b2b-crm-maroc'), 'type' => 'select', 'options' => array(
                    'open' => __('Ouverte', 'b2b-crm-maroc'),
                    'closed' => __('Clôturée', 'b2b-crm-maroc'),
                )),
            ),
            'columns' => array(
                array('key' => 'title', 'label' => __('Libellé', 'b2b-crm-maroc')),
                array('key' => 'type', 'label' => __('Type', 'b2b-crm-maroc')),
                array('key' => 'amount', 'label' => __('Montant', 'b2b-crm-maroc')),
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
            'button_label' => __('Ajouter un email', 'b2b-crm-maroc'),
            'statuses' => array(
                'draft' => __('Brouillon', 'b2b-crm-maroc'),
                'sent' => __('Envoyé', 'b2b-crm-maroc'),
            ),
            'fields' => array(
                array('name' => 'title', 'label' => __('Sujet', 'b2b-crm-maroc'), 'type' => 'text', 'required' => true),
                array('name' => 'meta[recipient]', 'label' => __('Destinataire', 'b2b-crm-maroc'), 'type' => 'email'),
                array('name' => 'owner', 'label' => __('Responsable', 'b2b-crm-maroc'), 'type' => 'text'),
                array('name' => 'status', 'label' => __('Statut', 'b2b-crm-maroc'), 'type' => 'select', 'options' => array(
                    'draft' => __('Brouillon', 'b2b-crm-maroc'),
                    'sent' => __('Envoyé', 'b2b-crm-maroc'),
                )),
            ),
            'columns' => array(
                array('key' => 'title', 'label' => __('Sujet', 'b2b-crm-maroc')),
                array('key' => 'recipient', 'label' => __('Destinataire', 'b2b-crm-maroc')),
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
            'button_label' => __('Ajouter un événement', 'b2b-crm-maroc'),
            'statuses' => array(
                'planned' => __('Planifié', 'b2b-crm-maroc'),
                'done' => __('Terminé', 'b2b-crm-maroc'),
            ),
            'fields' => array(
                array('name' => 'title', 'label' => __('Titre', 'b2b-crm-maroc'), 'type' => 'text', 'required' => true),
                array('name' => 'due_date', 'label' => __('Date', 'b2b-crm-maroc'), 'type' => 'date'),
                array('name' => 'owner', 'label' => __('Responsable', 'b2b-crm-maroc'), 'type' => 'text'),
                array('name' => 'status', 'label' => __('Statut', 'b2b-crm-maroc'), 'type' => 'select', 'options' => array(
                    'planned' => __('Planifié', 'b2b-crm-maroc'),
                    'done' => __('Terminé', 'b2b-crm-maroc'),
                )),
            ),
            'columns' => array(
                array('key' => 'title', 'label' => __('Titre', 'b2b-crm-maroc')),
                array('key' => 'due_date', 'label' => __('Date', 'b2b-crm-maroc')),
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
            'button_label' => __('Ajouter un rendez-vous', 'b2b-crm-maroc'),
            'statuses' => array(
                'planned' => __('Planifié', 'b2b-crm-maroc'),
                'done' => __('Terminé', 'b2b-crm-maroc'),
            ),
            'fields' => array(
                array('name' => 'title', 'label' => __('Sujet', 'b2b-crm-maroc'), 'type' => 'text', 'required' => true),
                array('name' => 'due_date', 'label' => __('Date', 'b2b-crm-maroc'), 'type' => 'date'),
                array('name' => 'meta[location]', 'label' => __('Lieu', 'b2b-crm-maroc'), 'type' => 'text'),
                array('name' => 'owner', 'label' => __('Responsable', 'b2b-crm-maroc'), 'type' => 'text'),
                array('name' => 'status', 'label' => __('Statut', 'b2b-crm-maroc'), 'type' => 'select', 'options' => array(
                    'planned' => __('Planifié', 'b2b-crm-maroc'),
                    'done' => __('Terminé', 'b2b-crm-maroc'),
                )),
            ),
            'columns' => array(
                array('key' => 'title', 'label' => __('Sujet', 'b2b-crm-maroc')),
                array('key' => 'location', 'label' => __('Lieu', 'b2b-crm-maroc')),
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
            'button_label' => __('Ajouter un appel', 'b2b-crm-maroc'),
            'statuses' => array(
                'planned' => __('Planifié', 'b2b-crm-maroc'),
                'done' => __('Terminé', 'b2b-crm-maroc'),
            ),
            'fields' => array(
                array('name' => 'title', 'label' => __('Sujet', 'b2b-crm-maroc'), 'type' => 'text', 'required' => true),
                array('name' => 'meta[phone]', 'label' => __('Téléphone', 'b2b-crm-maroc'), 'type' => 'text'),
                array('name' => 'due_date', 'label' => __('Date', 'b2b-crm-maroc'), 'type' => 'date'),
                array('name' => 'owner', 'label' => __('Responsable', 'b2b-crm-maroc'), 'type' => 'text'),
                array('name' => 'status', 'label' => __('Statut', 'b2b-crm-maroc'), 'type' => 'select', 'options' => array(
                    'planned' => __('Planifié', 'b2b-crm-maroc'),
                    'done' => __('Terminé', 'b2b-crm-maroc'),
                )),
            ),
            'columns' => array(
                array('key' => 'title', 'label' => __('Sujet', 'b2b-crm-maroc')),
                array('key' => 'phone', 'label' => __('Téléphone', 'b2b-crm-maroc')),
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
