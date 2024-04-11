<?php
/**
 *
 * @package OVS
 * @author Clément Vacheron
 * @link https://www.overscan.com
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
function move_scripts_to_footer()
{
    // Défilement de la file d'attente des scripts jQuery vers le pied de page
    wp_scripts()->add_data('jquery', 'group', 1);
    wp_scripts()->add_data('jquery-core', 'group', 1);
    wp_scripts()->add_data('jquery-migrate', 'group', 1);

    // Défilement de tous les autres scripts vers le pied de page
    foreach(wp_scripts()->registered as $script) {
        wp_script_add_data($script->handle, 'group', 1);
    }
}
add_action('wp_enqueue_scripts', 'move_scripts_to_footer');

// Filtrer les attributs des balises de script pour charger les scripts de manière asynchrone ou différée
add_filter('script_loader_tag', function ($tag, $handle) {
    // Récupérer l'objet global $wp_scripts
    global $wp_scripts;

    // Vérifier si le script en cours de chargement est présent dans la file d'attente des scripts
    if (isset($wp_scripts->registered[$handle])) {
        // Récupérer les données du script à partir de l'objet global $wp_scripts
        $script = $wp_scripts->registered[$handle];

        // Vérifier si l'attribut 'async' ou 'defer' est défini dans les attributs supplémentaires du script
        if (isset($script->extra['async']) && $script->extra['async']) {
            // Ajouter l'attribut async à la balise de script
            $tag = str_replace(' src', ' async="async" src', $tag);
        } elseif (isset($script->extra['defer']) && $script->extra['defer']) {
            // Ajouter l'attribut defer à la balise de script
            $tag = str_replace(' src', ' defer="defer" src', $tag);
        }
    }

    return $tag;

}, 10, 2);

function my_init()
{
    if (!is_admin()) {
        wp_deregister_script('jquery');
        wp_register_script('jquery', false);
    }
}
add_action('init', 'my_init');


function extract_inline_styles($html, $handle, $href, $media)
{
    // Vérifie si le script est un fichier CSS et non inline
    if (strpos($html, 'type="text/css" data-rocket') !== false) {
        // Récupère le contenu CSS inline
        preg_match_all('#<style(.*?)>(.*?)</style>#is', $html, $matches);

        // Si du CSS inline est trouvé
        if (isset($matches[2]) && ! empty($matches[2])) {
            $inline_css = implode("\n", $matches[2]);

            // Supprime le CSS inline de la balise <style>
            $html = str_replace($matches[0], '', $html);

            // Écrit le CSS inline dans un fichier externe
            $upload_dir = wp_upload_dir();
            $css_file = $upload_dir['basedir'] . '/extracted-inline-styles.css';

            // Écrit le CSS dans le fichier
            file_put_contents($css_file, $inline_css, FILE_APPEND);

            // Ajoute le fichier CSS externe dans la balise <link>
            $html .= '<link rel="stylesheet" href="' . $upload_dir['baseurl'] . '/extracted-inline-styles.css" type="text/css" media="' . esc_attr($media) . '" />';
        }
    }
    return $html;
}
add_filter('style_loader_tag', 'extract_inline_styles', 10, 4);

function print_external_css()
{
    $upload_dir = wp_upload_dir();
    $css_file = $upload_dir['basedir'] . '/extracted-inline-styles.css';

    // Vérifie si le fichier externe existe
    if (file_exists($css_file)) {
        // Lit le contenu du fichier
        $external_css = file_get_contents($css_file);

        // Supprime le fichier externe
        unlink($css_file);

        // Affiche le contenu du fichier CSS
        echo '<style type="text/css">' . $external_css . '</style>';
    }
}
add_action('wp_print_styles', 'print_external_css');
