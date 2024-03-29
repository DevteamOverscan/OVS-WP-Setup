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
/*-------------------------------------------------
                SECURITY HTACCESS
--------------------------------------------------*/
function htaccessInclude()
{
    $rules = "
# BEGIN DISABLE PHP ENGINE
<Files *>
    SetHandler none
    SetHandler default-handler
    Options -ExecCGI
    RemoveHandler .cgi .php .php3 .php4 .php5 .php7 .phtml .pl .py .pyc .pyo
</Files>
<IfModule mod_php7.c>
    php_flag engine off
</IfModule>
<IfModule mod_php5.c>
    php_flag engine off
</IfModule>
# END DISABLE PHP ENGINE
";
    $htaccessFile = ABSPATH . '/wp-includes/.htaccess';

    $results = file_put_contents($htaccessFile, $rules, FILE_APPEND | LOCK_EX);
    return $results;
}
