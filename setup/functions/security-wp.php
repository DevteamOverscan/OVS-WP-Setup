<?php
/**
 * Renforce la sécurité générale d'une installation WordPress.
 *
 * @package OVS
 * @author Overscan
 * @link https://www.overscan.com
 */

if (!defined('ABSPATH')) {
    exit;
}

// ==============================================
// Nettoyage de WordPress
// ==============================================

// Retirer la version de WordPress du code source généré.
remove_action('wp_head', 'wp_generator');

// Désactiver le protocole XML-RPC.
add_filter('xmlrpc_enabled', '__return_false');

// Retirer les métadonnées WordPress inutiles du <head>.
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'feed_links', 2);
remove_action('wp_head', 'feed_links_extra', 3);
remove_action('wp_head', 'index_rel_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'start_post_rel_link', 10, 0);
remove_action('wp_head', 'parent_post_rel_link', 10, 0);
remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0);
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');

// ==============================================
// Sécurisation de la connexion
// ==============================================

add_action('login_init', 'secure_wp_login_access');

/**
 * Bloque l'accès direct à wp-login.php hors cas autorisés.
 */
function secure_wp_login_access() {

    $allowed_actions = array(
        'rp',
        'resetpass',
        'lostpassword',
        'postpass'
    );

    $current_action = $_REQUEST['action'] ?? 'login';

    // Autoriser actions WordPress standard
    if (in_array($current_action, $allowed_actions, true)) {
        return;
    }

    $token = $_COOKIE['ovs-login-token'] ?? '';

    if ($token && get_transient('ovs_login_token_' . $token)) {
        return;
    }

    global $wp_query;

    $wp_query->set_404();
    status_header(404);
    nocache_headers();

    include get_query_template('404');
    exit;
}

// ==============================================
// Gestion des erreurs
// ==============================================

/**
 * Retourne un message générique lors d'un échec de connexion.
 */

add_filter('login_errors', function($error) {
    $error_codes = [
        'invalid_username',
        'invalid_email', 
        'incorrect_password',
        'invalidcombo',
    ];

    global $errors;
    if (is_wp_error($errors)) {
        foreach ($error_codes as $code) {
            if ($errors->get_error_message($code)) {
                return __('Une erreur s\'est produite avec les identifiants fournis. Veuillez réessayer.', 'ovs');
            }
        }
    }

    return $error;
});

// ==============================================
// Désactivation de l'énumération des utilisateurs
// ==============================================

/**
 * Redirige les tentatives d'énumération des utilisateurs via les pages auteur.
 */
function redirect_user_enumeration_attempt() {
    if (is_user_admin()) {
        return;
    }

    if (is_author() || isset($_GET['author'])) {
        wp_redirect(home_url(), 301);
        exit;
    }
}
add_action('parse_request', 'redirect_user_enumeration_attempt');

/**
 * Bloque l'énumération des utilisateurs via l'API REST.
 */
function disable_user_enumeration_rest_api($response, $handler, $request) {
    if (is_user_logged_in() && current_user_can('edit_posts')) {
        return $response;
    }

    if (strpos($request->get_route(), '/wp/v2/users') !== false) {
        return new WP_Error(
            'rest_disabled',
            __('L\'énumération des utilisateurs est désactivée.', 'ovs'),
            array('status' => 403)
        );
    }
    return $response;
}
add_filter('rest_pre_dispatch', 'disable_user_enumeration_rest_api', 10, 3);


/**
 * Supprimer les auteurs dans les feeds
 */

add_action('do_feed_rss2_comments', '__return_false', 1);
add_action('do_feed_atom_comments', '__return_false', 1);


add_filter('the_author', function () {
    return '';
});

add_filter('the_author_display_name', function () {
    return '';
});

add_filter('get_the_author_display_name', function ($name) {
    return '';
});


// ==============================================
// Sécurité complémentaire
// ==============================================

/**
 * Supprime l'en-tête Pingback de la réponse HTTP.
 */
add_filter('wp_headers', function($headers) {
    unset($headers['X-Pingback']);
    return $headers;
});

add_filter('xmlrpc_methods', function($methods) {
    unset($methods['pingback.ping']);
    return $methods;
});

// Désactiver les mises à jour automatiques des fichiers de traduction.
add_filter('auto_update_translation', '__return_false');
