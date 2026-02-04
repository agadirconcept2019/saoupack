<?php

if (!defined('ABSPATH')) {
    exit;
}

class B2B_CRM_Views
{
    public static function render_dashboard($stats, array $recent)
    {
        ?>
        <div class="b2b-crm__section">
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
        <div class="b2b-crm__section">
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

    public static function render_collect()
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
        );
        ?>
        <div class="b2b-crm__section">
            <h2><?php echo esc_html__('Moteur de Recherche Intelligent', 'b2b-crm-maroc'); ?></h2>
            <?php settings_errors('b2b-crm-maroc'); ?>
            <form class="b2b-crm__collect" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('b2b_crm_run_collect'); ?>
                <input type="hidden" name="action" value="b2b_crm_run_collect" />
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

    public static function render_settings()
    {
        ?>
        <div class="b2b-crm__section">
            <h2><?php echo esc_html__('Paramétrage & Fonctionnalités', 'b2b-crm-maroc'); ?></h2>
            <p class="b2b-crm__muted"><?php echo esc_html__('Centralisez ici tous les réglages du plugin et accédez rapidement aux modules clés.', 'b2b-crm-maroc'); ?></p>
            <div class="b2b-crm__settings-grid">
                <div class="b2b-crm__settings-card">
                    <h3><?php echo esc_html__('Collecte intelligente', 'b2b-crm-maroc'); ?></h3>
                    <p><?php echo esc_html__('Définissez la ville, le secteur, les sources et la profondeur de recherche.', 'b2b-crm-maroc'); ?></p>
                    <a class="b2b-crm__link" href="<?php echo esc_url(add_query_arg(array('page' => 'b2b-crm-maroc', 'tab' => 'sources'), admin_url('admin.php'))); ?>"><?php echo esc_html__('Configurer la collecte', 'b2b-crm-maroc'); ?></a>
                </div>
                <div class="b2b-crm__settings-card">
                    <h3><?php echo esc_html__('Base de leads', 'b2b-crm-maroc'); ?></h3>
                    <p><?php echo esc_html__('Suivez, filtrez et exportez les leads enregistrés.', 'b2b-crm-maroc'); ?></p>
                    <a class="b2b-crm__link" href="<?php echo esc_url(add_query_arg(array('page' => 'b2b-crm-maroc', 'tab' => 'base'), admin_url('admin.php'))); ?>"><?php echo esc_html__('Ouvrir la base', 'b2b-crm-maroc'); ?></a>
                </div>
                <div class="b2b-crm__settings-card">
                    <h3><?php echo esc_html__('Pipeline CRM', 'b2b-crm-maroc'); ?></h3>
                    <p><?php echo esc_html__('Visualisez l’avancement commercial et les statuts.', 'b2b-crm-maroc'); ?></p>
                    <a class="b2b-crm__link" href="<?php echo esc_url(add_query_arg(array('page' => 'b2b-crm-maroc', 'tab' => 'pipeline'), admin_url('admin.php'))); ?>"><?php echo esc_html__('Voir le pipeline', 'b2b-crm-maroc'); ?></a>
                </div>
                <div class="b2b-crm__settings-card">
                    <h3><?php echo esc_html__('Dashboard', 'b2b-crm-maroc'); ?></h3>
                    <p><?php echo esc_html__('Consultez les statistiques, les performances et l’activité récente.', 'b2b-crm-maroc'); ?></p>
                    <a class="b2b-crm__link" href="<?php echo esc_url(add_query_arg(array('page' => 'b2b-crm-maroc', 'tab' => 'dashboard'), admin_url('admin.php'))); ?>"><?php echo esc_html__('Revenir au dashboard', 'b2b-crm-maroc'); ?></a>
                </div>
            </div>
        </div>
        <?php
        self::render_collect();
    }

    public static function render_sources()
    {
        $saved = get_option('b2b_crm_sources_config', array());
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
        );
        ?>
        <div class="b2b-crm__section">
            <h2><?php echo esc_html__('Sources de données', 'b2b-crm-maroc'); ?></h2>
            <p class="b2b-crm__muted"><?php echo esc_html__('Choisissez les sources et préparez leurs intégrations IA, APIs et URLs.', 'b2b-crm-maroc'); ?></p>
            <?php settings_errors('b2b-crm-maroc'); ?>
            <form class="b2b-crm__source-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('b2b_crm_save_sources'); ?>
                <input type="hidden" name="action" value="b2b_crm_save_sources" />
                <div class="b2b-crm__settings-grid">
                    <?php foreach ($sources as $key => $source) : ?>
                        <?php
                        $values = isset($saved[$key]) && is_array($saved[$key]) ? $saved[$key] : array();
                        $enabled = !empty($values['enabled']);
                        ?>
                        <div class="b2b-crm__settings-card b2b-crm__settings-card--source">
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
}
