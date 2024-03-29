<?php
/**
 * Class Setup
 * @package OVS
 * @author Clément Vacheron
 * @link https://www.overscan.com
 * Main Plugin class
 * @since 1
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (file_exists(dirname(__DIR__) . '/managerFiles.php')) {
    require_once dirname(__DIR__) . '/managerFiles.php';
}

function loadPlugin()
{
    $plugins = array('contact-form-7','wp-seopress','resmushit-image-optimizer','webp-converter-for-media','two-factor');
    $results = [];
    foreach ($plugins as $value) {
        $results[] = ManagerFiles::installExternalFile($value, 'plugin');
    }
    $customPlugins = array('ovs-entity','ovs-page-error');

    if (!get_option('custom_plugins', false)) {
        update_option('custom_plugins', $customPlugins);
    }

    foreach ($customPlugins as $value) {
        $results[] = ManagerFiles::unzipFile(dirname(__FILE__) . '/'.
        $value .'.zip', ABSPATH . 'wp-content/mu-plugins');
    }
    if(in_array(false, $results)) {
        wp_send_json(array('status' => 'error',
            'message' => $results));
    } else {
        wp_send_json(array('status' => 'success',
            'message' => 'Installation des plugins terminée avec succès.'));
    }

}
loadPlugin();
