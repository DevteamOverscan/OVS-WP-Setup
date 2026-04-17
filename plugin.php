<?php

/**
 * Classe principale du plugin.
 *
 * @package OVS
 * @author Overscan
 * @link https://www.overscan.com
 * @since 1
 */

if (!defined('ABSPATH')) {
    exit;
}

class Plugin
{
    /**
     * Instance unique du plugin.
     *
     * @since 1
     * @access private
     * @static
     *
     * @var Plugin
     */
    private static $_instance = null;

    /**
     * Retourne l'instance unique du plugin.
     *
     * @since 1
     * @access public
     *
     * @return Plugin
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }


    /**
     * Initialise le plugin et ses hooks principaux.
     *
     * @since 1.2.0
     * @access public
     */
    public function __construct()
    {

        register_deactivation_hook(__FILE__, array($this, 'plugin_deactivation'));
        add_action('admin_enqueue_scripts', array($this, 'admin_include_script'), 11);

        $this->load_files();
        $setup = new SetUp();
        $setup->init();
        if (!get_option('ovs_activated', false)) {
            register_activation_hook(__FILE__, array($this, 'plugin_activation'));
            update_option('ovs_activated', true);
        }

    }

    /**
     * Charge les scripts et styles utilisés dans l'administration.
     */
    public function admin_include_script()
    {
        if (!did_action('wp_enqueue_media')) {
            wp_enqueue_media();
        }

        // Charger les scripts JavaScript de l'administration.
        wp_enqueue_script('admin-ovs', plugin_dir_url(__FILE__) . '/assets/js/admin.js', null, false, true);
        wp_enqueue_script('alert-ovs', plugin_dir_url(__FILE__) . '/assets/js/alert.js', null, false, true);

        // Charger les feuilles de style de l'administration.
        wp_enqueue_style('admin-icon', plugin_dir_url(__FILE__) . '/assets/pictofont/style.css', false, '1.0.0');
        wp_enqueue_style('admin-ovs', plugin_dir_url(__FILE__) . '/assets/css/admin.css', false, '1.0.0');
    }

    /**
     * Exécute les actions prévues à l'activation du plugin.
     */
    public function plugin_activation()
    {
        $setup = new SetUp();
        wp_logout();
        exit;

    }

    /**
     * Supprime les fichiers déposés à la racine lors de la désactivation du plugin.
     */
    public function plugin_deactivation()
    {
        $files = array(
            'ovs-connect.php',
        );
        foreach ($files as $f) {
            $destination = ABSPATH . $f;
            if (file_exists($destination)) {
                unlink($destination);
            }
        }
    }

    /**
     * Charge les fichiers d'initialisation du plugin.
     */
    private function load_files()
    {
        $roots_includes = array(
            'setup',
        );
        $pluginPath = __DIR__;

        if (is_dir($pluginPath)) {
            foreach ($roots_includes as $file) {
                $filepath = $pluginPath . '/' . $file . '/init.php';
                if (file_exists($filepath)) {
                    require_once $filepath;
                } else {
                    error_log("Avertissement: Fichier `$filepath` introuvable pour inclusion!");
                }
            }
        } else {
            error_log("Warning: Directory `$pluginPath` does not exist!");
        }
    }

}

Plugin::instance();
