<?php

/**
 * Classe d'initialisation des fonctionnalités du plugin.
 *
 * @package OVS
 * @author Overscan
 * @link https://www.overscan.com
 * @since 1
 */

if (!defined('ABSPATH')) {
    exit;
}
class SetUp
{
    public function init()
    {
        // Détecter une installation Bedrock pour adapter les chemins utilisés.
        $wp_siteurl = getenv('WP_SITEURL');

        if (!empty($wp_siteurl) && strpos($wp_siteurl, '/wp') !== false) {
            define('WP_STACK', 'bedrock');
            define('WP_CONTENT_FOLDERNAME', 'app');
        } else {
            define('WP_CONTENT_FOLDERNAME', 'wp-content');
        }

        add_action('login_enqueue_scripts', array($this, 'my_login'));

        // Désactiver les mises à jour automatiques des thèmes et extensions.
        add_filter('auto_update_theme', '__return_false');
        add_filter('auto_update_plugin', '__return_false');

        // Empêcher l'impression du site depuis le navigateur.
        add_action('wp_head', function () {
            echo '<style> @media print { body { display:none } } </style>';
        });

        // Autoriser l'envoi de fichiers SVG.
        add_filter('upload_mimes', array($this, 'add_file_types_to_uploads'));

        // Planifier le nettoyage quotidien des sessions WooCommerce.
        if (!wp_next_scheduled('cron_wc_clean_cart')) {
            wp_schedule_event(time(), 'daily', 'cron_wc_clean_cart');
        }

        add_action('cron_wc_clean_cart', array($this, 'wc_clean_session_cart'));
        $this->loadFeatures();
    }

    /**
     * Charge les styles personnalisés de la page de connexion.
     */
    public function my_login()
    {
        wp_enqueue_style('ovs-login', plugin_dir_url(__DIR__) . '/assets/css/login.css');
    }

    /**
     * Ajoute la prise en charge des fichiers SVG dans les médias.
     */
    public function add_file_types_to_uploads($file_types)
    {
        $new_filetypes = array();
        $new_filetypes['svg'] = 'image/svg+xml';
        $file_types = array_merge($file_types, $new_filetypes);
        return $file_types;
    }

    /**
     * Supprime les sessions WooCommerce expirées et vide le cache associé.
     */
    public function wc_clean_session_cart()
    {
        global $wpdb;

        $wpdb->query("TRUNCATE {$wpdb->prefix}woocommerce_sessions");
        $wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE meta_key='_woocommerce_persistent_cart_" . get_current_blog_id() . "';");
        wp_cache_flush();
    }

    /**
     * Charge l'ensemble des fonctionnalités activées par le plugin.
     */
    private function loadFeatures()
    {
        update_option('features', array(
            'duplicate-post-page',
            'security-force-brut',
            'security-wp',
            'remove-comments',
            'tarte-au-citron'
        ));

        foreach (get_option('features') as $file) {
            $filepath = dirname(__FILE__) . '/functions/' . $file . '.php';
            if (file_exists($filepath)) {
                require_once $filepath;
            } else {
                trigger_error("Error locating `$filepath` for inclusion!", E_USER_ERROR);
            }
        }
    }

}
