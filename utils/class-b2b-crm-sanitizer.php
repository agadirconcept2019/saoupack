<?php

if (!defined('ABSPATH')) {
    exit;
}

class B2B_CRM_Sanitizer
{
    public static function lead_fields(array $input)
    {
        $allowed_statuses = self::allowed_statuses();
        $allowed_interests = self::allowed_interest_levels();
        $allowed_stages = self::allowed_stages();

        $fields = array(
            'company_name' => 'text',
            'sector' => 'text',
            'city' => 'text',
            'contact_name' => 'text',
            'contact_role' => 'text',
            'phone' => 'text',
            'phone_mobile' => 'text',
            'email' => 'email',
            'website' => 'text',
            'social_json' => 'json',
            'status' => 'key',
            'stage' => 'stage',
            'owner_user_id' => 'int',
            'last_contact' => 'datetime',
            'next_action' => 'text',
            'follow_up_date' => 'date',
            'interest_level' => 'key',
            'tags' => 'text',
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
                    $sanitized = sanitize_key($value);
                    if ($field === 'status' && !in_array($sanitized, $allowed_statuses, true)) {
                        $map = array(
                            'nouveau' => 'new',
                            'qualifie' => 'qualified',
                            'contacte' => 'contacted',
                            'inactif' => 'inactive',
                        );
                        if (isset($map[$sanitized])) {
                            $sanitized = $map[$sanitized];
                        } else {
                            break;
                        }
                    }
                    if ($field === 'interest_level' && !in_array($sanitized, $allowed_interests, true)) {
                        $map = array(
                            'faible' => 'low',
                            'moyen' => 'medium',
                            'fort' => 'high',
                        );
                        if (isset($map[$sanitized])) {
                            $sanitized = $map[$sanitized];
                        } else {
                            break;
                        }
                    }
                    $clean[$field] = $sanitized;
                    break;
                case 'stage':
                    $clean_stage = sanitize_text_field($value);
                    if (!self::is_valid_stage($clean_stage, $allowed_stages)) {
                        break;
                    }
                    $clean[$field] = $clean_stage;
                    break;
                case 'int':
                    $clean[$field] = absint($value);
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

    public static function allowed_statuses()
    {
        return array('new', 'qualified', 'contacted', 'inactive');
    }

    public static function allowed_interest_levels()
    {
        return array('low', 'medium', 'high');
    }

    public static function allowed_stages()
    {
        return B2B_CRM_Lead_Repository::stages();
    }

    public static function is_valid_status($value)
    {
        $sanitized = sanitize_key($value);
        return in_array($sanitized, self::allowed_statuses(), true);
    }

    public static function is_valid_interest_level($value)
    {
        $sanitized = sanitize_key($value);
        return in_array($sanitized, self::allowed_interest_levels(), true);
    }

    public static function is_valid_stage($value, $allowed_stages = null)
    {
        $value = sanitize_text_field($value);
        if ($value === '') {
            return true;
        }
        $allowed = $allowed_stages === null ? self::allowed_stages() : $allowed_stages;
        if (empty($allowed)) {
            return true;
        }
        return in_array($value, $allowed, true);
    }
}
