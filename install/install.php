<?php

/**
 *
 * @package OVS
 * @author Clément Vacheron
 * @link https://www.overscan.com
 */


if (file_exists(dirname(__DIR__) . '/managerFiles.php')) {
    require_once dirname(__DIR__) . '/managerFiles.php';
}

function install()
{

    if(isset($_POST['function']) && $_POST['function'] === 'remove') {
        update_option('install', true);
        ManagerFiles::deleteDirectory(dirname(__FILE__));
    }

    if (isset($_POST['function']) && $_POST['function'] === 'theme') {
        require_once dirname(__FILE__) . '/theme/generateChildTheme.php';
    } elseif (isset($_POST['function']) && $_POST['function'] === 'security') {
        require_once dirname(__FILE__) . '/shield/shield.php';
    } elseif (isset($_POST['function']) && $_POST['function'] === 'plugins') {
        require_once dirname(__FILE__) . '/plugins/plugins.php';
    } elseif (isset($_POST['function']) && $_POST['function'] === 'features') {
        if (!empty($_POST['features']) && is_array(get_option('features'))) {
            $features = array_merge($_POST['features'], get_option('features'));
            update_option('features', $features);
            wp_send_json(array(
              'status' => 'success',
              'message' => 'Installation des Fonctionnalités terminée avec succès.'
            ));
        }
        wp_send_json(array(
          'status' => 'success',
          'message' => 'Aucune fonctionnalité ajouté selon vos choix.'
        ));
    } else {
        wp_send_json_error('Pas d\'action correspondante');
    }
}

function loadFeatures()
{
    return array_merge($_POST['features'], get_option('features'));
}
