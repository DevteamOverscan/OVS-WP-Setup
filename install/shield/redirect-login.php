<?php

function redirectLoginURL()
{
    if (! function_exists('wp_insert_htaccess_rules')) {
        require_once ABSPATH . '/wp-admin/includes/misc.php';
    }
    $str = rand();
    $result = hash("sha256", $str);
    if(!get_option('ovs_auth', false)) {
        $rules = array('<IfModule mod_rewrite.c>',
            'RewriteEngine On',
            'RewriteBase /',
            '# Bloquer l\'accès à wp-login.php',
            'RewriteRule wp-login.php - [F]',
            '# Réécrire ovs-connect vers ovs-connect.php',
            'RewriteRule ^ovs-connect$ /ovs-connect.php [L]',
            '# Condition de réécriture pour ovs-authentification.php',
            'RewriteCond %{HTTP_COOKIE} !^.*ovs\-key=' . $result . '.*$ [NC]',
            'RewriteRule ovs-authentification.php - [F]',
            '# Redirection pour les autres erreurs 403',
            'ErrorDocument 403 /index.php?status=403',
        '</IfModule>');
        update_option('ovs_auth', $result);
        insert_with_markers(ABSPATH . '.htaccess', 'OVS Hide Login', $rules);
    }
}
function hide_login()
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

}
