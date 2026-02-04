<?php

if (!defined('ABSPATH')) {
    exit;
}

class B2B_CRM_Collector
{
    public static function run($city, $sector, $precision, array $sources_config, array $selected_sources)
    {
        $results = array(
            'counts' => array(),
            'errors' => array(),
        );

        $enabled_sources = array_filter($selected_sources, function ($key) use ($sources_config) {
            return isset($sources_config[$key]['enabled']) && $sources_config[$key]['enabled'];
        });

        foreach ($enabled_sources as $source_key) {
            $config = isset($sources_config[$source_key]) ? $sources_config[$source_key] : array();
            $counts = 0;

            if ($source_key === 'google_maps') {
                $counts = self::collect_google_maps($city, $sector, $config, $precision, $results['errors']);
            } else {
                $counts = self::collect_from_json_endpoint($source_key, $city, $sector, $config, $precision, $results['errors']);
            }

            $results['counts'][$source_key] = $counts;
        }

        return $results;
    }

    private static function collect_google_maps($city, $sector, array $config, $precision, array &$errors)
    {
        $api_key = isset($config['api_key']) ? $config['api_key'] : '';
        if (empty($api_key)) {
            $errors[] = __('Clé API Google manquante pour Google Maps.', 'b2b-crm-maroc');
            return 0;
        }

        $endpoint = isset($config['endpoint']) && !empty($config['endpoint'])
            ? $config['endpoint']
            : 'https://maps.googleapis.com/maps/api/place/textsearch/json';

        $query = trim($sector . ' ' . $city);
        if ($query === '') {
            $errors[] = __('Ville ou secteur manquant pour Google Maps.', 'b2b-crm-maroc');
            return 0;
        }

        $args = array(
            'timeout' => 20,
        );
        $url = add_query_arg(
            array(
                'query' => $query,
                'key' => $api_key,
            ),
            $endpoint
        );

        $response = wp_remote_get($url, $args);
        if (is_wp_error($response)) {
            $errors[] = $response->get_error_message();
            return 0;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        if (!is_array($data) || empty($data['results'])) {
            return 0;
        }

        $count = 0;
        foreach ($data['results'] as $item) {
            if (empty($item['name'])) {
                continue;
            }

            $payload = array(
                'company_name' => $item['name'],
                'sector' => $sector,
                'city' => $city,
                'source' => 'google_maps',
                'collected_method' => 'Google Places API',
            );

            B2B_CRM_Lead_Repository::upsert($payload);
            $count++;

            if ($precision === 'standard' && $count >= 20) {
                break;
            }
        }

        return $count;
    }

    private static function collect_from_json_endpoint($source_key, $city, $sector, array $config, $precision, array &$errors)
    {
        $endpoint = isset($config['endpoint']) ? $config['endpoint'] : '';
        if (empty($endpoint)) {
            $errors[] = sprintf(
                __('Endpoint manquant pour la source %s.', 'b2b-crm-maroc'),
                $source_key
            );
            return 0;
        }

        $response = wp_remote_get($endpoint, array('timeout' => 20));
        if (is_wp_error($response)) {
            $errors[] = $response->get_error_message();
            return 0;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['results']) && is_array($data['results'])) {
            $items = $data['results'];
        } elseif (is_array($data)) {
            $items = $data;
        } else {
            $errors[] = sprintf(
                __('Format JSON invalide pour la source %s.', 'b2b-crm-maroc'),
                $source_key
            );
            return 0;
        }

        $count = 0;
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $social = array();
            if (!empty($item['social']) && is_array($item['social'])) {
                $social = $item['social'];
            }

            $payload = array(
                'company_name' => self::pick_field($item, array('company_name', 'name', 'company')),
                'sector' => self::pick_field($item, array('sector')) ?: $sector,
                'city' => self::pick_field($item, array('city')) ?: $city,
                'email' => self::pick_field($item, array('email')),
                'phone' => self::pick_field($item, array('phone')),
                'phone_mobile' => self::pick_field($item, array('phone_mobile', 'mobile')),
                'website' => self::pick_field($item, array('website', 'site')),
                'social_json' => empty($social) ? null : wp_json_encode($social),
                'source' => $source_key,
                'collected_method' => self::source_label($source_key),
            );

            if (empty($payload['company_name'])) {
                continue;
            }

            B2B_CRM_Lead_Repository::upsert($payload);
            $count++;

            if ($precision === 'standard' && $count >= 20) {
                break;
            }
        }

        return $count;
    }

    private static function pick_field(array $item, array $keys)
    {
        foreach ($keys as $key) {
            if (!empty($item[$key])) {
                return $item[$key];
            }
        }

        return '';
    }

    private static function source_label($source_key)
    {
        $labels = array(
            'directories' => 'Annuaires',
            'social' => 'Réseaux sociaux',
            'domains' => 'Scan domaines',
            'institutions' => 'Portails institutionnels',
        );

        return $labels[$source_key] ?? $source_key;
    }
}
