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
            'dashboard' => __('Dashboard', 'b2b-crm-maroc'),
            'collect' => __('Collecte', 'b2b-crm-maroc'),
            'base' => __('Base SQL', 'b2b-crm-maroc'),
            'pipeline' => __('CRM Pipeline', 'b2b-crm-maroc'),
        );

        ?>
        <div class="wrap b2b-crm b2b-crm--app">
            <?php settings_errors('b2b-crm-maroc'); ?>
            <div class="b2b-crm__topbar">
                <div class="b2b-crm__brand">
                    <span class="b2b-crm__logo">🛡️</span>
                    <div>
                        <strong>Morocco Collector <span>B2B</span></strong>
                        <div class="b2b-crm__subtitle"><?php echo esc_html__('B2B Morocco Data Collector', 'b2b-crm-maroc'); ?></div>
                    </div>
                </div>
                <div class="b2b-crm__topbar-actions">
                    <span class="b2b-crm__device"><?php echo esc_html__('Device', 'b2b-crm-maroc'); ?></span>
                    <span class="dashicons dashicons-admin-generic"></span>
                </div>
            </div>

            <nav class="b2b-crm__tabs">
                <?php foreach ($tabs as $key => $label) : ?>
                    <a class="<?php echo $tab === $key ? 'is-active' : ''; ?>" href="<?php echo esc_url(add_query_arg(array('page' => 'b2b-crm-maroc', 'tab' => $key), admin_url('admin.php'))); ?>">
                        <?php echo esc_html($label); ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <?php if ($tab === 'dashboard') : ?>
                <?php self::render_dashboard($data['items']); ?>
            <?php elseif ($tab === 'collect') : ?>
                <?php B2B_CRM_Views::render_collect(); ?>
            <?php elseif ($tab === 'pipeline') : ?>
                <?php self::render_pipeline($data['items']); ?>
            <?php else : ?>
                <?php self::render_base($filters, $data, $total_pages, $paged); ?>
            <?php endif; ?>
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

    private static function render_base($filters, $data, $total_pages, $paged)
    {
        ?>
        <div class="b2b-crm__section b2b-crm__section--row">
            <div>
                <h2><?php echo esc_html__('Gestion de la Base de Leads', 'b2b-crm-maroc'); ?></h2>
            </div>
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
                        <th><?php echo esc_html__('Contact', 'b2b-crm-maroc'); ?></th>
                        <th><?php echo esc_html__('Web & Sociaux', 'b2b-crm-maroc'); ?></th>
                        <th><?php echo esc_html__('Priorité', 'b2b-crm-maroc'); ?></th>
                        <th><?php echo esc_html__('Statut CRM', 'b2b-crm-maroc'); ?></th>
                        <th><?php echo esc_html__('Actions', 'b2b-crm-maroc'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['items'])) : ?>
                        <tr>
                            <td colspan="6"><?php echo esc_html__('Aucun lead pour le moment.', 'b2b-crm-maroc'); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($data['items'] as $lead) : ?>
                            <tr>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(array('page' => 'b2b-crm-maroc', 'lead_id' => $lead['id']), admin_url('admin.php'))); ?>">
                                        <?php echo esc_html($lead['company_name']); ?>
                                    </a>
                                    <div class="b2b-crm__sub"><?php echo esc_html(trim($lead['city'] . ' · ' . $lead['sector'], ' ·')); ?></div>
                                </td>
                                <td>
                                    <div class="b2b-crm__contact-main"><?php echo esc_html($lead['phone']); ?></div>
                                    <div class="b2b-crm__sub"><?php echo esc_html($lead['email']); ?></div>
                                </td>
                                <td class="b2b-crm__icons">
                                    <span class="dashicons dashicons-admin-site"></span>
                                    <span class="dashicons dashicons-linkedin"></span>
                                    <span class="dashicons dashicons-facebook"></span>
                                    <span class="dashicons dashicons-instagram"></span>
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

    private static function interests()
    {
        return array(
            'low' => __('Faible', 'b2b-crm-maroc'),
            'medium' => __('Moyen', 'b2b-crm-maroc'),
            'high' => __('Fort', 'b2b-crm-maroc'),
        );
    }
}
