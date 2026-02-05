<?php

if (!defined('ABSPATH')) {
    exit;
}

class B2B_CRM_Lead_Detail_Page
{
    public static function render($lead_id)
    {
        if (!current_user_can(B2B_CRM_MAROC_LEADS_CAP)) {
            return;
        }

        self::handle_post($lead_id);

        $lead = B2B_CRM_Lead_Repository::get($lead_id);
        if (!$lead) {
            echo '<div class="notice notice-error"><p>' . esc_html__('Lead introuvable.', 'b2b-crm-maroc') . '</p></div>';
            return;
        }

        $interactions = B2B_CRM_Interaction_Repository::list($lead_id);
        $owners = get_users(array('capability' => B2B_CRM_MAROC_ACCESS_CAP));
        $module_settings = get_option('b2b_crm_module_settings', array());
        $email_templates = array();
        if (!empty($module_settings['emails_templates'])) {
            foreach (preg_split('/\r\n|\r|\n/', (string) $module_settings['emails_templates']) as $line) {
                $line = trim($line);
                if (!$line || strpos($line, '|') === false) {
                    continue;
                }
                list($title, $body) = array_map('trim', explode('|', $line, 2));
                if ($title && $body) {
                    $email_templates[$title] = $body;
                }
            }
        }
        $social = array();
        if (!empty($lead['social_json'])) {
            $decoded = json_decode($lead['social_json'], true);
            if (is_array($decoded)) {
                $social = $decoded;
            }
        }
        $suggestions = B2B_CRM_Lead_Repository::key_values(array('company_name', 'contact_name', 'email', 'phone', 'phone_mobile', 'website'));
        ?>
        <div class="wrap b2b-crm">
            <?php settings_errors('b2b-crm-maroc'); ?>
            <h1><?php echo esc_html($lead['company_name']); ?></h1>
            <a class="button" href="<?php echo esc_url(add_query_arg(array('page' => 'b2b-crm-maroc', 'tab' => 'base'), B2B_CRM_Lead_List_Page::base_url())); ?>"><?php echo esc_html__('Retour à la liste', 'b2b-crm-maroc'); ?></a>

            <div class="b2b-crm__grid">
                <div class="b2b-crm__card">
                    <h2><?php echo esc_html__('Informations', 'b2b-crm-maroc'); ?></h2>
                    <form method="post">
                        <?php wp_nonce_field('b2b_crm_save_lead', 'b2b_crm_nonce'); ?>
                        <input type="hidden" name="b2b_crm_action" value="save_lead" />
                        <table class="form-table">
                            <tr>
                                <th><?php echo esc_html__('Société', 'b2b-crm-maroc'); ?></th>
                                <td><input type="text" name="company_name" value="<?php echo esc_attr($lead['company_name']); ?>" class="regular-text" list="b2b-crm-company-suggestions" /></td>
                            </tr>
                            <tr>
                                <th><?php echo esc_html__('Secteur', 'b2b-crm-maroc'); ?></th>
                                <td><input type="text" name="sector" value="<?php echo esc_attr($lead['sector']); ?>" class="regular-text" /></td>
                            </tr>
                            <tr>
                                <th><?php echo esc_html__('Ville', 'b2b-crm-maroc'); ?></th>
                                <td><input type="text" name="city" value="<?php echo esc_attr($lead['city']); ?>" class="regular-text" /></td>
                            </tr>
                            <tr>
                                <th><?php echo esc_html__('Contact', 'b2b-crm-maroc'); ?></th>
                                <td><input type="text" name="contact_name" value="<?php echo esc_attr($lead['contact_name']); ?>" class="regular-text" list="b2b-crm-contact-suggestions" /></td>
                            </tr>
                            <tr>
                                <th><?php echo esc_html__('Fonction', 'b2b-crm-maroc'); ?></th>
                                <td><input type="text" name="contact_role" value="<?php echo esc_attr($lead['contact_role']); ?>" class="regular-text" /></td>
                            </tr>
                            <tr>
                                <th><?php echo esc_html__('Téléphone', 'b2b-crm-maroc'); ?></th>
                                <td><input type="text" name="phone" value="<?php echo esc_attr($lead['phone']); ?>" class="regular-text" list="b2b-crm-phone-suggestions" /></td>
                            </tr>
                            <tr>
                                <th><?php echo esc_html__('GSM', 'b2b-crm-maroc'); ?></th>
                                <td><input type="text" name="phone_mobile" value="<?php echo esc_attr($lead['phone_mobile']); ?>" class="regular-text" list="b2b-crm-mobile-suggestions" /></td>
                            </tr>
                            <tr>
                                <th><?php echo esc_html__('Email', 'b2b-crm-maroc'); ?></th>
                                <td><input type="email" name="email" value="<?php echo esc_attr($lead['email']); ?>" class="regular-text" list="b2b-crm-email-suggestions" /></td>
                            </tr>
                            <tr>
                                <th><?php echo esc_html__('Site Web', 'b2b-crm-maroc'); ?></th>
                                <td><input type="text" name="website" value="<?php echo esc_attr($lead['website']); ?>" class="regular-text" list="b2b-crm-website-suggestions" /></td>
                            </tr>
                            <tr>
                                <th><?php echo esc_html__('Réseaux Sociaux', 'b2b-crm-maroc'); ?></th>
                                <td>
                                    <p><input type="url" name="social_linkedin" value="<?php echo esc_attr($social['linkedin'] ?? ''); ?>" class="regular-text" placeholder="<?php echo esc_attr__('Lien LinkedIn', 'b2b-crm-maroc'); ?>" /></p>
                                    <p><input type="url" name="social_facebook" value="<?php echo esc_attr($social['facebook'] ?? ''); ?>" class="regular-text" placeholder="<?php echo esc_attr__('Lien Facebook', 'b2b-crm-maroc'); ?>" /></p>
                                    <p><input type="url" name="social_instagram" value="<?php echo esc_attr($social['instagram'] ?? ''); ?>" class="regular-text" placeholder="<?php echo esc_attr__('Lien Instagram', 'b2b-crm-maroc'); ?>" /></p>
                                </td>
                            </tr>
                            <tr>
                                <th><?php echo esc_html__('Étape', 'b2b-crm-maroc'); ?></th>
                                <td>
                                    <select name="stage">
                                        <option value=""><?php echo esc_html__('Sélectionner', 'b2b-crm-maroc'); ?></option>
                                        <?php foreach (B2B_CRM_Lead_Repository::stages() as $stage) : ?>
                                            <option value="<?php echo esc_attr($stage); ?>" <?php selected($lead['stage'], $stage); ?>><?php echo esc_html($stage); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><?php echo esc_html__('Responsable', 'b2b-crm-maroc'); ?></th>
                                <td>
                                    <select name="owner_user_id">
                                        <option value=""><?php echo esc_html__('Sélectionner', 'b2b-crm-maroc'); ?></option>
                                        <?php foreach ($owners as $owner) : ?>
                                            <option value="<?php echo esc_attr($owner->ID); ?>" <?php selected((int) $lead['owner_user_id'], (int) $owner->ID); ?>>
                                                <?php echo esc_html($owner->display_name); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><?php echo esc_html__('Statut', 'b2b-crm-maroc'); ?></th>
                                <td>
                                    <select name="status">
                                        <?php foreach (self::statuses() as $key => $label) : ?>
                                            <option value="<?php echo esc_attr($key); ?>" <?php selected($lead['status'], $key); ?>><?php echo esc_html($label); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><?php echo esc_html__('Intérêt', 'b2b-crm-maroc'); ?></th>
                                <td>
                                    <select name="interest_level">
                                        <?php foreach (self::interests() as $key => $label) : ?>
                                            <option value="<?php echo esc_attr($key); ?>" <?php selected($lead['interest_level'], $key); ?>><?php echo esc_html($label); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><?php echo esc_html__('Tags', 'b2b-crm-maroc'); ?></th>
                                <td><input type="text" name="tags" value="<?php echo esc_attr($lead['tags']); ?>" class="regular-text" placeholder="<?php echo esc_attr__('ex: priorité, retail', 'b2b-crm-maroc'); ?>" /></td>
                            </tr>
                            <tr>
                                <th><?php echo esc_html__('Dernier contact', 'b2b-crm-maroc'); ?></th>
                                <td><input type="datetime-local" name="last_contact" value="<?php echo esc_attr(self::format_datetime_local($lead['last_contact'])); ?>" /></td>
                            </tr>
                            <tr>
                                <th><?php echo esc_html__('Prochaine action', 'b2b-crm-maroc'); ?></th>
                                <td><input type="text" name="next_action" value="<?php echo esc_attr($lead['next_action']); ?>" class="regular-text" /></td>
                            </tr>
                            <tr>
                                <th><?php echo esc_html__('Date de relance', 'b2b-crm-maroc'); ?></th>
                                <td><input type="date" name="follow_up_date" value="<?php echo esc_attr($lead['follow_up_date']); ?>" /></td>
                            </tr>
                            <tr>
                                <th><?php echo esc_html__('Commentaires', 'b2b-crm-maroc'); ?></th>
                                <td><textarea name="notes" class="large-text" rows="4"><?php echo esc_textarea($lead['notes']); ?></textarea></td>
                            </tr>
                            <tr>
                                <th><?php echo esc_html__('Source', 'b2b-crm-maroc'); ?></th>
                                <td><input type="text" name="source" value="<?php echo esc_attr($lead['source']); ?>" class="regular-text" /></td>
                            </tr>
                            <tr>
                                <th><?php echo esc_html__('Méthode de collecte', 'b2b-crm-maroc'); ?></th>
                                <td><input type="text" name="collected_method" value="<?php echo esc_attr($lead['collected_method']); ?>" class="regular-text" /></td>
                            </tr>
                        </table>
                        <datalist id="b2b-crm-company-suggestions">
                            <?php foreach ($suggestions['company_name'] ?? array() as $value) : ?>
                                <option value="<?php echo esc_attr($value); ?>"></option>
                            <?php endforeach; ?>
                        </datalist>
                        <datalist id="b2b-crm-contact-suggestions">
                            <?php foreach ($suggestions['contact_name'] ?? array() as $value) : ?>
                                <option value="<?php echo esc_attr($value); ?>"></option>
                            <?php endforeach; ?>
                        </datalist>
                        <datalist id="b2b-crm-email-suggestions">
                            <?php foreach ($suggestions['email'] ?? array() as $value) : ?>
                                <option value="<?php echo esc_attr($value); ?>"></option>
                            <?php endforeach; ?>
                        </datalist>
                        <datalist id="b2b-crm-phone-suggestions">
                            <?php foreach ($suggestions['phone'] ?? array() as $value) : ?>
                                <option value="<?php echo esc_attr($value); ?>"></option>
                            <?php endforeach; ?>
                        </datalist>
                        <datalist id="b2b-crm-mobile-suggestions">
                            <?php foreach ($suggestions['phone_mobile'] ?? array() as $value) : ?>
                                <option value="<?php echo esc_attr($value); ?>"></option>
                            <?php endforeach; ?>
                        </datalist>
                        <datalist id="b2b-crm-website-suggestions">
                            <?php foreach ($suggestions['website'] ?? array() as $value) : ?>
                                <option value="<?php echo esc_attr($value); ?>"></option>
                            <?php endforeach; ?>
                        </datalist>
                        <p>
                            <button class="button button-primary"><?php echo esc_html__('Enregistrer', 'b2b-crm-maroc'); ?></button>
                        </p>
                    </form>
                </div>

                <div class="b2b-crm__card b2b-crm__card--email">
                    <div class="b2b-crm__email-header">
                        <h2><?php echo esc_html__('Nouveau message', 'b2b-crm-maroc'); ?></h2>
                    </div>
                    <form method="post">
                        <?php wp_nonce_field('b2b_crm_send_email', 'b2b_crm_email_nonce'); ?>
                        <input type="hidden" name="b2b_crm_action" value="send_email" />
                        <div class="b2b-crm__email-fields">
                            <?php if (!empty($email_templates)) : ?>
                                <label class="b2b-crm__email-row">
                                    <span><?php echo esc_html__('Template', 'b2b-crm-maroc'); ?></span>
                                    <select name="email_template">
                                        <option value=""><?php echo esc_html__('Libre', 'b2b-crm-maroc'); ?></option>
                                        <?php foreach ($email_templates as $title => $body) : ?>
                                            <option value="<?php echo esc_attr($title); ?>"><?php echo esc_html($title); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                            <?php endif; ?>
                            <label class="b2b-crm__email-row">
                                <span><?php echo esc_html__('Destinataires', 'b2b-crm-maroc'); ?></span>
                                <input type="text" readonly value="<?php echo esc_attr($lead['email']); ?>" />
                            </label>
                            <label class="b2b-crm__email-row">
                                <span><?php echo esc_html__('Objet', 'b2b-crm-maroc'); ?></span>
                                <input type="text" name="email_subject" required />
                            </label>
                        </div>
                        <div class="b2b-crm__email-editor">
                            <?php
                            wp_editor(
                                '',
                                'b2b_crm_email_message',
                                array(
                                    'textarea_name' => 'email_message',
                                    'media_buttons' => false,
                                    'teeny' => true,
                                )
                            );
                            ?>
                        </div>
                        <div class="b2b-crm__email-footer">
                            <button class="button button-primary"><?php echo esc_html__('Envoyer', 'b2b-crm-maroc'); ?></button>
                            <div class="b2b-crm__email-tools">
                                <span class="dashicons dashicons-paperclip"></span>
                                <span class="dashicons dashicons-format-image"></span>
                                <span class="dashicons dashicons-smiley"></span>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="b2b-crm__card b2b-crm__card--interactions">
                    <h2><?php echo esc_html__('Historique des interactions', 'b2b-crm-maroc'); ?></h2>
                    <form method="post" class="b2b-crm__interaction-form">
                        <?php wp_nonce_field('b2b_crm_add_interaction', 'b2b_crm_interaction_nonce'); ?>
                        <input type="hidden" name="b2b_crm_action" value="add_interaction" />
                        <input type="text" name="interaction_type" placeholder="<?php echo esc_attr__('Type (appel, email, meeting)', 'b2b-crm-maroc'); ?>" required />
                        <textarea name="interaction_content" rows="3" placeholder="<?php echo esc_attr__('Détails', 'b2b-crm-maroc'); ?>" required></textarea>
                        <button class="button"><?php echo esc_html__('Ajouter', 'b2b-crm-maroc'); ?></button>
                    </form>
                    <?php if (empty($interactions)) : ?>
                        <p><?php echo esc_html__('Aucune interaction enregistrée.', 'b2b-crm-maroc'); ?></p>
                    <?php else : ?>
                        <ul class="b2b-crm__timeline">
                            <?php foreach ($interactions as $interaction) : ?>
                                <li>
                                    <strong><?php echo esc_html($interaction['interaction_type']); ?></strong>
                                    <span><?php echo esc_html(mysql2date('d/m/Y H:i', $interaction['created_at'])); ?></span>
                                    <p><?php echo esc_html($interaction['content']); ?></p>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }

    private static function handle_post($lead_id)
    {
        if (empty($_POST['b2b_crm_action'])) {
            return;
        }

        $action = sanitize_key($_POST['b2b_crm_action']);

        if ($action === 'save_lead' && isset($_POST['b2b_crm_nonce']) && wp_verify_nonce($_POST['b2b_crm_nonce'], 'b2b_crm_save_lead')) {
            $social = array(
                'linkedin' => isset($_POST['social_linkedin']) ? esc_url_raw(wp_unslash($_POST['social_linkedin'])) : '',
                'facebook' => isset($_POST['social_facebook']) ? esc_url_raw(wp_unslash($_POST['social_facebook'])) : '',
                'instagram' => isset($_POST['social_instagram']) ? esc_url_raw(wp_unslash($_POST['social_instagram'])) : '',
            );
            $social = array_filter($social);
            $_POST['social_json'] = empty($social) ? '' : wp_json_encode($social);
            $data = B2B_CRM_Sanitizer::lead_fields(wp_unslash($_POST));
            B2B_CRM_Lead_Repository::update($lead_id, $data);
            B2B_CRM_Interaction_Repository::add($lead_id, 'audit', __('Lead mis à jour.', 'b2b-crm-maroc'), get_current_user_id());
            add_settings_error('b2b-crm-maroc', 'lead_saved', __('Lead mis à jour.', 'b2b-crm-maroc'), 'updated');
        }

        if ($action === 'send_email' && isset($_POST['b2b_crm_email_nonce']) && wp_verify_nonce($_POST['b2b_crm_email_nonce'], 'b2b_crm_send_email')) {
            if (!current_user_can(B2B_CRM_MAROC_EMAIL_CAP)) {
                add_settings_error('b2b-crm-maroc', 'email_denied', __('Accès refusé.', 'b2b-crm-maroc'), 'error');
                return;
            }
            $lead = B2B_CRM_Lead_Repository::get($lead_id);
            $subject = sanitize_text_field(wp_unslash($_POST['email_subject']));
            $message = wp_kses_post(wp_unslash($_POST['email_message']));
            $template_name = isset($_POST['email_template']) ? sanitize_text_field(wp_unslash($_POST['email_template'])) : '';
            $module_settings = get_option('b2b_crm_module_settings', array());
            if ($template_name && !empty($module_settings['emails_templates'])) {
                foreach (preg_split('/\r\n|\r|\n/', (string) $module_settings['emails_templates']) as $line) {
                    $line = trim($line);
                    if (!$line || strpos($line, '|') === false) {
                        continue;
                    }
                    list($title, $body) = array_map('trim', explode('|', $line, 2));
                    if ($title === $template_name) {
                        $message = $body . "\n\n" . $message;
                        break;
                    }
                }
            }
            $settings = get_option('b2b_crm_module_settings', array());
            $signature = !empty($settings['emails_signature']) ? "\n\n" . wp_kses_post($settings['emails_signature']) : '';
            $result = B2B_CRM_Email_Service::send($lead, $subject, $message . $signature);

            $logs = get_option('b2b_crm_email_logs', array());
            if (!is_array($logs)) {
                $logs = array();
            }

            if (is_wp_error($result)) {
                $logs[] = array(
                    'date' => current_time('mysql'),
                    'user_id' => get_current_user_id(),
                    'subject' => $subject,
                    'status' => 'failed',
                );
                add_settings_error('b2b-crm-maroc', 'email_failed', $result->get_error_message(), 'error');
            } else {
                B2B_CRM_Interaction_Repository::add($lead_id, 'email', $subject, get_current_user_id());
                $logs[] = array(
                    'date' => current_time('mysql'),
                    'user_id' => get_current_user_id(),
                    'subject' => $subject,
                    'status' => 'sent',
                );
                add_settings_error('b2b-crm-maroc', 'email_sent', __('Email envoyé.', 'b2b-crm-maroc'), 'updated');
            }

            update_option('b2b_crm_email_logs', array_slice($logs, -100));
        }

        if ($action === 'add_interaction' && isset($_POST['b2b_crm_interaction_nonce']) && wp_verify_nonce($_POST['b2b_crm_interaction_nonce'], 'b2b_crm_add_interaction')) {
            $type = sanitize_text_field(wp_unslash($_POST['interaction_type']));
            $content = sanitize_textarea_field(wp_unslash($_POST['interaction_content']));
            B2B_CRM_Interaction_Repository::add($lead_id, $type, $content, get_current_user_id());
            add_settings_error('b2b-crm-maroc', 'interaction_added', __('Interaction ajoutée.', 'b2b-crm-maroc'), 'updated');
        }

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

    private static function format_datetime_local($value)
    {
        if (empty($value) || $value === '0000-00-00 00:00:00') {
            return '';
        }

        return gmdate('Y-m-d\TH:i', strtotime($value));
    }
}
