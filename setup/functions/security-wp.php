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

// ----------------------------------------- //
// --         Remove version WP           -- //
// ----------------------------------------- //
remove_action('wp_head', 'wp_generator');

// ----------------------------------------- //
// --    Security - Disable XML-RCP       -- //
// ----------------------------------------- //
add_filter('xmlrpc_enabled', '__return_false');
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

// --------------------------------------- //
// --     Change l'access à l'admin     -- //
// --------------------------------------- //
//Modification de l'adresse de déconnexion
add_filter('logout_url', 'custom_logout_url', 10, 2);
function custom_logout_url($logout_url, $redirect)
{
    return wp_nonce_url(home_url('/ovs-authentification.php?action=logout'), 'log-out');
}

//Modification de l'adresse de connexion
add_filter('login_url', 'custom_login_url', 10, 3);
function custom_login_url($login_url, $redirect, $force_reauth)
{
    return str_replace('wp-login', 'ovs-authentification', $login_url);
}

//Modification du lien du reset password
add_filter('lostpassword_url', 'my_lostpassword_url', 10, 0);
function my_lostpassword_url()
{
    $auth = get_option('ovs_auth', false);

    if (isset($_COOKIE['ovs-key']) && $_COOKIE['ovs-key'] !== $auth) {
        exit();
    } elseif (!isset($_COOKIE['ovs-key'])) {
        setcookie("ovs-key", $auth, strtotime("+1 week"), '/');
    }

    return site_url('/ovs-authentification.php?action=lostpassword');
}

//Modification du lien du reset password dans le mail envoyé à l'utilisateur
add_filter("retrieve_password_message", "mapp_custom_password_reset", 99, 4);
function mapp_custom_password_reset($message, $key, $user_login, $user_data)
{
    /* translators: %s: User login. */
    $message  = sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
    $message .= __('To set your password, visit the following address:') . "\r\n\r\n";
    $message .= network_site_url("ovs-authentification.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login') . "\r\n\r\n";

    $message .= wp_login_url() . "\r\n";

    return $message;
}

// Supprimer le cookie ovs-key lors de la déconnexion
function custom_woocommerce_logout()
{
    if (isset($_COOKIE['ovs-key'])) {
        setcookie('ovs-key', '', time() - 3600, '/', COOKIE_DOMAIN); // Détruire le cookie
    }
    wp_logout(); // Déconnecter l'utilisateur
    wp_redirect(home_url()); // Redirection vers la page d'accueil après déconnexion
    exit;
}
add_action('wp_logout', 'custom_woocommerce_logout');

// Vérifier le nonce et gérer la redirection personnalisée
function custom_logout_redirect()
{
    if (!is_user_logged_in() && isset($_GET['action']) && $_GET['action'] == 'logout') {
        if (wp_verify_nonce($_GET['_wpnonce'], 'log-out')) {
            wp_redirect(home_url()); // Redirection vers la page d'accueil
        } else {
            wp_redirect(home_url('/access-denied')); // Redirection en cas de nonce invalide
        }
        exit;
    }
}
add_action('template_redirect', 'custom_logout_redirect');

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

// ------------------------------------------------ //
// --     Désactive l'énumération des comptes    -- //
// ------------------------------------------------ //

// Rediriger les requêtes d'énumération des utilisateurs vers la page d'accueil
function redirect_user_enumeration_attempt()
{
    if (is_user_admin()) {
        return; // Ne pas rediriger les utilisateurs administrateurs
    }

    if (preg_match('/\?author=([0-9]*)/', $_SERVER['REQUEST_URI'])) {
        wp_redirect(home_url(), 301);
        exit();
    }
}
add_action('template_redirect', 'redirect_user_enumeration_attempt');

// Désactiver les en-têtes d'erreur de l'API REST pour les requêtes utilisateur
function disable_user_enumeration_rest_api($response, $handler, $request)
{
    if (strpos($request->get_route(), '/wp/v2/users') !== false) {
        $response = new WP_Error(
            'rest_disabled',
            __('L\'énumération des utilisateurs est désactivée.'),
            array('status' => 403)
        );
    }
    return $response;
}
add_filter('rest_pre_dispatch', 'disable_user_enumeration_rest_api', 10, 3);
