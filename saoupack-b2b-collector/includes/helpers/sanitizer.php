<?php

if (!defined('ABSPATH')) {
    exit;
}

class Saoupack_B2B_Collector_Sanitizer
{
    private const PRIORITIES = array('high', 'medium');

    private const CRM_STATUSES = array(
        'new',
        'qualified',
        'contact_initiated',
        'proposal_sent',
        'negotiation',
        'won',
        'lost',
    );

    private const SOCIAL_KEYS = array(
        'linkedin',
        'facebook',
        'instagram',
        'x',
        'youtube',
        'tiktok',
    );

    public static function sanitize_id($value)
    {
        return absint($value);
    }

    public static function sanitize_lead(array $input, $partial = false)
    {
        $fields = array(
            'company_name' => 'text',
            'city' => 'text',
            'phone' => 'text',
            'email' => 'email',
            'website' => 'url',
            'social_links' => 'social',
            'priority' => 'priority',
            'crm_status' => 'status',
            'source' => 'text',
            'notes' => 'textarea',
        );

        $clean = array();

        foreach ($fields as $field => $type) {
            if (!array_key_exists($field, $input)) {
                if ($partial) {
                    continue;
                }
                $clean[$field] = null;
                continue;
            }

            $value = $input[$field];
            switch ($type) {
                case 'email':
                    $clean[$field] = sanitize_email($value);
                    break;
                case 'url':
                    $clean[$field] = esc_url_raw($value);
                    break;
                case 'social':
                    $clean[$field] = self::sanitize_social_links($value);
                    break;
                case 'priority':
                    $priority = sanitize_key($value);
                    $clean[$field] = in_array($priority, self::PRIORITIES, true) ? $priority : null;
                    break;
                case 'status':
                    $status = sanitize_key($value);
                    $clean[$field] = in_array($status, self::CRM_STATUSES, true) ? $status : null;
                    break;
                case 'textarea':
                    $clean[$field] = sanitize_textarea_field($value);
                    break;
                default:
                    $clean[$field] = sanitize_text_field($value);
                    break;
            }
        }

        if (!$partial) {
            $clean = array_filter($clean, static function ($value) {
                return $value !== null;
            });
        }

        return $clean;
    }

    public static function sanitize_social_links($value)
    {
        $links = array();

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                $value = $decoded;
            }
        }

        if (is_array($value)) {
            foreach (self::SOCIAL_KEYS as $key) {
                if (!array_key_exists($key, $value)) {
                    continue;
                }
                $url = trim((string) $value[$key]);
                if ($url === '') {
                    continue;
                }
                $links[$key] = esc_url_raw($url);
            }
        }

        if (empty($links)) {
            return '';
        }

        return wp_json_encode($links);
    }

    public static function valid_priority($value)
    {
        return in_array($value, self::PRIORITIES, true);
    }

    public static function valid_status($value)
    {
        return in_array($value, self::CRM_STATUSES, true);
    }

    public static function priorities()
    {
        return self::PRIORITIES;
    }

    public static function crm_statuses()
    {
        return self::CRM_STATUSES;
    }
}
