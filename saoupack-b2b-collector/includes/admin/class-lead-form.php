<?php

if (!defined('ABSPATH')) {
    exit;
}

class Saoupack_B2B_Collector_Lead_Form
{
    public static function render(array $lead = array())
    {
        $is_edit = !empty($lead['id']);
        $action = $is_edit ? 'saoupack_b2b_update_lead' : 'saoupack_b2b_create_lead';
        $nonce = $is_edit ? 'saoupack_b2b_update_lead' : 'saoupack_b2b_create_lead';
        $social = array();
        if (!empty($lead['social_links'])) {
            $decoded = json_decode($lead['social_links'], true);
            if (is_array($decoded)) {
                $social = $decoded;
            }
        }
        ?>
        <div class="b2b-crm__card">
            <h2><?php echo esc_html($is_edit ? __('Modifier le lead', 'saoupack-b2b-collector') : __('Ajouter un lead', 'saoupack-b2b-collector')); ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="b2b-crm__form">
                <?php wp_nonce_field($nonce); ?>
                <input type="hidden" name="action" value="<?php echo esc_attr($action); ?>" />
                <?php if ($is_edit) : ?>
                    <input type="hidden" name="lead_id" value="<?php echo esc_attr($lead['id']); ?>" />
                <?php endif; ?>
                <div class="b2b-crm__grid">
                    <label>
                        <span><?php echo esc_html__('Société', 'saoupack-b2b-collector'); ?></span>
                        <input type="text" name="company_name" value="<?php echo esc_attr($lead['company_name'] ?? ''); ?>" required />
                    </label>
                    <label>
                        <span><?php echo esc_html__('Ville', 'saoupack-b2b-collector'); ?></span>
                        <input type="text" name="city" value="<?php echo esc_attr($lead['city'] ?? ''); ?>" />
                    </label>
                    <label>
                        <span><?php echo esc_html__('Téléphone', 'saoupack-b2b-collector'); ?></span>
                        <input type="text" name="phone" value="<?php echo esc_attr($lead['phone'] ?? ''); ?>" />
                    </label>
                    <label>
                        <span><?php echo esc_html__('Email', 'saoupack-b2b-collector'); ?></span>
                        <input type="email" name="email" value="<?php echo esc_attr($lead['email'] ?? ''); ?>" />
                    </label>
                    <label>
                        <span><?php echo esc_html__('Site web', 'saoupack-b2b-collector'); ?></span>
                        <input type="url" name="website" value="<?php echo esc_attr($lead['website'] ?? ''); ?>" />
                    </label>
                    <label>
                        <span><?php echo esc_html__('Source', 'saoupack-b2b-collector'); ?></span>
                        <input type="text" name="source" value="<?php echo esc_attr($lead['source'] ?? ''); ?>" />
                    </label>
                    <label>
                        <span><?php echo esc_html__('Priorité', 'saoupack-b2b-collector'); ?></span>
                        <select name="priority">
                            <?php foreach (Saoupack_B2B_Collector_Sanitizer::priorities() as $priority) : ?>
                                <option value="<?php echo esc_attr($priority); ?>" <?php selected($lead['priority'] ?? 'medium', $priority); ?>><?php echo esc_html($priority); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>
                        <span><?php echo esc_html__('Statut CRM', 'saoupack-b2b-collector'); ?></span>
                        <select name="crm_status">
                            <?php foreach (Saoupack_B2B_Collector_Sanitizer::crm_statuses() as $status) : ?>
                                <option value="<?php echo esc_attr($status); ?>" <?php selected($lead['crm_status'] ?? 'new', $status); ?>><?php echo esc_html($status); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
                <label>
                    <span><?php echo esc_html__('Réseaux sociaux', 'saoupack-b2b-collector'); ?></span>
                    <div class="b2b-crm__grid">
                        <input type="url" name="social_links[linkedin]" placeholder="LinkedIn" value="<?php echo esc_attr($social['linkedin'] ?? ''); ?>" />
                        <input type="url" name="social_links[facebook]" placeholder="Facebook" value="<?php echo esc_attr($social['facebook'] ?? ''); ?>" />
                        <input type="url" name="social_links[instagram]" placeholder="Instagram" value="<?php echo esc_attr($social['instagram'] ?? ''); ?>" />
                        <input type="url" name="social_links[x]" placeholder="X" value="<?php echo esc_attr($social['x'] ?? ''); ?>" />
                        <input type="url" name="social_links[youtube]" placeholder="YouTube" value="<?php echo esc_attr($social['youtube'] ?? ''); ?>" />
                        <input type="url" name="social_links[tiktok]" placeholder="TikTok" value="<?php echo esc_attr($social['tiktok'] ?? ''); ?>" />
                    </div>
                </label>
                <label>
                    <span><?php echo esc_html__('Notes', 'saoupack-b2b-collector'); ?></span>
                    <textarea name="notes" rows="4"><?php echo esc_textarea($lead['notes'] ?? ''); ?></textarea>
                </label>
                <div class="b2b-crm__section-actions">
                    <button class="b2b-crm__button" type="submit"><?php echo esc_html($is_edit ? __('Mettre à jour', 'saoupack-b2b-collector') : __('Créer', 'saoupack-b2b-collector')); ?></button>
                    <a class="b2b-crm__ghost" href="<?php echo esc_url(admin_url('admin.php?page=saoupack-b2b-collector-leads')); ?>"><?php echo esc_html__('Retour', 'saoupack-b2b-collector'); ?></a>
                </div>
            </form>
        </div>
        <?php
    }
}
