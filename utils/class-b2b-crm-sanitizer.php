<?php

if (!defined('ABSPATH')) {
    exit;
}

class B2B_CRM_Sanitizer
{
    public static function lead_fields(array $input)
    {
        $fields = array(
            'company_name' => 'text',
            'sector' => 'text',
            'city' => 'text',
            'contact_name' => 'text',
            'contact_role' => 'text',
            'phone' => 'text',
            'email' => 'email',
            'social_json' => 'json',
            'status' => 'key',
            'last_contact' => 'datetime',
            'next_action' => 'text',
            'follow_up_date' => 'date',
            'interest_level' => 'key',
            'notes' => 'text',
            'source' => 'text',
            'collected_at' => 'datetime',
            'collected_method' => 'text',
        );

        $clean = array();

        foreach ($fields as $field => $type) {
            if (!array_key_exists($field, $input)) {
                continue;
            }

            $value = $input[$field];
            switch ($type) {
                case 'email':
                    $clean[$field] = sanitize_email($value);
                    break;
                case 'key':
                    $clean[$field] = sanitize_key($value);
                    break;
                case 'json':
                    $decoded = json_decode(wp_unslash($value), true);
                    $clean[$field] = $decoded === null ? null : wp_json_encode($decoded);
                    break;
                case 'datetime':
                    $clean[$field] = sanitize_text_field($value);
                    break;
                case 'date':
                    $clean[$field] = sanitize_text_field($value);
                    break;
                default:
                    $clean[$field] = sanitize_text_field($value);
                    break;
            }
        }

        return $clean;
    }
}
