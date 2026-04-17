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
    // Autoriser certaines actions sans authentification préalable.
    $allowed_actions = array(
        'rp',           // Réinitialisation du mot de passe.
        'resetpass',    // Formulaire de réinitialisation du mot de passe.
        'lostpassword', // Demande de mot de passe perdu.
        'postpass'      // Accès à un contenu protégé par mot de passe.
    );

    $current_action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'login';

    // Laisser passer les actions autorisées, les utilisateurs connectés et les visiteurs disposant du cookie.
    if (in_array($current_action, $allowed_actions)
        || isset($_COOKIE['ovs-login-key'])
        || is_user_logged_in()) {
        return;
    }

    // Bloquer les accès directs non autorisés à la page de connexion.
    if ($current_action === 'login' || empty($current_action)) {
        error_log('Tentative accès direct wp-login.php : ' . $_SERVER['REMOTE_ADDR']);
        wp_redirect(home_url('/404'));
        exit;
    }
}

// ==============================================
// Sécurisation de l'accèes au Back Office
// ==============================================

add_action('admin_init', 'secure_wp_admin_access');

/**
 * Restreint l'accès à wp-admin aux utilisateurs autorisés.
 */
function secure_wp_admin_access() {
    // Autoriser les requêtes AJAX WordPress (admin-ajax.php).
    if (strpos($_SERVER['REQUEST_URI'], 'admin-ajax.php') !== false) {
        return;
    }

    // Autoriser l'exécution du cron WordPress (wp-cron.php).
    if (strpos($_SERVER['REQUEST_URI'], 'wp-cron.php') !== false) {
        return;
    }

    // Autoriser les appels à l'API REST.
    if (strpos($_SERVER['REQUEST_URI'], 'wp-json') !== false) {
        return;
    }

    // Bloquer l'accès à l'administration sans session valide ni cookie attendu.
    if (!is_user_logged_in() && !isset($_COOKIE['ovs-login-key'])) {
        wp_redirect(home_url('/404'));
        exit;
    }
}

// ==============================================
// Gestion des erreurs
// ==============================================

/**
 * Affiche une page dédiée pour les erreurs 403.
 */
function custom_error_pages() {
    global $wp_query;

    if (isset($_REQUEST['status']) && $_REQUEST['status'] == 403) {
        $wp_query->is_404 = false;
        $wp_query->is_page = true;
        $wp_query->is_singular = true;
        $wp_query->is_single = false;
        $wp_query->is_home = false;
        $wp_query->is_archive = false;
        $wp_query->is_category = false;
        status_header(403);
        unset($_COOKIE['wp-connex']);
        get_template_part('403');
        exit;
    }
}
add_action('wp', 'custom_error_pages');

/**
 * Retourne un message générique lors d'un échec de connexion.
 */
add_filter('login_errors', function() {
    return __('Une erreur s\'est produite avec les identifiants fournis. Veuillez réessayer.', 'ovs');
});

// ==============================================
// Désactivation de l'énumération des utilisateurs
// ==============================================

/**
 * Redirige les tentatives d'énumération des utilisateurs via les pages auteur.
 */
function redirect_user_enumeration_attempt() {
    // Ne pas appliquer cette redirection dans l'administration.
    if (is_user_admin()) {
        return;
    }

    // Rediriger les pages auteur et les accès utilisant le paramètre author.
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
    // Autoriser les utilisateurs connectés disposant des droits d'édition.
    if (is_user_logged_in() && current_user_can('edit_posts')) {
        return $response;
    }

    // Refuser l'accès aux routes exposant la liste des utilisateurs.
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
