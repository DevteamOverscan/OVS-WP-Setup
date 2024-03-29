<?php
/**
 *
 * @package OVS
 * @author Clément Vacheron
 * @link https://www.overscan.com
 */

if (!defined('ABSPATH')) {
    exit;
}
function init()
{
    $roots_includes = array(
        'htaccessBase',
        'htaccessContent',
        'htaccessInclude',
        'htaccessUpload',
    );
    foreach ($roots_includes as $file) {
        $filepath = dirname(__FILE__) . '/' . $file . '.php';
        if (file_exists($filepath)) {
            require_once $filepath;
        } else {
            trigger_error("Error locating `$filepath` for inclusion!", E_USER_ERROR);
        }
    }
    if(!htaccessBase() || !htaccessContent() || !htaccessInclude() || !htaccessUpload()) {
        wp_send_json_error(array('status' => 'error',
                    'message' => 'Une erreur c\'est produite'));
    }
    wp_send_json(array('status' => 'success',
                'message' => 'Installation des règles de sécurité terminée avec succès.'));
}
init();
