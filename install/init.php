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

function setup_ajax()
{
    if (is_dir(dirname(__FILE__))) {
        require_once dirname(__FILE__) . '/install.php';
        add_action('wp_ajax_install_ajax', 'install');
        add_action('wp_ajax_nopriv_install_ajax', 'install');
    } else {
        remove_action('wp_ajax_install_ajax', 'install');
        remove_action('wp_ajax_nopriv_install_ajax', 'install');
    }
}
setup_ajax();
// --------------------------------------------------------------//
// --  Installation des fichiers de base  -- //
// ------------------------------------------------------------- //

if(is_admin() && !get_option('install', false) && !wp_doing_ajax()) {
    echo '<div class="install-modal">';
    echo '<div class="wrapper">';
    echo '<h1>Installation des éléments de base</h1>';
    echo '<p>Avant de poursuivre sur l\'interface d\'administration de Wordpress veuillez installer les éléments nécessaire au bon fonctionnement du site</p>';
    echo '<div class="action">';
    echo '<a href="' . wp_logout_url() . '" class="annule">' . __('Annuler', 'ovs') . '</a>';
    echo '<button type="button" class="install">' . __('Continuer', 'ovs') . '</button>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
