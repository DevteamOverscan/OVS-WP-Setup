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
class ManagerFiles
{
    public static function installExternalFile($item_slug, $item_type)
    {
        // Construction de l'URL de l'API WordPress.org en fonction du type d'élément
        if ($item_type === 'theme') {
            $api_url = 'https://api.wordpress.org/themes/info/1.2/?action=theme_information&request[slug]=' . $item_slug;
        } elseif ($item_type === 'plugin') {
            $api_url = 'https://api.wordpress.org/plugins/info/1.0/' . $item_slug . '.json';
        } else {
            throw new Exception('Type d\'élément non pris en charge.');
        }

        // Récupération des informations sur l'élément depuis l'API WordPress.org
        $response = wp_remote_get($api_url);
        if (is_wp_error($response) || $response['response']['code'] !== 200) {
            throw new Exception('Erreur lors de la récupération des informations sur l\'élément depuis l\'API WordPress.org.');
        }

        // Analyse de la réponse JSON
        $item_info = json_decode(wp_remote_retrieve_body($response), true);

        // Vérification de la disponibilité du lien de téléchargement
        if (!isset($item_info['download_link'])) {
            throw new Exception('Le lien de téléchargement de l\'élément n\'est pas disponible.');
        }

        // Téléchargement du fichier ZIP depuis l'URL de téléchargement
        $temp_zip_file = self::downloadFile($item_info['download_link']);

        // Vérification du succès du téléchargement
        if (!$temp_zip_file) {
            throw new Exception('Erreur lors du téléchargement du fichier ZIP depuis l\'URL de téléchargement.');
        }

        // Emplacement où extraire l'élément
        $destination = ($item_type === 'theme') ? get_theme_root() : WP_PLUGIN_DIR;

        // Dézippage du fichier
        $unzip_result = self::unzipFile($temp_zip_file, $destination);
        if ($unzip_result !== true) {
            throw new Exception('Erreur lors du dézippage : ' . $unzip_result);
        }

        // Suppression du fichier ZIP temporaire
        unlink($temp_zip_file);

        return true;
    }

    private static function downloadFile($url)
    {
        $response = wp_remote_get($url);
        if (is_wp_error($response) || $response['response']['code'] !== 200) {
            return false;
        }

        $temp_zip_file = tempnam(sys_get_temp_dir(), 'theme_zip');
        if (!file_put_contents($temp_zip_file, $response['body'])) {
            unlink($temp_zip_file); // Suppression du fichier temporaire en cas d'échec
            return false;
        }

        return $temp_zip_file;
    }

    public static function unzipFile($file, $destination)
    {
        if (!function_exists('unzip_file')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        global $wp_filesystem;
        if (!$wp_filesystem) {
            WP_Filesystem();
        }

        $unzip_result = unzip_file($file, $destination);
        if (is_wp_error($unzip_result)) {
            return $unzip_result->get_error_message();
        }

        return true;
    }

    public static function deleteDirectory($dir)
    {
        if (!is_dir($dir)) {
            return false;
        }

        $files = array_diff(scandir($dir), array('.', '..'));

        foreach ($files as $file) {
            if (is_dir("$dir/$file")) {
                self::deleteDirectory("$dir/$file");
            } else {
                unlink("$dir/$file");
            }
        }

        return rmdir($dir);
    }
    public static function installPluginFromGit($repo_url)
    {
        $path_dir = dirname(__FILE__, 3);
        // Récupérer le nom du dépôt
        $pattern = '/\/([^\/]+)\.git$/'; // Expression régulière pour capturer le nom du dépôt
        preg_match($pattern, $repo_url, $matches);

        if (isset($matches[1]) && file_exists($path_dir . '/' . $matches[1])) {
            return $matches[1] . 'déjà installé. Supprimer le fichier et relancer le processus d\'installation';
        }
        // Changer le répertoire de travail vers mu-plugins
        chdir($path_dir);

        // Exécuter la commande Git en utilisant shell_exec
        $output = shell_exec("git clone {$repo_url}");

        return $matches[1];
    }
}
