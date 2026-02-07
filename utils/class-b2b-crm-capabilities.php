<?php

if (!defined('ABSPATH')) {
    exit;
}

class B2B_CRM_Capabilities
{
    public static function register()
    {
        add_filter('user_has_cap', array(__CLASS__, 'grant_access_cap'), 10, 4);
    }

    public static function grant_access_cap($allcaps, $caps, $args, $user)
    {
        if (!empty($allcaps['manage_options'])) {
            $allcaps[B2B_CRM_MAROC_ACCESS_CAP] = true;
        }

        return $allcaps;
    }

    public static function can_access_admin()
    {
        return current_user_can('manage_options') || current_user_can(B2B_CRM_MAROC_ACCESS_CAP);
    }

    public static function can_manage_leads()
    {
        return current_user_can('manage_options') || current_user_can(B2B_CRM_MAROC_LEADS_CAP);
    }

    public static function can_manage_settings()
    {
        return current_user_can('manage_options') || current_user_can(B2B_CRM_MAROC_SETTINGS_CAP);
    }

    public static function can_manage_sources()
    {
        return current_user_can('manage_options') || current_user_can(B2B_CRM_MAROC_SOURCES_CAP);
    }

    public static function can_send_email()
    {
        return current_user_can('manage_options') || current_user_can(B2B_CRM_MAROC_EMAIL_CAP);
    }
}
