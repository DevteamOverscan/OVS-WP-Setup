<?php

/**
 * Class Plugin
 * @package OVS
 * @author Clément Vacheron
 * @link https://www.overscan.com
 * Main Plugin class
 * @since 1
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Plugin
{
    /**
     * Instance
     *
     * @since 1
     * @access private
     * @static
     *
     * @var Plugin The single instance of the class.
     */
    private static $_instance = null;

    /**
     * Instance
     *
     * Ensures only one instance of the class is loaded or can be loaded.
     *
     * @since 1
     * @access public
     *
     * @return Plugin An instance of the class.
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }


    /**
     *  Plugin class constructor
     *
     * Register plugin action hooks and filters
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
            // $this->plugin_activation();
            update_option('ovs_activated', true);
        }

    }

    public function admin_include_script()
    {
        if (!did_action('wp_enqueue_media')) {
            wp_enqueue_media();
        }
        // Enqueue scripts and styles
        // JS

        wp_enqueue_script('admin-setup', plugin_dir_url(__FILE__) . '/assets/js/install-process.js', null, false, true);
        wp_enqueue_script('admin-ovs', plugin_dir_url(__FILE__) . '/assets/js/admin.js', null, false, true);
        //CSS
        wp_enqueue_style('admin-icon', plugin_dir_url(__FILE__) . '/assets/pictofont/style.css', false, '1.0.0');
        wp_enqueue_style('admin-form', plugin_dir_url(__FILE__) . '/assets/css/admin-form.css', false, '1.0.0');
        wp_enqueue_style('admin-ovs', plugin_dir_url(__FILE__) . '/assets/css/admin.css', false, '1.0.0');
    }

    public function plugin_activation()
    {
        $setup = new SetUp();
        $setup->hide_login();
        wp_logout();
        exit;

    }

    public function plugin_deactivation()
    {
        $files = array(
            'ovs-connect.php',
            'ovs-authentification.php',
        );
        foreach ($files as $f) {
            $destination = ABSPATH . $f;
            if (file_exists($destination)) {
                unlink($destination);
            }
        }
    }

    private function load_files()
    {
        $roots_includes = array(
            'setup',
            'install',
        );
        $pluginPath = __DIR__;

        if (is_dir($pluginPath)) {
            foreach ($roots_includes as $file) {
                $filepath = $pluginPath . '/' . $file . '/init.php';
                if (file_exists($filepath)) {
                    require_once $filepath;
                } else {
                    trigger_error("Error locating `$filepath` for inclusion!", E_USER_ERROR);
                }
            }
        }
    }
}

// Instantiate Plugin Class
Plugin::instance();
