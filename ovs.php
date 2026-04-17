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
     * Version du plugin.
     *
     * @since 1
     * @var string
     */
    public const VERSION = '1';


    /**
     * Version minimale de PHP requise.
     *
     * @since 1.2.0
     * @var string Minimum PHP version required to run the plugin.
     */
    public const MINIMUM_PHP_VERSION = '7.4';

    /**
     * Enregistre l'initialisation du plugin.
     *
     * @since 1.0.0
     * @access public
     */
    public function __construct()
    {
        // Définir le dossier du plugin pour les inclusions internes.
        define('OVS_SETUP_PATH', basename(dirname(__FILE__)));
        add_action('plugins_loaded', array( $this, 'init' ));
    }

    /**
     * Initialise le plugin après vérification des prérequis.
     *
     * Vérifie des prérequis de base pour initialiser le plugin.
     *
     * @since 1.2.0
     * @access public
     */
    public function init()
    {
        // Vérifier la version minimale de PHP avant de charger le plugin.
        if (version_compare(PHP_VERSION, self::MINIMUM_PHP_VERSION, '<')) {
            add_action('admin_notices', array( $this, 'admin_notice_minimum_php_version' ));
            return;
        }

        require_once('plugin.php');
    }

    /**
     * Affiche un message d'alerte si la version de PHP est insuffisante.
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
            esc_html__('"%1$s" nécessite "%2$s" version %3$s ou supérieure.', 'ovs'),
            '<strong>' . esc_html__('OVS', 'ovs') . '</strong>',
            '<strong>' . esc_html__('PHP', 'ovs') . '</strong>',
            self::MINIMUM_PHP_VERSION
        );

        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }
}

new Ovs();
