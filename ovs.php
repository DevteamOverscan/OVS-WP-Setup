<?php
/**
 * Plugin Name: OVS WP Setup
 * Description: Plugin de sécurité et d’optimisation pour WordPress
 * Plugin URI:  https://www.overscan.com/
 * Version:     1
 * Author:      Overscan
 * Author URI:  https://www.overscan.com/
 * Text Domain: ovs
 * License: GPL v3 or later
 * License URI: https://www.gnu.org/licenses/quick-guide-gplv3.html
 */

if (!defined('ABSPATH')) {
    exit;
}

final class Ovs
{
    /**
     * Plugin Version
     *
     * @since 1
     * @var string The plugin version.
     */
    public const VERSION = '1';


    /**
     * Minimum PHP Version
     *
     * @since 1.2.0
     * @var string Minimum PHP version required to run the plugin.
     */
    public const MINIMUM_PHP_VERSION = '7.4';

    /**
     * Constructor
     *
     * @since 1.0.0
     * @access public
     */
    public function __construct()
    {

        // Init Plugin
        define('OVS_SETUP_PATH', basename(dirname(__FILE__)));
        add_action('plugins_loaded', array( $this, 'init' ));
    }

    /**
     * Initialize the plugin
     *
     * Checks for basic plugin requirements, if one check fail don't continue,
     * if all check have passed include the plugin class.
     *
     * Fired by `plugins_loaded` action hook.
     *
     * @since 1.2.0
     * @access public
     */
    public function init()
    {

        // Check for required PHP version
        if (version_compare(PHP_VERSION, self::MINIMUM_PHP_VERSION, '<')) {
            add_action('admin_notices', array( $this, 'admin_notice_minimum_php_version' ));
            return;
        }

        // Once we get here, We have passed all validation checks so we can safely include our plugin
        require_once('plugin.php');
    }

    /**
     * Admin notice
     *
     * Warning when the site doesn't have a minimum required PHP version.
     *
     * @since 1.0.0
     * @access public
     */
    public function admin_notice_minimum_php_version()
    {
        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }

        $message = sprintf(
            /* translators: 1: Plugin name 2: PHP 3: Required PHP version */
            esc_html__('"%1$s" requires "%2$s" version %3$s or greater.', 'ovs'),
            '<strong>' . esc_html__('OVS', 'ovs') . '</strong>',
            '<strong>' . esc_html__('PHP', 'ovs') . '</strong>',
            self::MINIMUM_PHP_VERSION
        );

        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }
}

// Instantiate Ovs.
new Ovs();
