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
    $customPlugins = array('git@github.com:DevteamOverscan/OVS-WP-Entity.git','git@github.com:DevteamOverscan/OVS-WP-Error-Page.git');

    $customPluginsName = [];
    foreach ($customPlugins as $value) {
        preg_match('/\/([^\/]+)\.git$/', $value, $matches);
        $customPluginsName[] = $matches[1];
        $results[] = ManagerFiles::installPluginFromGit($value);
    }
    update_option('custom_plugins', $customPluginsName);

    if(in_array(false, $results)) {
        wp_send_json(array('status' => 'error',
            'message' => $results));
    } else {
        wp_send_json(array('status' => 'success',
            'message' => 'Installation des plugins terminée avec succès.'));
    }

}
loadPlugin();
