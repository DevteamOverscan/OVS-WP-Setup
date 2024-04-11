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
            echo '<style type="text/css"> @media print { body { display:none } } </style>';
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
    public function hide_login()
    {
        // Ajouter le fichier à la racine du site lors de l'activation
        $source = plugin_dir_path(__FILE__) . 'login/';
        $files = array('ovs-connect.php','ovs-authentification.php');
        foreach($files as $f) {
            $source_file = $source . $f;
            $destination = ABSPATH . $f;
            if(!file_exists($source_file)) {
                echo "<p style='background-color:#ffcc00;color:#333;font-weight:700;padding:1rem;'>Le fichier source $source_file n'existe pas.</p>";
                continue;
            }
            if (!file_exists($destination) && file_exists($source_file)) {
                copy($source_file, $destination);
            }
            if(file_exists(ABSPATH . 'xmlrpc.php')) {
                unlink(ABSPATH . 'xmlrpc.php');
            }
            if(file_exists(ABSPATH . 'wp-login.php')) {
                unlink(ABSPATH . 'wp-login.php');
            }
        }

        if(file_exists(plugin_dir_path(__FILE__) . 'redirect-login.php')) {
            require_once plugin_dir_path(__FILE__) . 'redirect-login.php';
            unlink(plugin_dir_path(__FILE__) . 'redirect-login.php');
        }

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
        if(!get_option('features', false)) {
            update_option('features', array(
                'duplicate-post-page',
            'security-force-brut',
            'security-wp',
            'perform',
            ));
        }
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
