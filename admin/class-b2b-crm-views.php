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
