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
                'b2b-crm-frontend',
                B2B_CRM_MAROC_URL . 'assets/css/frontend.css',
                array(),
                B2B_CRM_MAROC_VERSION
            );
        }
    }

    public static function render()
    {
        if (!is_user_logged_in()) {
            return '<p>' . esc_html__('Veuillez vous connecter pour accéder au CRM.', 'b2b-crm-maroc') . '</p>';
        }

        if (!current_user_can(B2B_CRM_MAROC_LEADS_CAP)) {
            return '<p>' . esc_html__('Accès refusé.', 'b2b-crm-maroc') . '</p>';
        }

        $message = self::handle_submission();
        $filters = array(
            'search' => isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '',
            'status' => isset($_GET['status']) ? sanitize_key($_GET['status']) : '',
            'city' => isset($_GET['city']) ? sanitize_text_field(wp_unslash($_GET['city'])) : '',
            'sector' => isset($_GET['sector']) ? sanitize_text_field(wp_unslash($_GET['sector'])) : '',
            'interest_level' => isset($_GET['interest_level']) ? sanitize_key($_GET['interest_level']) : '',
        );

        $paged = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
        $per_page = 10;
        $data = B2B_CRM_Lead_Repository::list($filters, $paged, $per_page);
        $total_pages = (int) ceil($data['total'] / $per_page);

        ob_start();
        ?>
        <div class="b2b-crm-frontend">
            <h2><?php echo esc_html__('CRM Leads', 'b2b-crm-maroc'); ?></h2>
            <?php if ($message) : ?>
                <div class="b2b-crm-frontend__notice"><?php echo esc_html($message); ?></div>
            <?php endif; ?>

            <form method="post" class="b2b-crm-frontend__form">
                <?php wp_nonce_field('b2b_crm_frontend_add_lead', 'b2b_crm_frontend_nonce'); ?>
                <input type="hidden" name="b2b_crm_frontend_action" value="add_lead" />
                <div class="b2b-crm-frontend__grid">
                    <label>
                        <span><?php echo esc_html__('Société', 'b2b-crm-maroc'); ?></span>
                        <input type="text" name="company_name" required />
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
                        <span><?php echo esc_html__('Secteur', 'b2b-crm-maroc'); ?></span>
                        <input type="text" name="sector" />
                    </label>
                    <label>
                        <span><?php echo esc_html__('Tags', 'b2b-crm-maroc'); ?></span>
                        <input type="text" name="tags" placeholder="<?php echo esc_attr__('ex: priorité, retail', 'b2b-crm-maroc'); ?>" />
                    </label>
                    <label>
                        <span><?php echo esc_html__('Statut', 'b2b-crm-maroc'); ?></span>
                        <select name="status">
                            <?php foreach (self::statuses() as $key => $label) : ?>
                                <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>
                        <span><?php echo esc_html__('Priorité', 'b2b-crm-maroc'); ?></span>
                        <select name="interest_level">
                            <?php foreach (self::interests() as $key => $label) : ?>
                                <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
                <button class="b2b-crm-frontend__button" type="submit"><?php echo esc_html__('Ajouter le lead', 'b2b-crm-maroc'); ?></button>
            </form>

            <form method="get" class="b2b-crm-frontend__filters">
                <input type="search" name="s" placeholder="<?php echo esc_attr__('Recherche', 'b2b-crm-maroc'); ?>" value="<?php echo esc_attr($filters['search']); ?>" />
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
                <button type="submit" class="b2b-crm-frontend__button b2b-crm-frontend__button--ghost"><?php echo esc_html__('Filtrer', 'b2b-crm-maroc'); ?></button>
            </form>

            <div class="b2b-crm-frontend__table">
                <div class="b2b-crm-frontend__table-row b2b-crm-frontend__table-row--head">
                    <span><?php echo esc_html__('Société', 'b2b-crm-maroc'); ?></span>
                    <span><?php echo esc_html__('Email', 'b2b-crm-maroc'); ?></span>
                    <span><?php echo esc_html__('Téléphone', 'b2b-crm-maroc'); ?></span>
                    <span><?php echo esc_html__('Statut', 'b2b-crm-maroc'); ?></span>
                    <span><?php echo esc_html__('Priorité', 'b2b-crm-maroc'); ?></span>
                </div>
                <?php if (empty($data['items'])) : ?>
                    <div class="b2b-crm-frontend__empty"><?php echo esc_html__('Aucun lead trouvé.', 'b2b-crm-maroc'); ?></div>
                <?php else : ?>
                    <?php foreach ($data['items'] as $lead) : ?>
                        <div class="b2b-crm-frontend__table-row">
                            <span><?php echo esc_html($lead['company_name']); ?></span>
                            <span><?php echo esc_html($lead['email']); ?></span>
                            <span><?php echo esc_html($lead['phone']); ?></span>
                            <span><?php echo esc_html(self::statuses()[$lead['status']] ?? $lead['status']); ?></span>
                            <span><?php echo esc_html(self::interests()[$lead['interest_level']] ?? $lead['interest_level']); ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <?php if ($total_pages > 1) : ?>
                <div class="b2b-crm-frontend__pagination">
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
        </div>
        <?php
        return ob_get_clean();
    }

    private static function handle_submission()
    {
        if (empty($_POST['b2b_crm_frontend_action']) || $_POST['b2b_crm_frontend_action'] !== 'add_lead') {
            return '';
        }

        if (!isset($_POST['b2b_crm_frontend_nonce']) || !wp_verify_nonce($_POST['b2b_crm_frontend_nonce'], 'b2b_crm_frontend_add_lead')) {
            return __('Nonce invalide.', 'b2b-crm-maroc');
        }

        $data = B2B_CRM_Sanitizer::lead_fields(wp_unslash($_POST));
        if (empty($data['company_name'])) {
            return __('Le nom de la société est requis.', 'b2b-crm-maroc');
        }

        B2B_CRM_Lead_Repository::upsert($data);
        do_action('b2b_crm_lead_created', $data);

        return __('Lead ajouté.', 'b2b-crm-maroc');
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
