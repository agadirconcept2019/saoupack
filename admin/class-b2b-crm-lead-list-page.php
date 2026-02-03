<?php

if (!defined('ABSPATH')) {
    exit;
}

class B2B_CRM_Lead_List_Page
{
    public static function render()
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

        ?>
        <div class="wrap b2b-crm">
            <h1><?php echo esc_html__('CRM B2B Maroc', 'b2b-crm-maroc'); ?></h1>

            <div class="b2b-crm__toolbar">
                <form method="get" class="b2b-crm__filters">
                    <input type="hidden" name="page" value="b2b-crm-maroc" />
                    <input type="search" name="s" placeholder="<?php echo esc_attr__('Recherche rapide', 'b2b-crm-maroc'); ?>" value="<?php echo esc_attr($filters['search']); ?>" />
                    <select name="status">
                        <option value=""><?php echo esc_html__('Statut', 'b2b-crm-maroc'); ?></option>
                        <?php foreach (self::statuses() as $key => $label) : ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected($filters['status'], $key); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="interest_level">
                        <option value=""><?php echo esc_html__('Intérêt', 'b2b-crm-maroc'); ?></option>
                        <?php foreach (self::interests() as $key => $label) : ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected($filters['interest_level'], $key); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="city" placeholder="<?php echo esc_attr__('Ville', 'b2b-crm-maroc'); ?>" value="<?php echo esc_attr($filters['city']); ?>" />
                    <input type="text" name="sector" placeholder="<?php echo esc_attr__('Secteur', 'b2b-crm-maroc'); ?>" value="<?php echo esc_attr($filters['sector']); ?>" />
                    <button class="button"><?php echo esc_html__('Filtrer', 'b2b-crm-maroc'); ?></button>
                </form>
                <div class="b2b-crm__meta">
                    <span class="b2b-crm__count"><?php echo esc_html(sprintf(__('%d leads', 'b2b-crm-maroc'), $data['total'])); ?></span>
                </div>
            </div>

            <table class="widefat fixed striped b2b-crm__table">
                <thead>
                    <tr>
                        <th><?php echo esc_html__('Société', 'b2b-crm-maroc'); ?></th>
                        <th><?php echo esc_html__('Contact', 'b2b-crm-maroc'); ?></th>
                        <th><?php echo esc_html__('Ville', 'b2b-crm-maroc'); ?></th>
                        <th><?php echo esc_html__('Secteur', 'b2b-crm-maroc'); ?></th>
                        <th><?php echo esc_html__('Statut', 'b2b-crm-maroc'); ?></th>
                        <th><?php echo esc_html__('Intérêt', 'b2b-crm-maroc'); ?></th>
                        <th><?php echo esc_html__('Source', 'b2b-crm-maroc'); ?></th>
                        <th><?php echo esc_html__('Collecté', 'b2b-crm-maroc'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['items'])) : ?>
                        <tr>
                            <td colspan="8"><?php echo esc_html__('Aucun lead pour le moment.', 'b2b-crm-maroc'); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($data['items'] as $lead) : ?>
                            <tr>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(array('page' => 'b2b-crm-maroc', 'lead_id' => $lead['id']), admin_url('admin.php'))); ?>">
                                        <?php echo esc_html($lead['company_name']); ?>
                                    </a>
                                </td>
                                <td>
                                    <div><?php echo esc_html($lead['contact_name']); ?></div>
                                    <small><?php echo esc_html($lead['email']); ?></small>
                                </td>
                                <td><?php echo esc_html($lead['city']); ?></td>
                                <td><?php echo esc_html($lead['sector']); ?></td>
                                <td>
                                    <select class="b2b-crm__quick" data-lead-id="<?php echo esc_attr($lead['id']); ?>" data-field="status">
                                        <?php foreach (self::statuses() as $key => $label) : ?>
                                            <option value="<?php echo esc_attr($key); ?>" <?php selected($lead['status'], $key); ?>><?php echo esc_html($label); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <select class="b2b-crm__quick" data-lead-id="<?php echo esc_attr($lead['id']); ?>" data-field="interest_level">
                                        <?php foreach (self::interests() as $key => $label) : ?>
                                            <option value="<?php echo esc_attr($key); ?>" <?php selected($lead['interest_level'], $key); ?>><?php echo esc_html($label); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td><?php echo esc_html($lead['source']); ?></td>
                                <td><?php echo esc_html(mysql2date('d/m/Y', $lead['collected_at'])); ?></td>
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
