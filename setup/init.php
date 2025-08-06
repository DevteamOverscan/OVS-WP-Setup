<?php

/**
 * Class Setup
 * @package OVS
 * @author Clément Vacheron
 * @link https://www.overscan.com
 * Main Plugin class
 * @since 1
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
class SetUp
{
    public function init()
    {
        // --------------------------------------------- //
        // --  Vérifier si la stack est Bedrock  -- //
        // --------------------------------------------- //
        $wp_siteurl = getenv('WP_SITEURL');

        if (!empty($wp_siteurl) && strpos($wp_siteurl, '/wp') !== false) {
            define('WP_STACK', 'bedrock');
            define('WP_CONTENT_FOLDERNAME', 'app');
        }


        add_action('login_enqueue_scripts', array($this,'my_login'));

        // --------------------------------------------- //
        // --  Désactive les MAJ auto plugins/themes  -- //
        // --------------------------------------------- //
        add_filter('auto_update_theme', '__return_false');
        add_filter('auto_update_plugin', '__return_false');

        // ---------------------------------------------------- //
        // --  Désactive la possibilité d'imprimer une page  -- //
        // ---------------------------------------------------- //
        add_action('wp_head', function () {
            echo '<style> @media print { body { display:none } } </style>';
        });
        // ----------------------------------------- //
        // -- Ajout possibilité de charger du svg -- //
        // ----------------------------------------- //
        add_filter('upload_mimes', array($this,'add_file_types_to_uploads'));

        // --------------------------------------------------------------//
        // --  Nettoie la table dans la base de donné de woocommerce  -- //
        // ------------------------------------------------------------- //
        if (!wp_next_scheduled('cron_wc_clean_cart')) {
            wp_schedule_event(time(), 'daily', 'cron_wc_clean_cart');
        }

        add_action('cron_wc_clean_cart', array($this,'wc_clean_session_cart'));
        $this->loadFeatures();

    }

    // --------------------------------------------------------------//
    // --  Personnalisation de la page Connexion  -- //
    // ------------------------------------------------------------- //

    public function my_login()
    {
        wp_enqueue_style('ovs-login', plugin_dir_url(__DIR__) . '/assets/css/login.css');
    }

    // ----------------------------------------- //
    // -- Ajout possibilité de charger du svg -- //
    // ----------------------------------------- //
    public function add_file_types_to_uploads($file_types)
    {
        $new_filetypes = array();
        $new_filetypes['svg'] = 'image/svg+xml';
        $file_types = array_merge($file_types, $new_filetypes);
        return $file_types;
    }

    // --------------------------------------------------------------//
    // --  Nettoie la table dans la base de donné de woocommerce  -- //
    // ------------------------------------------------------------- //

    public function wc_clean_session_cart()
    {
        global  $wpdb;

        $wpdb->query("TRUNCATE {$wpdb->prefix}woocommerce_sessions");
        $wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE meta_key='_woocommerce_persistent_cart_" . get_current_blog_id() . "';");
        wp_cache_flush();
    }

    // --------------------------------------------------------------//
    // --  Activation des fonctionnalitées                          -- //
    // ------------------------------------------------------------- //

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
