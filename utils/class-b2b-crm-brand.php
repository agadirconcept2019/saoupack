<?php

if (!defined('ABSPATH')) {
    exit;
}

class B2B_CRM_Brand
{
    public static function get_brand_logo_url()
    {
        $settings = get_option('b2b_crm_settings', array());
        $candidate = isset($settings['logo_url']) ? self::sanitize_logo_url($settings['logo_url']) : '';

        if ($candidate) {
            return $candidate;
        }

        return B2B_CRM_MAROC_URL . 'assets/images/saoupack-icon.svg';
    }

    public static function sanitize_logo_url($url)
    {
        $url = esc_url_raw((string) $url);
        if ($url === '') {
            return '';
        }

        $parts = wp_parse_url($url);
        if (empty($parts['scheme']) || empty($parts['host'])) {
            return '';
        }

        $scheme = strtolower($parts['scheme']);
        $host = strtolower($parts['host']);
        $allowed_hosts = array('localhost', '127.0.0.1', '::1');

        if ($scheme === 'https') {
            return $url;
        }

        if ($scheme === 'http' && in_array($host, $allowed_hosts, true)) {
            return $url;
        }

        return '';
    }
}
