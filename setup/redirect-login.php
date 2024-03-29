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
redirectLoginURL();
