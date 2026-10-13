<?php
/**
 * Intègre tarteaucitron.io Pro.
 *
 * Le code d'installation fourni par tarteaucitron.io est collé dans
 * Réglages > Cookies (tarteaucitron) et injecté en tout premier dans le <head>,
 *
 * Champ vide = désactivé. Quand il est rempli, la version gratuite n'est pas chargée.
 *
 * @package OVS
 * @author Overscan
 * @link https://www.overscan.com
 */

if (!defined('ABSPATH')) {
    exit;
}

const OVS_TAC_PRO_OPTION = 'ovs_tarteaucitron_pro_code';

/**
 * Indique si tarteaucitron Pro est configuré.
 */
function ovs_tac_pro_is_active(): bool
{
    return trim((string) get_option(OVS_TAC_PRO_OPTION, '')) !== '';
}

// Front : priorité très basse pour sortir avant GTM4WP et tout autre script.
add_action('wp_head', function () {
    if (ovs_tac_pro_is_active()) {
        echo get_option(OVS_TAC_PRO_OPTION) . "\n";
    }
}, -1000);

// Back-office : Réglages > Cookies (tarteaucitron).
add_action('admin_init', function () {
    register_setting('ovs-tarteaucitron', OVS_TAC_PRO_OPTION, array(
        'type'              => 'string',
        'sanitize_callback' => function ($value) {
            // Seuls les utilisateurs autorisés à publier du HTML libre peuvent enregistrer un script.
            return current_user_can('unfiltered_html') ? trim((string) $value) : get_option(OVS_TAC_PRO_OPTION, '');
        },
        'default'           => '',
    ));
});

add_action('admin_menu', function () {
    add_options_page('Cookies – tarteaucitron Pro', 'Cookies (tarteaucitron)', 'unfiltered_html', 'ovs-tarteaucitron', function () {
        ?>
        <div class="wrap">
            <h1>Cookies – tarteaucitron Pro</h1>
            <form action="options.php" method="post">
                <?php settings_fields('ovs-tarteaucitron'); ?>
                <p><label for="ovs-tac-code">Code d'installation fourni par tarteaucitron.io (menu ⋮ &gt; « Installer &lt;&gt; »). Laisser vide pour désactiver.</label></p>
                <textarea id="ovs-tac-code" name="<?php echo esc_attr(OVS_TAC_PRO_OPTION); ?>" class="large-text code" rows="6"><?php echo esc_textarea(get_option(OVS_TAC_PRO_OPTION, '')); ?></textarea>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    });
});
