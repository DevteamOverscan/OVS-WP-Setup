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

if (file_exists(dirname(__DIR__) . '/managerFiles.php')) {
    require_once dirname(__DIR__) . '/managerFiles.php';
}
function init()
{
    $results = loadTheme();
    StyleTheme();
    if ($results) {
        wp_send_json(array(
          'status' => 'success',
          'message' => 'Installation du Thème enfant terminée avec succès.'
        ));
    }
    wp_send_json_error(array(
      'status' => 'error',
      'message' => $results
    ));
}
function loadTheme()
{
    if(!is_dir(ABSPATH . WP_CONTENT_FOLDERNAME . '/themes/'. $_POST['theme'])) {
        if(!ManagerFiles::installExternalFile($_POST['theme'], 'theme')) {
            return false;
        }
    }
    if(!ManagerFiles::unzipFile(dirname(__FILE__) . '/ovs.zip', ABSPATH . WP_CONTENT_FOLDERNAME . '/themes/')) {
        return false;
    }

    return true;
}

function StyleTheme()
{

    $content = "/*!
Theme Name:     " . get_option('blogname') . "
Description:    Thème fait par Overscan. Experte du digital et partenaire de votre stratégie globale, l'agence Overscan vous accompagne sur l'ensemble de votre communication.
Créée en 1992, Overscan lie papier et digital avec subtilité et créativité. Nous écrivons vos histoires et les racontons à travers le graphisme et la technologie pour offrir une expérience unique à vos utilisateurs.
Des compétences maîtrisées par des équipes complémentaires de 25 collaborateurs enthousiastes et en soif de découvertes qui font la force de l'agence clermontoise.
Author:         Overscan
Author URI:     https://www.overscan.com
Template:       " . $_POST['theme'] . "
Text Domain: ovs
Domain Path: /languages
Version:        1.0
*/

";
    $filesPath = [ABSPATH . WP_CONTENT_FOLDERNAME . '/themes/ovs/style.scss', ABSPATH . WP_CONTENT_FOLDERNAME . '/themes/ovs/style.css'];
    if (file_exists(ABSPATH . WP_CONTENT_FOLDERNAME . '/themes/ovs')) {
        foreach ($filesPath as $key => $filePath) {
            if ($key === 0 && !file_exists($filePath)) {
                $results = file_put_contents($filePath, $content . '@import "assets/css/front/index";', FILE_APPEND | LOCK_EX);
            }
            if (!file_exists($filePath)) {
                $results = file_put_contents($filePath, $content, FILE_APPEND | LOCK_EX);
            }
        }
        return $results;
    }
}
init();
