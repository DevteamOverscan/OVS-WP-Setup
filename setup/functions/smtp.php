<?php
/**
 *
 * @package OVS
 * @author Overscan
 * @link https://www.overscan.com
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}



// Fonction pour configurer l'envoi d'e-mails via SMTP
function wp_mail_smtp($phpmailer)
{
    $phpmailer->isSMTP(); // Utiliser SMTP
    $phpmailer->Host       = SMTP_HOST;
    $phpmailer->SMTPAuth   = true;
    $phpmailer->Port       = SMTP_PORT;
    $phpmailer->Username   = SMTP_USERNAME;
    $phpmailer->Password   = SMTP_PASSWORD;
    $phpmailer->SMTPSecure = SMTP_SECURE;
}

if (
    defined('SMTP_HOST') && !empty(SMTP_HOST) &&
    defined('SMTP_PORT') && !empty(SMTP_PORT) &&
    defined('SMTP_USERNAME') && !empty(SMTP_USERNAME) &&
    defined('SMTP_PASSWORD') && !empty(SMTP_PASSWORD) &&
    defined('SMTP_SECURE') && !empty(SMTP_SECURE)
) {
    // Si toutes les constantes sont définies et non vides, configurer l'envoi d'e-mails via SMTP
    add_action('phpmailer_init', 'wp_mail_smtp');
}
