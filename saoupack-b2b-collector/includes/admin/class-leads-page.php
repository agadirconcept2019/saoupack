<?php

if (!defined('ABSPATH')) {
    exit;
}

class Saoupack_B2B_Collector_Leads_Page
{
    public static function register()
    {
        add_action('admin_post_saoupack_b2b_create_lead', array(__CLASS__, 'handle_create'));
        add_action('admin_post_saoupack_b2b_update_lead', array(__CLASS__, 'handle_update'));
        add_action('admin_post_saoupack_b2b_delete_lead', array(__CLASS__, 'handle_delete'));
        add_action('admin_post_saoupack_b2b_send_email', array(__CLASS__, 'handle_send_email'));
    }

    public static function render()
    {
        if (!current_user_can(SAOUPACK_B2B_COLLECTOR_CAP)) {
            wp_die(__('Accès refusé.', 'saoupack-b2b-collector'));
        }

        $action = isset($_GET['action']) ? sanitize_key($_GET['action']) : '';
        $lead_id = isset($_GET['lead_id']) ? absint($_GET['lead_id']) : 0;

        echo '<div class="wrap crm-app b2b-crm b2b-crm--app">';
        settings_errors('saoupack-b2b-collector');

        if ($action === 'add' || ($action === 'edit' && $lead_id)) {
            $lead = $lead_id ? Saoupack_B2B_Collector_Leads_Service::get($lead_id) : array();
            Saoupack_B2B_Collector_Lead_Form::render($lead ? $lead : array());
            echo '</div>';
            return;
        }

        if ($action === 'send-email' && $lead_id) {
            self::render_email_form($lead_id);
            echo '</div>';
            return;
        }

        self::render_list();
        echo '</div>';
    }

    private static function render_list()
    {
        $filters = array(
            'search' => isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '',
            'crm_status' => isset($_GET['status']) ? sanitize_key($_GET['status']) : '',
            'priority' => isset($_GET['priority']) ? sanitize_key($_GET['priority']) : '',
        );

        $page = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
        $per_page = 20;

        $data = Saoupack_B2B_Collector_Leads_Service::list($filters, $page, $per_page);
        $total_pages = (int) ceil($data['total'] / $per_page);

        ?>
        <div class="b2b-crm__section b2b-crm__section--row">
            <div>
                <h1><?php echo esc_html__('Leads (V1)', 'saoupack-b2b-collector'); ?></h1>
                <p class="b2b-crm__muted"><?php echo esc_html__('Table conforme V1 (REST + table dédiée).', 'saoupack-b2b-collector'); ?></p>
            </div>
            <div class="b2b-crm__section-actions">
                <a class="b2b-crm__button" href="<?php echo esc_url(admin_url('admin.php?page=saoupack-b2b-collector-leads&action=add')); ?>"><?php echo esc_html__('Ajouter un lead', 'saoupack-b2b-collector'); ?></a>
            </div>
        </div>

        <div class="b2b-crm__table-card">
            <div class="b2b-crm__toolbar">
                <form method="get" class="b2b-crm__filters">
                    <input type="hidden" name="page" value="saoupack-b2b-collector-leads" />
                    <input type="search" name="s" placeholder="<?php echo esc_attr__('Recherche', 'saoupack-b2b-collector'); ?>" value="<?php echo esc_attr($filters['search']); ?>" />
                    <select name="status">
                        <option value=""><?php echo esc_html__('Statut CRM', 'saoupack-b2b-collector'); ?></option>
                        <?php foreach (Saoupack_B2B_Collector_Sanitizer::crm_statuses() as $status) : ?>
                            <option value="<?php echo esc_attr($status); ?>" <?php selected($filters['crm_status'], $status); ?>><?php echo esc_html($status); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="priority">
                        <option value=""><?php echo esc_html__('Priorité', 'saoupack-b2b-collector'); ?></option>
                        <?php foreach (Saoupack_B2B_Collector_Sanitizer::priorities() as $priority) : ?>
                            <option value="<?php echo esc_attr($priority); ?>" <?php selected($filters['priority'], $priority); ?>><?php echo esc_html($priority); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="b2b-crm__ghost" type="submit"><?php echo esc_html__('Filtrer', 'saoupack-b2b-collector'); ?></button>
                </form>
            </div>

            <table class="b2b-crm__table">
                <thead>
                    <tr>
                        <th><?php echo esc_html__('Société', 'saoupack-b2b-collector'); ?></th>
                        <th><?php echo esc_html__('Téléphone', 'saoupack-b2b-collector'); ?></th>
                        <th><?php echo esc_html__('E-mail', 'saoupack-b2b-collector'); ?></th>
                        <th><?php echo esc_html__('Site Web', 'saoupack-b2b-collector'); ?></th>
                        <th><?php echo esc_html__('Réseaux', 'saoupack-b2b-collector'); ?></th>
                        <th><?php echo esc_html__('Priorité', 'saoupack-b2b-collector'); ?></th>
                        <th><?php echo esc_html__('Statut CRM', 'saoupack-b2b-collector'); ?></th>
                        <th><?php echo esc_html__('Actions', 'saoupack-b2b-collector'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['items'])) : ?>
                        <tr>
                            <td colspan="8"><?php echo esc_html__('Aucun lead pour le moment.', 'saoupack-b2b-collector'); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($data['items'] as $lead) : ?>
                            <?php $social_links = self::social_links($lead); ?>
                            <tr>
                                <td><?php echo esc_html($lead['company_name']); ?></td>
                                <td><?php echo esc_html($lead['phone']); ?></td>
                                <td><?php echo esc_html($lead['email']); ?></td>
                                <td>
                                    <?php if (!empty($lead['website'])) : ?>
                                        <a href="<?php echo esc_url($lead['website']); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($lead['website']); ?></a>
                                    <?php else : ?>
                                        <span class="dashicons dashicons-minus"></span>
                                    <?php endif; ?>
                                </td>
                                <td class="b2b-crm__icons">
                                    <?php if (empty($social_links)) : ?>
                                        <span class="dashicons dashicons-minus"></span>
                                    <?php else : ?>
                                        <?php foreach ($social_links as $link) : ?>
                                            <a href="<?php echo esc_url($link['url']); ?>" target="_blank" rel="noopener noreferrer" title="<?php echo esc_attr($link['label']); ?>">
                                                <span class="dashicons <?php echo esc_attr($link['icon']); ?>" aria-hidden="true"></span>
                                            </a>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </td>
                                <td><span class="b2b-crm__pill b2b-crm__pill--<?php echo esc_attr($lead['priority']); ?>"><?php echo esc_html($lead['priority']); ?></span></td>
                                <td><span class="b2b-crm__pill b2b-crm__pill--status"><?php echo esc_html($lead['crm_status']); ?></span></td>
                                <td class="b2b-crm__actions">
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=saoupack-b2b-collector-leads&action=edit&lead_id=' . (int) $lead['id'])); ?>"><?php echo esc_html__('Éditer', 'saoupack-b2b-collector'); ?></a>
                                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=saoupack_b2b_delete_lead&lead_id=' . (int) $lead['id']), 'saoupack_b2b_delete_lead')); ?>" onclick="return confirm('<?php echo esc_js(__('Confirmer la suppression ?', 'saoupack-b2b-collector')); ?>');"><?php echo esc_html__('Supprimer', 'saoupack-b2b-collector'); ?></a>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=saoupack-b2b-collector-leads&action=send-email&lead_id=' . (int) $lead['id'])); ?>"><?php echo esc_html__('Envoyer un mail', 'saoupack-b2b-collector'); ?></a>
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
                            'total' => $total_pages,
                            'current' => $page,
                        ));
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    private static function render_email_form($lead_id)
    {
        $lead = Saoupack_B2B_Collector_Leads_Service::get($lead_id);
        if (!$lead) {
            echo '<div class="notice notice-error"><p>' . esc_html__('Lead introuvable.', 'saoupack-b2b-collector') . '</p></div>';
            return;
        }
        ?>
        <div class="b2b-crm__card">
            <h2><?php echo esc_html__('Envoyer un email', 'saoupack-b2b-collector'); ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="b2b-crm__form">
                <?php wp_nonce_field('saoupack_b2b_send_email'); ?>
                <input type="hidden" name="action" value="saoupack_b2b_send_email" />
                <input type="hidden" name="lead_id" value="<?php echo esc_attr($lead['id']); ?>" />
                <label>
                    <span><?php echo esc_html__('Destinataire', 'saoupack-b2b-collector'); ?></span>
                    <input type="text" readonly value="<?php echo esc_attr($lead['email']); ?>" />
                </label>
                <label>
                    <span><?php echo esc_html__('Sujet', 'saoupack-b2b-collector'); ?></span>
                    <input type="text" name="subject" required />
                </label>
                <label>
                    <span><?php echo esc_html__('Message', 'saoupack-b2b-collector'); ?></span>
                    <textarea name="message" rows="6" required></textarea>
                </label>
                <div class="b2b-crm__section-actions">
                    <button class="b2b-crm__button" type="submit"><?php echo esc_html__('Envoyer', 'saoupack-b2b-collector'); ?></button>
                    <a class="b2b-crm__ghost" href="<?php echo esc_url(admin_url('admin.php?page=saoupack-b2b-collector-leads')); ?>"><?php echo esc_html__('Retour', 'saoupack-b2b-collector'); ?></a>
                </div>
            </form>
        </div>
        <?php
    }

    public static function handle_create()
    {
        if (!current_user_can(SAOUPACK_B2B_COLLECTOR_CAP)) {
            wp_die(__('Accès refusé.', 'saoupack-b2b-collector'));
        }

        check_admin_referer('saoupack_b2b_create_lead');

        $data = Saoupack_B2B_Collector_Sanitizer::sanitize_lead(wp_unslash($_POST));
        if (empty($data['company_name'])) {
            add_settings_error('saoupack-b2b-collector', 'missing_company', __('La société est requise.', 'saoupack-b2b-collector'), 'error');
            wp_safe_redirect(admin_url('admin.php?page=saoupack-b2b-collector-leads&action=add'));
            exit;
        }

        Saoupack_B2B_Collector_Leads_Service::create($data);
        add_settings_error('saoupack-b2b-collector', 'lead_created', __('Lead créé.', 'saoupack-b2b-collector'), 'updated');

        wp_safe_redirect(admin_url('admin.php?page=saoupack-b2b-collector-leads'));
        exit;
    }

    public static function handle_update()
    {
        if (!current_user_can(SAOUPACK_B2B_COLLECTOR_CAP)) {
            wp_die(__('Accès refusé.', 'saoupack-b2b-collector'));
        }

        check_admin_referer('saoupack_b2b_update_lead');

        $lead_id = isset($_POST['lead_id']) ? absint($_POST['lead_id']) : 0;
        if (!$lead_id) {
            wp_die(__('Lead invalide.', 'saoupack-b2b-collector'));
        }

        $data = Saoupack_B2B_Collector_Sanitizer::sanitize_lead(wp_unslash($_POST), true);
        Saoupack_B2B_Collector_Leads_Service::update($lead_id, $data);

        add_settings_error('saoupack-b2b-collector', 'lead_updated', __('Lead mis à jour.', 'saoupack-b2b-collector'), 'updated');
        wp_safe_redirect(admin_url('admin.php?page=saoupack-b2b-collector-leads'));
        exit;
    }

    public static function handle_delete()
    {
        if (!current_user_can(SAOUPACK_B2B_COLLECTOR_CAP)) {
            wp_die(__('Accès refusé.', 'saoupack-b2b-collector'));
        }

        check_admin_referer('saoupack_b2b_delete_lead');

        $lead_id = isset($_GET['lead_id']) ? absint($_GET['lead_id']) : 0;
        if ($lead_id) {
            Saoupack_B2B_Collector_Leads_Service::delete($lead_id);
            add_settings_error('saoupack-b2b-collector', 'lead_deleted', __('Lead supprimé.', 'saoupack-b2b-collector'), 'updated');
        }

        wp_safe_redirect(admin_url('admin.php?page=saoupack-b2b-collector-leads'));
        exit;
    }

    public static function handle_send_email()
    {
        if (!current_user_can(SAOUPACK_B2B_COLLECTOR_CAP)) {
            wp_die(__('Accès refusé.', 'saoupack-b2b-collector'));
        }

        check_admin_referer('saoupack_b2b_send_email');

        $lead_id = isset($_POST['lead_id']) ? absint($_POST['lead_id']) : 0;
        $subject = isset($_POST['subject']) ? sanitize_text_field(wp_unslash($_POST['subject'])) : '';
        $message = isset($_POST['message']) ? wp_kses_post(wp_unslash($_POST['message'])) : '';

        $result = Saoupack_B2B_Collector_Leads_Service::send_email($lead_id, array(
            'subject' => $subject,
            'message' => $message,
        ));

        if (is_wp_error($result)) {
            add_settings_error('saoupack-b2b-collector', 'email_failed', $result->get_error_message(), 'error');
        } else {
            add_settings_error('saoupack-b2b-collector', 'email_sent', __('Email envoyé.', 'saoupack-b2b-collector'), 'updated');
        }

        wp_safe_redirect(admin_url('admin.php?page=saoupack-b2b-collector-leads'));
        exit;
    }

    private static function social_links(array $lead)
    {
        if (empty($lead['social_links'])) {
            return array();
        }

        $decoded = json_decode($lead['social_links'], true);
        if (!is_array($decoded)) {
            return array();
        }

        $map = array(
            'linkedin' => array('icon' => 'dashicons-linkedin', 'label' => 'LinkedIn'),
            'facebook' => array('icon' => 'dashicons-facebook', 'label' => 'Facebook'),
            'instagram' => array('icon' => 'dashicons-instagram', 'label' => 'Instagram'),
            'x' => array('icon' => 'dashicons-twitter', 'label' => 'X'),
            'youtube' => array('icon' => 'dashicons-video-alt3', 'label' => 'YouTube'),
            'tiktok' => array('icon' => 'dashicons-video-alt2', 'label' => 'TikTok'),
        );

        $links = array();
        foreach ($decoded as $key => $url) {
            if (empty($url)) {
                continue;
            }
            $icon = $map[$key]['icon'] ?? 'dashicons-admin-links';
            $label = $map[$key]['label'] ?? $key;
            $links[] = array(
                'url' => $url,
                'icon' => $icon,
                'label' => $label,
            );
        }

        return $links;
    }
}
