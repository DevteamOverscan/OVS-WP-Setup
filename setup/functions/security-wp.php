<?php
/**
 *
 * @package OVS
 * @author Overscan
 * @link https://www.overscan.com
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// ----------------------------------------- //
// --         Remove version WP           -- //
// ----------------------------------------- //
remove_action('wp_head', 'wp_generator');

// ----------------------------------------- //
// --    Security - Disable XML-RCP       -- //
add_filter('xmlrpc_enabled', '__return_false');
// ----------------------------------------- //
// add_filter('rest_jsonp_enabled', '__return_false');

// ------------------------------------------------- //
// --  Security - Disable ALL infos Wordpress     -- //
// ------------------------------------------------- //

remove_action('wp_head', 'rsd_link'); // remove really simple discovery link
remove_action('wp_head', 'wp_generator'); // remove wordpress version

remove_action('wp_head', 'feed_links', 2); // remove rss feed links (make sure you add them in yourself if youre using feedblitz or an rss service)
remove_action('wp_head', 'feed_links_extra', 3); // removes all extra rss feed links

remove_action('wp_head', 'index_rel_link'); // remove link to index page
remove_action('wp_head', 'wlwmanifest_link'); // remove wlwmanifest.xml (needed to support windows live writer)

remove_action('wp_head', 'start_post_rel_link', 10, 0); // remove random post link
remove_action('wp_head', 'parent_post_rel_link', 10, 0); // remove parent post link
remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0); // remove the next and previous post links
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);

remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');

remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0); // Remove shortlink


// ==============================================
// -- Sécurisation de l'accès à wp-login.php --
// ==============================================

add_action('login_init', 'secure_wp_login_access');

function secure_wp_login_access() {
    // Whitelist des actions autorisées sans passage par ovs-connect.php
    $allowed_actions = array(
        'rp',           // Reset password (lien email)
        'resetpass',    // Formulaire de reset
        'lostpassword', // Mot de passe perdu
        'postpass'      // Password pour post privé
    );
    
    $current_action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'login';
    
    // Cas autorisés :
    if (in_array($current_action, $allowed_actions) 
        || isset($_COOKIE['ovs-login-key']) 
        || is_user_logged_in()) {
        return;
    }
    
    // Cas bloqués :
    // Tentative d'accès direct à la page de login
    if ($current_action === 'login' || empty($current_action)) {
        error_log('Tentative d acces direct a wp-login.php depuis IP: ' . $_SERVER['REMOTE_ADDR']);
        wp_redirect(home_url('/404'));
        exit;
    }
}

// ==============================================
// -- Sécurisation de l'accès à wp-admin --
// ==============================================

add_action('admin_init', 'secure_wp_admin_access');

function secure_wp_admin_access() {
        // Ne jamais bloquer admin-ajax.php
    if (strpos($_SERVER['REQUEST_URI'], 'admin-ajax.php') !== false) {
        return;
    }
    
    // Ne jamais bloquer wp-cron.php
    if (strpos($_SERVER['REQUEST_URI'], 'wp-cron.php') !== false) {
        return;
    }
    
    // Ne jamais bloquer l'API REST
    if (strpos($_SERVER['REQUEST_URI'], 'wp-json') !== false) {
        return;
    }

    // Ne bloquer que si l'utilisateur n'est pas connecté
    // et n'a pas le cookie ovs-login-key (donc n'est pas passé par ovs-connect.php)
    if (!is_user_logged_in() && !isset($_COOKIE['ovs-login-key'])) {
        wp_redirect(home_url('/404'));
        exit;
    }
}

// Rendu de la page forbidden
function custom_error_pages()
{
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

// ------ //

// ------------------------------------------------ //
// --     Désactive l'énumération des comptes    -- //
// ------------------------------------------------ //

// Rediriger les requêtes d'énumération des utilisateurs vers la page d'accueil
function redirect_user_enumeration_attempt()
{

    if (is_user_admin()) {
        return; // Ne pas rediriger les utilisateurs administrateurs
    }

    if (is_author() || isset($_GET['author'])) {
        wp_redirect(home_url(), 301);
        exit;
    }

}
add_action('parse_request', 'redirect_user_enumeration_attempt');

function disable_user_enumeration_rest_api($response, $handler, $request)
{
    // Ne bloque pas les utilisateurs connectés ayant des droits d'édition
    if (is_user_logged_in() && current_user_can('edit_posts')) {
        return $response;
    }

    if (strpos($request->get_route(), '/wp/v2/users') !== false) {
        return new WP_Error(
            'rest_disabled',
            __('L\'énumération des utilisateurs est désactivée.'),
            array('status' => 403)
        );
    }
    return $response;
}
add_filter('rest_pre_dispatch', 'disable_user_enumeration_rest_api', 10, 3);


//  Disable pingback.ping xmlrpc method to prevent WordPress from participating in DDoS attacks

// remove x-pingback HTTP header
add_filter('wp_headers', function($headers) {
    unset($headers['X-Pingback']);
    return $headers;
});
// disable pingbacks
add_filter( 'xmlrpc_methods', function( $methods ) {
        unset( $methods['pingback.ping'] );
        return $methods;
});
add_filter( 'auto_update_translation', '__return_false' );