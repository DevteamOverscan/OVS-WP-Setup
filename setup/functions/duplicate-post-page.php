<?php

/**
 * Ajoute la duplication d'articles et de pages dans l'administration.
 *
 * @package OVS
 * @author Overscan
 * @link https://www.overscan.com
 */
if (!defined('ABSPATH')) {
    exit; /* Sortie si accès direct. */
}

/**
 * Duplique un contenu WordPress en brouillon.
 */
function rd_duplicate_post_as_draft()
{
    global $wpdb;

    if (! (isset($_GET['post']) || isset($_POST['post']) || (isset($_REQUEST['action']) && 'rd_duplicate_post_as_draft' == $_REQUEST['action']))) {
        wp_die('Aucun article à dupliquer !');
    }

    // Vérifier le nonce de sécurité avant de lancer la duplication.
    if (!isset($_GET['duplicate_nonce']) || !wp_verify_nonce($_GET['duplicate_nonce'], basename(__FILE__))) {
        return;
    }

    // Récupérer l'identifiant du contenu d'origine.
    $post_id = (isset($_GET['post']) ? absint($_GET['post']) : absint($_POST['post']));

    // Charger les données du contenu à dupliquer.
    $post = get_post($post_id);

    // Attribuer le brouillon au compte actuellement connecté.
    $current_user = wp_get_current_user();
    $new_post_author = $current_user->ID;

    // Créer le duplicata uniquement si le contenu source existe.
    if (isset($post) && $post != null) {
        // Préparer les données du nouveau brouillon.
        $args = array(
            'comment_status' => $post->comment_status,
            'ping_status' => $post->ping_status,
            'post_author' => $new_post_author,
            'post_content' => $post->post_content,
            'post_excerpt' => $post->post_excerpt,
            'post_name' => $post->post_name,
            'post_parent' => $post->post_parent,
            'post_password' => $post->post_password,
            'post_status' => 'draft',
            'post_title' => $post->post_title,
            'post_type' => $post->post_type,
            'to_ping' => $post->to_ping,
            'menu_order' => $post->menu_order,
        );

        // Insérer le nouveau brouillon dans WordPress.
        $new_post_id = wp_insert_post($args);

        // Reproduire les taxonomies du contenu d'origine.
        $taxonomies = get_object_taxonomies($post->post_type);
        foreach ($taxonomies as $taxonomy) {
            $post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
            wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
        }

        // Copier l'ensemble des métadonnées du contenu d'origine.
        $post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");
        if (count($post_meta_infos) != 0) {
            $sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
            foreach ($post_meta_infos as $meta_info) {
                $meta_key = $meta_info->meta_key;
                if ($meta_key == '_wp_old_slug') {
                    continue;
                }
                $meta_value = addslashes($meta_info->meta_value);
                $sql_query_sel[] = "SELECT $new_post_id, '$meta_key', '$meta_value'";
            }
            $sql_query .= implode(" UNION ALL ", $sql_query_sel);
            $wpdb->query($sql_query);
        }

        // Rediriger vers l'écran d'édition du nouveau brouillon.
        wp_redirect(admin_url('post.php?action=edit&post=' . $new_post_id));
        exit;
    } else {
        wp_die('La duplication de l’article a échoué : article introuvable.');
    }
}
add_action('admin_action_rd_duplicate_post_as_draft', 'rd_duplicate_post_as_draft');

/**
 * Ajoute le lien de duplication dans la liste des contenus.
 */
function rd_duplicate_post_link($actions, $post)
{
    if (current_user_can('edit_posts')) {
        $actions['duplicate'] = '<a href="' . wp_nonce_url('admin.php?action=rd_duplicate_post_as_draft&post=' . $post->ID, basename(__FILE__), 'duplicate_nonce') . '" title="Dupliquer cet élément" rel="permalink">Dupliquer</a>';
    }
    return $actions;
}

add_filter('post_row_actions', 'rd_duplicate_post_link', 10, 2);
add_filter('page_row_actions', 'rd_duplicate_post_link', 10, 2);
