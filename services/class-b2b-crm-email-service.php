<?php

if (!defined('ABSPATH')) {
    exit;
}

class B2B_CRM_Email_Service
{
    public static function send($lead, $subject, $message)
    {
        if (empty($lead['email'])) {
            return new WP_Error('missing_email', __('Aucun email professionnel renseigné.', 'b2b-crm-maroc'));
        }

        $headers = array('Content-Type: text/html; charset=UTF-8');

        $sent = wp_mail($lead['email'], $subject, $message, $headers);

        if (!$sent) {
            return new WP_Error('email_failed', __('Envoi impossible.', 'b2b-crm-maroc'));
        }

        return true;
    }
}
