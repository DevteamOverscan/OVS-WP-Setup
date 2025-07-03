# OVS-WP-Setup

## À effetuer si vous n'utilisez pas le boiler de Overscan

Dans le dossier "mu-plugins" créer un fichier _"ovs.php"_. Ajouter le code suivant à l'intérieur du fichier nouvellement créé.

```php
<?php
/**
 * Plugin Name: Ovs
 * Description: Plugin personnalisé d'Overscan pour Wordpress
 * Plugin URI:  https://www.overscan.com/
 * Version:     1
 * Author:      Overscan
 * Author URI:  https://www.overscan.com/
 * Text Domain: ovs
 * License: GPL v3 or later
 * License URI: https://www.gnu.org/licenses/quick-guide-gplv3.html
 */

/**
 *
 * @package OVS
 * @author Overscan
 * @link https://www.overscan.com
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

//Package d'installation
require WPMU_PLUGIN_DIR . '/ovs-wp-setup/ovs.php';

if(get_option('custom_plugins') !== false) {
    foreach (get_option('custom_plugins') as $plugin) {
        if(file_exists(WPMU_PLUGIN_DIR . '/'. $plugin.'/init.php')) {
            require WPMU_PLUGIN_DIR . '/'. $plugin.'/init.php';
        }
    }
}
```

## SMTP 

Si vous ne souhaitez pas utiliser un plugin SMTP externe, OVS WP Setup peut gèrer la configuration.
Vous pouvez dans ce cas ajouter les variables suivantes dans le wp-config :

```php
define('SMTP_HOST', 'smtp.example.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('SMTP_SECURE', 'tls');
```
