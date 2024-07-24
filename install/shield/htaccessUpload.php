<?php
/**
 *
 * @package OVS
 * @autor Clément Vacheron
 * @link https://www.overscan.com
 */

if (!defined('ABSPATH')) {
    exit;
}
/*-------------------------------------------------
                SECURITY HTACCESS
--------------------------------------------------*/
function htaccessUpload()
{
    $uploadDir = ABSPATH . WP_CONTENT_FOLDERNAME . '/uploads';
    $htaccessFile = $uploadDir . '/.htaccess';

    // Vérifie si le dossier uploads existe, sinon le crée
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Règles .htaccess à ajouter
    $rules = "
# BEGIN DISABLE PHP ENGINE
<Files *>
    SetHandler none
    SetHandler default-handler
    Options -ExecCGI
    RemoveHandler .cgi .php .php3 .php4 .php5 .php7 .phtml .pl .py .pyc .pyo
</Files>
<IfModule mod_php7.c>
    php_flag engine off
</IfModule>
<IfModule mod_php5.c>
    php_flag engine off
</IfModule>
# END DISABLE PHP ENGINE
";

    // Écrit les règles dans le fichier .htaccess
    $results = file_put_contents($htaccessFile, $rules, FILE_APPEND | LOCK_EX);

    return $results;
}

// Appelle la fonction htaccessUpload
htaccessUpload();
