<?php

if (!defined('ABSPATH')) {
    exit;
}

class B2B_CRM_Views
{
    public static function render_dashboard($stats, array $recent)
    {
        ?>
        <div class="b2b-crm__section" id="b2b-crm-collect">
            <h2><?php echo esc_html__('Statistiques & Performance', 'b2b-crm-maroc'); ?></h2>
            <div class="b2b-crm__stats">
                <?php foreach ($stats as $stat) : ?>
                    <div class="b2b-crm__stat-card">
                        <div class="b2b-crm__stat-icon"><span><?php echo esc_html($stat['icon']); ?></span></div>
                        <div class="b2b-crm__stat-value"><?php echo esc_html($stat['value']); ?></div>
                        <div class="b2b-crm__stat-label"><?php echo esc_html($stat['label']); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="b2b-crm__section" id="b2b-crm-sources">
            <div class="b2b-crm__card b2b-crm__card--large">
                <h3><?php echo esc_html__('Activité Récente', 'b2b-crm-maroc'); ?></h3>
                <div class="b2b-crm__recent-grid">
                    <?php foreach ($recent as $lead) : ?>
                        <div class="b2b-crm__recent-item">
                            <div class="b2b-crm__avatar"><?php echo esc_html($lead['initial']); ?></div>
                            <div>
                                <div class="b2b-crm__recent-name"><?php echo esc_html($lead['company_name']); ?></div>
                                <div class="b2b-crm__recent-meta"><?php echo esc_html($lead['meta']); ?></div>
                            </div>
                            <span class="b2b-crm__pill"><?php echo esc_html($lead['status']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php
    }

    public static function render_collect($redirect_to = '')
    {
        $config = get_option('b2b_crm_collect_config', array());
        $selected_city = isset($config['city']) ? $config['city'] : '';
        $selected_sector = isset($config['sector']) ? $config['sector'] : '';
        $selected_precision = isset($config['precision']) ? $config['precision'] : 'standard';
        $selected_sources = isset($config['sources']) && is_array($config['sources']) ? $config['sources'] : array();
        $cities = self::morocco_cities();
        $sectors = array(
            'Expert Comptable',
            'Agence Immobilière',
            'Garage Automobile',
            'Clinique Privée',
            'Architecte',
            'Gardiennage & Sécurité',
            'Hôtel & Tourisme',
            'Restaurant',
            'Notaire',
            'Agence Digitale',
        );
        $sources = array(
            'google_maps' => 'Google Maps & GMB',
            'directories' => 'Annuaires Marocains',
            'social' => 'Réseaux Sociaux Pro',
            'domains' => 'Scan Domaines (.ma, .com...)',
            'institutions' => 'Portails Institutionnels',
            'excel' => 'Fichier Excel / CSV',
        );
        ?>
        <div class="b2b-crm__section">
            <h2><?php echo esc_html__('Moteur de Recherche Intelligent', 'b2b-crm-maroc'); ?></h2>
            <?php settings_errors('b2b-crm-maroc'); ?>
            <form class="b2b-crm__collect" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('b2b_crm_run_collect'); ?>
                <input type="hidden" name="action" value="b2b_crm_run_collect" />
                <?php if ($redirect_to) : ?>
                    <input type="hidden" name="redirect_to" value="<?php echo esc_url($redirect_to); ?>" />
                <?php endif; ?>
                <div class="b2b-crm__collect-card">
                    <div class="b2b-crm__collect-main">
                        <h3><?php echo esc_html__('Configuration Live', 'b2b-crm-maroc'); ?></h3>
                        <div class="b2b-crm__collect-grid">
                            <div>
                                <label><?php echo esc_html__('Ville', 'b2b-crm-maroc'); ?></label>
                                <select class="b2b-crm__input" name="city">
                                    <option value=""><?php echo esc_html__('Choisir une ville', 'b2b-crm-maroc'); ?></option>
                                    <?php foreach ($cities as $city) : ?>
                                        <option value="<?php echo esc_attr($city); ?>" <?php selected($selected_city, $city); ?>><?php echo esc_html($city); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label><?php echo esc_html__('Secteur', 'b2b-crm-maroc'); ?></label>
                                <input type="text" class="b2b-crm__input" name="sector" id="b2b-crm-sector" value="<?php echo esc_attr($selected_sector); ?>" placeholder="<?php echo esc_attr__('Ex: Architecte', 'b2b-crm-maroc'); ?>" />
                            </div>
                        </div>
                        <div class="b2b-crm__chips">
                            <?php foreach ($sectors as $chip) : ?>
                                <button type="button" class="b2b-crm__chip <?php echo $selected_sector === $chip ? 'is-active' : ''; ?>" data-sector="<?php echo esc_attr($chip); ?>">
                                    <?php echo esc_html($chip); ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        <div class="b2b-crm__collect-sources">
                            <?php foreach ($sources as $key => $label) : ?>
                                <label class="b2b-crm__source <?php echo in_array($key, $selected_sources, true) ? 'is-active' : ''; ?>">
                                    <input type="checkbox" name="sources[]" value="<?php echo esc_attr($key); ?>" <?php checked(in_array($key, $selected_sources, true)); ?> />
                                    <span><?php echo esc_html($label); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="b2b-crm__collect-side">
                        <div class="b2b-crm__precision">
                            <div class="b2b-crm__precision-header"><?php echo esc_html__('Précision', 'b2b-crm-maroc'); ?></div>
                            <div class="b2b-crm__precision-switch">
                                <button type="button" class="<?php echo $selected_precision === 'standard' ? 'is-active' : ''; ?>" data-precision="standard">Standard</button>
                                <button type="button" class="<?php echo $selected_precision === 'deep' ? 'is-active' : ''; ?>" data-precision="deep">Deep</button>
                            </div>
                            <input type="hidden" name="precision" id="b2b-crm-precision" value="<?php echo esc_attr($selected_precision); ?>" />
                            <button class="b2b-crm__cta" type="submit"><?php echo esc_html__('Lancer', 'b2b-crm-maroc'); ?></button>
                        </div>
                        <div class="b2b-crm__collect-preview"></div>
                    </div>
                </div>
            </form>
        </div>
        <?php
    }

    public static function render_settings($forced_tab = '')
    {
        $settings = wp_parse_args(get_option('b2b_crm_settings', array()), array(
            'workspace_name' => 'CRM Saoupack',
            'default_currency' => 'MAD',
            'timezone' => 'Africa/Casablanca',
            'owner_email' => '',
            'portal_slug' => 'crm',
            'allowed_domains' => array(),
        ));
        $modules = wp_parse_args(get_option('b2b_crm_modules_config', array()), array(
            'accounts' => true,
            'contacts' => true,
            'base' => true,
            'emails' => true,
            'tasks' => true,
            'collect' => true,
            'sources' => true,
            'pipeline' => true,
        ));
        $module_settings = wp_parse_args(get_option('b2b_crm_module_settings', array()), array(
            'opportunities_stages' => '',
            'emails_signature' => '',
            'emails_templates' => "Relance 1|Bonjour {{company_name}}, je me permets de vous relancer.
Relance 2|Bonjour {{company_name}}, avez-vous pu consulter notre proposition ?
Prise de contact|Bonjour {{company_name}}, je souhaite vous présenter nos services.",
            'tasks_sla' => '24h',
        ));
        $key_values = wp_parse_args(get_option('b2b_crm_key_values', array()), array(
            'status' => array('Nouveau', 'Qualifié', 'Contacté', 'Inactif'),
            'priority' => array('Faible', 'Moyen', 'Fort'),
            'source' => array('Google Maps', 'Annuaires', 'Import CSV'),
            'tags' => array(),
        ));

        $settings_tab = $forced_tab ? sanitize_key($forced_tab) : (isset($_GET['settings_tab']) ? sanitize_key($_GET['settings_tab']) : 'general');
        $tabs = array(
            'general' => __('Général', 'b2b-crm-maroc'),
            'company' => __('Entreprise', 'b2b-crm-maroc'),
            'modules' => __('Modules', 'b2b-crm-maroc'),
            'references' => __('Référentiels', 'b2b-crm-maroc'),
            'email' => __('Email', 'b2b-crm-maroc'),
            'access' => __('Rôles & Accès', 'b2b-crm-maroc'),
            'onboarding' => __('Onboarding', 'b2b-crm-maroc'),
        );
        if (!isset($tabs[$settings_tab])) {
            $settings_tab = 'general';
        }

        $base_url = add_query_arg(array('page' => 'b2b-crm-maroc', 'tab' => 'settings'), B2B_CRM_Lead_List_Page::base_url());
        $portal_url = class_exists('B2B_CRM_Shortcode') ? B2B_CRM_Shortcode::portal_url() : home_url('/crm/');
        $email_logs = get_option('b2b_crm_email_logs', array());
        $email_logs = is_array($email_logs) ? array_slice(array_reverse($email_logs), 0, 10) : array();

        $sources_config = get_option('b2b_crm_sources_config', array());
        $google_endpoint = isset($sources_config['google_maps']['endpoint']) ? esc_url_raw($sources_config['google_maps']['endpoint']) : '';
        $google_key = isset($sources_config['google_maps']['api_key']) ? trim((string) $sources_config['google_maps']['api_key']) : '';
        $smtp_detected = defined('WPMS_ON') || defined('POST_SMTP_VERSION') || class_exists('WPMailSMTP\Core');
        $source_endpoint_reachable = false;
        if ($google_endpoint) {
            $response = wp_remote_head($google_endpoint, array('timeout' => 2));
            if (!is_wp_error($response)) {
                $code = (int) wp_remote_retrieve_response_code($response);
                $source_endpoint_reachable = $code >= 200 && $code < 500;
            }
        }

        $onboarding = array(
            'db' => class_exists('B2B_CRM_Lead_Table') && class_exists('B2B_CRM_Interaction_Table'),
            'caps' => current_user_can(B2B_CRM_MAROC_SETTINGS_CAP) && current_user_can(B2B_CRM_MAROC_ACCESS_CAP),
            'email' => !empty($settings['owner_email']) && $smtp_detected,
            'sources' => !empty($settings['allowed_domains']) && !empty($google_key) && $source_endpoint_reachable,
        );
        ?>
        <div class="b2b-crm__section">
            <h2><?php echo esc_html__('Paramétrage CRM Saoupack', 'b2b-crm-maroc'); ?></h2>
            <div class="b2b-crm__toolbar">
                <nav class="b2b-crm__filters">
                    <?php foreach ($tabs as $key => $label) : ?>
                        <a class="b2b-crm__ghost <?php echo $settings_tab === $key ? 'is-active' : ''; ?>" href="<?php echo esc_url(add_query_arg('settings_tab', $key, $base_url)); ?>"><?php echo esc_html($label); ?></a>
                    <?php endforeach; ?>
                </nav>
                <a class="b2b-crm__export" href="<?php echo esc_url($portal_url); ?>"><?php echo esc_html__('Ouvrir le portail CRM', 'b2b-crm-maroc'); ?></a>
            </div>

            <form class="b2b-crm__settings-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('b2b_crm_save_settings'); ?>
                <input type="hidden" name="action" value="b2b_crm_save_settings" />

                <div class="b2b-crm__settings-layout b2b-crm__settings-layout--stacked">
                    <?php if ($settings_tab === 'general') : ?>
                        <section class="b2b-crm__settings-section">
                            <h3><?php echo esc_html__('Général', 'b2b-crm-maroc'); ?></h3>
                            <div class="b2b-crm__settings-fields">
                                <label class="b2b-crm__label"><?php echo esc_html__('Nom du workspace', 'b2b-crm-maroc'); ?><input class="b2b-crm__input" type="text" name="workspace_name" value="<?php echo esc_attr($settings['workspace_name']); ?>" /></label>
                                <label class="b2b-crm__label"><?php echo esc_html__('Devise', 'b2b-crm-maroc'); ?><input class="b2b-crm__input" type="text" name="default_currency" value="<?php echo esc_attr($settings['default_currency']); ?>" /></label>
                                <label class="b2b-crm__label"><?php echo esc_html__('Fuseau horaire', 'b2b-crm-maroc'); ?><input class="b2b-crm__input" type="text" name="timezone" value="<?php echo esc_attr($settings['timezone']); ?>" /></label>
                                <label class="b2b-crm__label"><?php echo esc_html__('Slug portail CRM', 'b2b-crm-maroc'); ?><input class="b2b-crm__input" type="text" name="portal_slug" value="<?php echo esc_attr($settings['portal_slug']); ?>" /></label>
                                <label class="b2b-crm__label"><?php echo esc_html__('Domaines autorisés', 'b2b-crm-maroc'); ?><textarea class="b2b-crm__input b2b-crm__input--area" name="allowed_domains" rows="3"><?php echo esc_textarea(implode("\n", $settings['allowed_domains'])); ?></textarea></label>
                            </div>
                        </section>
                    <?php elseif ($settings_tab === 'company') : ?>
                        <?php B2B_CRM_Lead_List_Page::render_accounts(); ?>
                    <?php elseif ($settings_tab === 'modules') : ?>
                        <section class="b2b-crm__settings-section">
                            <h3><?php echo esc_html__('Modules actifs', 'b2b-crm-maroc'); ?></h3>
                            <div class="b2b-crm__modules-grid">
                                <?php foreach ($modules as $key => $enabled) : ?>
                                    <label class="b2b-crm__module-toggle">
                                        <input type="checkbox" name="modules[]" value="<?php echo esc_attr($key); ?>" <?php checked($enabled); ?> />
                                        <span><?php echo esc_html(self::module_label($key)); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <p class="b2b-crm__muted"><?php echo esc_html__('Un module décoché disparaît du menu et devient inaccessible.', 'b2b-crm-maroc'); ?></p>
                        </section>
                    <?php elseif ($settings_tab === 'references') : ?>
                        <section class="b2b-crm__settings-section">
                            <h3><?php echo esc_html__('Référentiels', 'b2b-crm-maroc'); ?></h3>
                            <div class="b2b-crm__settings-fields">
                                <label class="b2b-crm__label"><?php echo esc_html__('Statuts CRM', 'b2b-crm-maroc'); ?><textarea class="b2b-crm__input b2b-crm__input--area" name="key_values[status]" rows="4"><?php echo esc_textarea(implode("\n", $key_values['status'])); ?></textarea></label>
                                <label class="b2b-crm__label"><?php echo esc_html__('Priorités', 'b2b-crm-maroc'); ?><textarea class="b2b-crm__input b2b-crm__input--area" name="key_values[priority]" rows="4"><?php echo esc_textarea(implode("\n", $key_values['priority'])); ?></textarea></label>
                                <label class="b2b-crm__label"><?php echo esc_html__('Sources', 'b2b-crm-maroc'); ?><textarea class="b2b-crm__input b2b-crm__input--area" name="key_values[source]" rows="4"><?php echo esc_textarea(implode("\n", $key_values['source'])); ?></textarea></label>
                                <label class="b2b-crm__label"><?php echo esc_html__('Tags', 'b2b-crm-maroc'); ?><textarea class="b2b-crm__input b2b-crm__input--area" name="key_values[tags]" rows="4"><?php echo esc_textarea(implode("\n", $key_values['tags'])); ?></textarea></label>
                            </div>
                            <p class="b2b-crm__muted"><?php echo esc_html__('Ces valeurs alimentent les suggestions et les contrôles serveur.', 'b2b-crm-maroc'); ?></p>
                        </section>
                    <?php elseif ($settings_tab === 'email') : ?>
                        <section class="b2b-crm__settings-section">
                            <h3><?php echo esc_html__('Configuration Email', 'b2b-crm-maroc'); ?></h3>
                            <div class="b2b-crm__settings-fields">
                                <label class="b2b-crm__label"><?php echo esc_html__('Email propriétaire', 'b2b-crm-maroc'); ?><input class="b2b-crm__input" type="email" name="owner_email" value="<?php echo esc_attr($settings['owner_email']); ?>" /></label>
                                <label class="b2b-crm__label"><?php echo esc_html__('Signature', 'b2b-crm-maroc'); ?><textarea class="b2b-crm__input b2b-crm__input--area" name="module_settings[emails_signature]" rows="4"><?php echo esc_textarea($module_settings['emails_signature']); ?></textarea></label>
                                <label class="b2b-crm__label"><?php echo esc_html__('Templates (Titre|Message, 1 ligne)', 'b2b-crm-maroc'); ?><textarea class="b2b-crm__input b2b-crm__input--area" name="module_settings[emails_templates]" rows="6"><?php echo esc_textarea($module_settings['emails_templates']); ?></textarea></label>
                            </div>
                            <h4><?php echo esc_html__('Logs récents', 'b2b-crm-maroc'); ?></h4>
                            <ul class="b2b-crm__timeline">
                                <?php if (empty($email_logs)) : ?>
                                    <li><span><?php echo esc_html__('Aucun log.', 'b2b-crm-maroc'); ?></span></li>
                                <?php else : ?>
                                    <?php foreach ($email_logs as $log) : ?>
                                        <li><strong><?php echo esc_html($log['status'] ?? ''); ?></strong> <span><?php echo esc_html(($log['date'] ?? '') . ' · ' . ($log['subject'] ?? '')); ?></span></li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                <input type="hidden" name="action" value="b2b_crm_test_email" />
                                <?php wp_nonce_field('b2b_crm_test_email'); ?>
                                <button class="b2b-crm__ghost" type="submit"><?php echo esc_html__('Tester l’envoi email', 'b2b-crm-maroc'); ?></button>
                            </form>
                        </section>
                    <?php elseif ($settings_tab === 'access') : ?>
                        <section class="b2b-crm__settings-section">
                            <h3><?php echo esc_html__('Rôles & Accès', 'b2b-crm-maroc'); ?></h3>
                            <p class="b2b-crm__muted"><?php echo esc_html__('Rôle CRM Admin: accès complet. Rôle CRM Agent: gestion leads + emails.', 'b2b-crm-maroc'); ?></p>
                            <ul class="b2b-crm__timeline">
                                <li><strong>✅</strong><span>b2b_crm_access</span></li>
                                <li><strong>✅</strong><span>b2b_crm_manage_leads</span></li>
                                <li><strong>✅</strong><span>b2b_crm_manage_settings</span></li>
                                <li><strong>✅</strong><span>b2b_crm_manage_sources</span></li>
                                <li><strong>✅</strong><span>b2b_crm_send_email</span></li>
                            </ul>
                        </section>
                    <?php else : ?>
                        <section class="b2b-crm__settings-section">
                            <h3><?php echo esc_html__('Onboarding (diagnostic)', 'b2b-crm-maroc'); ?></h3>
                            <ul class="b2b-crm__timeline">
                                <li><strong><?php echo $onboarding['db'] ? '✅' : '❌'; ?></strong><span><?php echo esc_html__('Tables DB CRM', 'b2b-crm-maroc'); ?></span> <a class="b2b-crm__ghost" href="<?php echo esc_url(add_query_arg(array('settings_tab' => 'general'), $base_url)); ?>"><?php echo esc_html__('Vérifier', 'b2b-crm-maroc'); ?></a></li>
                                <li><strong><?php echo $onboarding['caps'] ? '✅' : '❌'; ?></strong><span><?php echo esc_html__('Rôles & capabilities', 'b2b-crm-maroc'); ?></span> <a class="b2b-crm__ghost" href="<?php echo esc_url(add_query_arg(array('settings_tab' => 'access'), $base_url)); ?>"><?php echo esc_html__('Contrôler', 'b2b-crm-maroc'); ?></a></li>
                                <li><strong><?php echo $onboarding['email'] ? '✅' : '❌'; ?></strong><span><?php echo esc_html__('Email provider', 'b2b-crm-maroc'); ?></span> <a class="b2b-crm__ghost" href="<?php echo esc_url(add_query_arg(array('settings_tab' => 'email'), $base_url)); ?>"><?php echo esc_html__('Configurer SMTP/Test', 'b2b-crm-maroc'); ?></a></li>
                                <li><strong><?php echo $onboarding['sources'] ? '✅' : '❌'; ?></strong><span><?php echo esc_html__('Sources autorisées', 'b2b-crm-maroc'); ?></span> <a class="b2b-crm__ghost" href="<?php echo esc_url(add_query_arg(array('page' => 'b2b-crm-maroc', 'tab' => 'sources'), B2B_CRM_Lead_List_Page::base_url())); ?>"><?php echo esc_html__('Définir domaines/clé Google', 'b2b-crm-maroc'); ?></a></li>
                            </ul>
                        </section>
                    <?php endif; ?>
                </div>

                <div class="b2b-crm__settings-actions">
                    <button class="b2b-crm__cta" type="submit"><?php echo esc_html__('Enregistrer', 'b2b-crm-maroc'); ?></button>
                </div>
            </form>
        </div>
        <?php
    }

    public static function render_sources($redirect_to = '')
    {
        $saved = get_option('b2b_crm_sources_config', array());
        $defaults = array(
            'google_maps' => array(
                'enabled' => true,
                'api_key' => 'VOTRE_CLE_API_GOOGLE',
                'endpoint' => 'https://maps.googleapis.com/maps/api/place/textsearch/json',
                'options' => 'restaurant, architecte, clinique, notaire',
                'ai_model' => 'gpt-4o-mini',
                'notes' => 'Filtrer par catégories GMB et ville.',
            ),
            'directories' => array(
                'enabled' => false,
                'endpoint' => 'https://exemple-annuaire.ma/export.json',
                'options' => 'crawl=2, delay=3s',
                'notes' => 'Format JSON avec name, email, phone, website.',
                'ai_model' => 'gpt-4o-mini',
                'api_key' => '',
            ),
            'social' => array(
                'enabled' => false,
                'api_key' => 'TOKEN_API_RESEAUX',
                'endpoint' => 'https://api.exemple-social.com/leads.json',
                'ai_model' => 'gpt-4o-mini',
                'notes' => 'URLs ciblées LinkedIn/Facebook/Instagram.',
                'options' => '',
            ),
            'domains' => array(
                'enabled' => false,
                'api_key' => 'CLE_WHOIS',
                'options' => '.ma, .com, .net',
                'notes' => 'Exclure domaines parking et spam.',
                'endpoint' => 'https://api.exemple-whois.com/leads.json',
                'ai_model' => 'gpt-4o-mini',
            ),
            'institutions' => array(
                'enabled' => false,
                'endpoint' => 'https://api.portail-gouv.ma/entreprises',
                'api_key' => '',
                'notes' => 'Format JSON avec name, email, phone, city.',
                'ai_model' => 'gpt-4o-mini',
                'options' => '',
            ),
            'excel' => array(
                'enabled' => false,
                'endpoint' => '',
                'options' => 'delimiter=;',
                'notes' => 'Importer un CSV exporté depuis Excel (colonnes name,email,phone,city,sector,website).',
                'ai_model' => 'gpt-4o-mini',
                'api_key' => '',
                'file_url' => '',
            ),
        );
        $sources = array(
            'google_maps' => array(
                'title' => __('Google Maps & GMB', 'b2b-crm-maroc'),
                'description' => __('API Google Places, catégories GMB, rayon de recherche et enrichissement IA.', 'b2b-crm-maroc'),
                'fields' => array(
                    'api_key' => __('Clé API Google', 'b2b-crm-maroc'),
                    'endpoint' => __('Endpoint Places API', 'b2b-crm-maroc'),
                    'options' => __('Catégories/Types (ex: restaurant, architecte)', 'b2b-crm-maroc'),
                ),
            ),
            'directories' => array(
                'title' => __('Annuaires Marocains', 'b2b-crm-maroc'),
                'description' => __('Ciblage par URLs d’annuaires, profondeur de crawl et extraction IA.', 'b2b-crm-maroc'),
                'fields' => array(
                    'endpoint' => __('URL de base / liste d’URLs', 'b2b-crm-maroc'),
                    'options' => __('Profondeur de crawl / délais (texte)', 'b2b-crm-maroc'),
                    'notes' => __('Notes (règles d’extraction, anti-doublons)', 'b2b-crm-maroc'),
                ),
            ),
            'social' => array(
                'title' => __('Réseaux Sociaux Pro', 'b2b-crm-maroc'),
                'description' => __('URLs ciblées, tokens APIs et scoring IA sur profils pros.', 'b2b-crm-maroc'),
                'fields' => array(
                    'api_key' => __('Token/API key (LinkedIn/Facebook)', 'b2b-crm-maroc'),
                    'endpoint' => __('URLs de recherche / pages', 'b2b-crm-maroc'),
                    'ai_model' => __('Modèle IA (scoring & qualification)', 'b2b-crm-maroc'),
                ),
            ),
            'domains' => array(
                'title' => __('Scan Domaines (.ma, .com...)', 'b2b-crm-maroc'),
                'description' => __('Listes TLD, fournisseurs WHOIS et enrichissement IA.', 'b2b-crm-maroc'),
                'fields' => array(
                    'api_key' => __('Clé API WHOIS/Registrar', 'b2b-crm-maroc'),
                    'options' => __('TLDs ciblés (ex: .ma, .com)', 'b2b-crm-maroc'),
                    'notes' => __('Règles d’enrichissement et exclusions', 'b2b-crm-maroc'),
                ),
            ),
            'institutions' => array(
                'title' => __('Portails Institutionnels', 'b2b-crm-maroc'),
                'description' => __('Portails officiels, APIs ouvertes et extraction IA.', 'b2b-crm-maroc'),
                'fields' => array(
                    'endpoint' => __('URLs / APIs officielles', 'b2b-crm-maroc'),
                    'api_key' => __('Clé API (si disponible)', 'b2b-crm-maroc'),
                    'notes' => __('Notes (fréquence, accès, format)', 'b2b-crm-maroc'),
                ),
            ),
            'excel' => array(
                'title' => __('Fichier Excel / CSV', 'b2b-crm-maroc'),
                'description' => __('Importer un fichier CSV exporté depuis Excel ou un lien direct vers un CSV.', 'b2b-crm-maroc'),
                'fields' => array(
                    'endpoint' => __('URL du fichier CSV', 'b2b-crm-maroc'),
                    'options' => __('Séparateur CSV (ex: delimiter=;)', 'b2b-crm-maroc'),
                    'notes' => __('Colonnes attendues (name, email, phone, city, sector, website).', 'b2b-crm-maroc'),
                ),
            ),
        );
        ?>
        <div class="b2b-crm__section">
            <h2><?php echo esc_html__('Sources de données', 'b2b-crm-maroc'); ?></h2>
            <p class="b2b-crm__muted"><?php echo esc_html__('Choisissez les sources et préparez leurs intégrations IA, APIs et URLs.', 'b2b-crm-maroc'); ?></p>
            <?php settings_errors('b2b-crm-maroc'); ?>
            <form class="b2b-crm__source-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data">
                <?php wp_nonce_field('b2b_crm_save_sources'); ?>
                <input type="hidden" name="action" value="b2b_crm_save_sources" />
                <?php if ($redirect_to) : ?>
                    <input type="hidden" name="redirect_to" value="<?php echo esc_url($redirect_to); ?>" />
                <?php endif; ?>
                <div class="b2b-crm__tabs b2b-crm__tabs--sources" role="tablist">
                    <?php foreach ($sources as $key => $source) : ?>
                        <button type="button" class="b2b-crm__tab-button <?php echo $key === 'google_maps' ? 'is-active' : ''; ?>" data-source-tab="<?php echo esc_attr($key); ?>" role="tab" aria-selected="<?php echo $key === 'google_maps' ? 'true' : 'false'; ?>">
                            <?php echo esc_html($source['title']); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
                <div class="b2b-crm__settings-grid b2b-crm__settings-grid--tabs">
                    <?php foreach ($sources as $key => $source) : ?>
                        <?php
                        $values = isset($saved[$key]) && is_array($saved[$key]) ? $saved[$key] : array();
                        $values = self::merge_source_defaults($defaults[$key] ?? array(), $values);
                        $enabled = !empty($values['enabled']);
                        ?>
                        <div class="b2b-crm__settings-card b2b-crm__settings-card--source <?php echo $key === 'google_maps' ? 'is-active' : ''; ?>" data-source-panel="<?php echo esc_attr($key); ?>" role="tabpanel" <?php echo $key === 'google_maps' ? '' : 'hidden'; ?>>
                            <div class="b2b-crm__settings-header">
                                <h3><?php echo esc_html($source['title']); ?></h3>
                                <label class="b2b-crm__toggle">
                                    <input type="checkbox" name="sources[<?php echo esc_attr($key); ?>][enabled]" value="1" <?php checked($enabled); ?> />
                                    <span><?php echo esc_html__('Activé', 'b2b-crm-maroc'); ?></span>
                                </label>
                            </div>
                            <p><?php echo esc_html($source['description']); ?></p>
                            <div class="b2b-crm__source-fields">
                                <?php foreach ($source['fields'] as $field_key => $label) : ?>
                                    <label>
                                        <span><?php echo esc_html($label); ?></span>
                                        <?php
                                        $field_value = isset($values[$field_key]) ? $values[$field_key] : '';
                                        $is_textarea = in_array($field_key, array('notes', 'options'), true);
                                        ?>
                                        <?php if ($is_textarea) : ?>
                                            <textarea class="b2b-crm__input b2b-crm__input--area" name="sources[<?php echo esc_attr($key); ?>][<?php echo esc_attr($field_key); ?>]" rows="3"><?php echo esc_textarea($field_value); ?></textarea>
                                        <?php else : ?>
                                            <input class="b2b-crm__input" type="text" name="sources[<?php echo esc_attr($key); ?>][<?php echo esc_attr($field_key); ?>]" value="<?php echo esc_attr($field_value); ?>" />
                                        <?php endif; ?>
                                    </label>
                                <?php endforeach; ?>
                                <?php if ($key === 'excel') : ?>
                                    <label>
                                        <span><?php echo esc_html__('Téléverser un fichier CSV', 'b2b-crm-maroc'); ?></span>
                                        <input type="file" name="sources_excel_file" accept=".csv,.xls,.xlsx" />
                                    </label>
                                    <?php if (!empty($values['file_url'])) : ?>
                                        <div class="b2b-crm__source-hint">
                                            <?php echo esc_html__('Fichier actuel :', 'b2b-crm-maroc'); ?>
                                            <a href="<?php echo esc_url($values['file_url']); ?>" target="_blank" rel="noopener noreferrer">
                                                <?php echo esc_html($values['file_url']); ?>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <label>
                                    <span><?php echo esc_html__('Modèle IA (optionnel)', 'b2b-crm-maroc'); ?></span>
                                    <input class="b2b-crm__input" type="text" name="sources[<?php echo esc_attr($key); ?>][ai_model]" value="<?php echo esc_attr(isset($values['ai_model']) ? $values['ai_model'] : ''); ?>" />
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="b2b-crm__section">
                    <button class="b2b-crm__cta" type="submit"><?php echo esc_html__('Enregistrer les sources', 'b2b-crm-maroc'); ?></button>
                </div>
            </form>
        </div>
        <?php
    }

    private static function morocco_cities()
    {
        return array(
            'Agadir',
            'Al Hoceïma',
            'Béni Mellal',
            'Berkane',
            'Berrechid',
            'Boujdour',
            'Boulemane',
            'Casablanca',
            'Chefchaouen',
            'Dakhla',
            'El Jadida',
            'Errachidia',
            'Essaouira',
            'Fès',
            'Figuig',
            'Guelmim',
            'Ifrane',
            'Kénitra',
            'Khemisset',
            'Khouribga',
            'Laâyoune',
            'Larache',
            'Marrakech',
            'Meknès',
            'Mohammédia',
            'Nador',
            'Ouarzazate',
            'Oujda',
            'Rabat',
            'Safi',
            'Salé',
            'Sefrou',
            'Settat',
            'Sidi Ifni',
            'Sidi Kacem',
            'Sidi Slimane',
            'Tanger',
            'Taounate',
            'Taroudant',
            'Taza',
            'Tétouan',
        );
    }

    private static function module_label($key)
    {
        $labels = array(
            'accounts' => __('Entreprise', 'b2b-crm-maroc'),
            'contacts' => __('Contacts', 'b2b-crm-maroc'),
            'base' => __('Prospects', 'b2b-crm-maroc'),
            'emails' => __('Emails', 'b2b-crm-maroc'),
            'tasks' => __('Activités', 'b2b-crm-maroc'),
            'collect' => __('Collecte', 'b2b-crm-maroc'),
            'sources' => __('Sources', 'b2b-crm-maroc'),
            'pipeline' => __('Pipeline', 'b2b-crm-maroc'),
        );

        return $labels[$key] ?? $key;
    }

    public static function render_pipeline(array $columns)
    {
        ?>
        <div class="b2b-crm__section">
            <h2><?php echo esc_html__('Pipeline Commercial', 'b2b-crm-maroc'); ?></h2>
            <div class="b2b-crm__pipeline">
                <?php foreach ($columns as $column) : ?>
                    <div class="b2b-crm__pipeline-column">
                        <div class="b2b-crm__pipeline-header">
                            <span><?php echo esc_html($column['label']); ?></span>
                            <span class="b2b-crm__pipeline-count"><?php echo esc_html($column['count']); ?></span>
                        </div>
                        <div class="b2b-crm__pipeline-body">
                            <?php foreach ($column['items'] as $item) : ?>
                                <div class="b2b-crm__pipeline-card">
                                    <div class="b2b-crm__pipeline-title"><?php echo esc_html($item['company_name']); ?></div>
                                    <span class="b2b-crm__badge b2b-crm__badge--<?php echo esc_attr($item['interest']); ?>"><?php echo esc_html($item['interest_label']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    private static function merge_source_defaults(array $defaults, array $values)
    {
        $merged = $defaults;

        foreach ($values as $key => $value) {
            if ($value === '' || $value === null) {
                continue;
            }
            $merged[$key] = $value;
        }

        return $merged;
    }
}
