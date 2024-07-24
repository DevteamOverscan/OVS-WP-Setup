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

function display_install_modal()
{
    if (is_admin() && !get_option('install', false) && !wp_doing_ajax()) {
        ?>
        <div class="install-modal">
            <div class="wrapper">
                <h1>Installation des éléments de base</h1>
                <p>Avant de poursuivre sur l'interface d'administration de WordPress, veuillez installer les éléments nécessaires au bon fonctionnement du site.</p>
                <div class="action">
                    <a href="<?php echo wp_logout_url(); ?>" class="annule"><?php _e('Annuler', 'ovs'); ?></a>
                    <button type="button" class="install"><?php _e('Continuer', 'ovs'); ?></button>
                </div>
            </div>
        </div>
        <?php
    }
}
add_action('admin_notices', 'display_install_modal');
?>
