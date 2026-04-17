<?php
/**
 * Désactive l'ensemble des fonctionnalités liées aux commentaires.
 *
 * @package OVS
 * @author Overscan
 * @link https://www.overscan.com
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_init', function () {
    // Rediriger toute tentative d'accès à l'écran des commentaires.
    global $pagenow;

    if ($pagenow === 'edit-comments.php') {
        wp_redirect(admin_url());
        exit;
    }

    // Supprimer le bloc des commentaires récents du tableau de bord.
    remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');

    // Retirer les commentaires et trackbacks de tous les types de contenus compatibles.
    foreach (get_post_types() as $post_type) {
        if (post_type_supports($post_type, 'comments')) {
            remove_post_type_support($post_type, 'comments');
            remove_post_type_support($post_type, 'trackbacks');
        }
    }
});

// Fermer les commentaires et pingbacks côté front-office.
add_filter('comments_open', '__return_false', 20, 2);
add_filter('pings_open', '__return_false', 20, 2);

// Masquer les commentaires existants dans le front-office.
add_filter('comments_array', '__return_empty_array', 10, 2);

// Retirer l'entrée Commentaires du menu d'administration.
add_action('admin_menu', function () {
    remove_menu_page('edit-comments.php');
});

// Retirer l'accès aux commentaires depuis la barre d'administration.
add_action('init', function () {
    if (is_admin_bar_showing()) {
        remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
    }
});
