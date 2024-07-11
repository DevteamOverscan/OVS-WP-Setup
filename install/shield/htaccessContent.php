<?php
/**
 *
 * @package OVS
 * @author Clément Vacheron
 * @link https://www.overscan.com
 */

if (!defined('ABSPATH')) {
    exit;
}

function htaccessContent()
{
    $rules = "
# Empêche la navigation dans les répertoires
Options All -Indexes
Order deny,allow
Deny from all
<Files ~ \"\.(xml|css|ico|xsl|jpe?g|json|png|gif|js|pdf|svg|webp|ttf|woff|woff2)$\">
Allow from all
</Files>
<FilesMatch \"^webpc-passthru\.php$\">
Allow From All
</FilesMatch>
";
    $htaccessFile = ABSPATH . WP_CONTENT_FOLDERNAME . '/.htaccess';
    $results = file_put_contents($htaccessFile, $rules, FILE_APPEND | LOCK_EX);
    return $results;

}
