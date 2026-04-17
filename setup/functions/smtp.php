<?php
/**
 * Configure l'envoi des e-mails WordPress via SMTP.
 *
 * @package OVS
 * @author Overscan
 * @link https://www.overscan.com
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Applique la configuration SMTP au client mail WordPress.
 */
function wp_mail_smtp($phpmailer)
{
    $phpmailer->isSMTP(); // Utiliser le protocole SMTP.
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
    // Activer SMTP uniquement si toutes les constantes requises sont définies.
    add_action('phpmailer_init', 'wp_mail_smtp');
}
