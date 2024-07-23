# OVS-WP-Setup

## Installation

A l'intérieur de votre projet Wordpress créer un dossier "mu-plugins" à l'emplacelement suivant : "/wp-content"

Une fois le dossier créé, aller dans le dossier "mu-plugins" et cloner le repo à l'intérieur.

_Attention !!!_
Certain dossier sont supprimer à la fin du processus lancer par le plugin pour des raison de sécurité. Si vous souhaitez avoir tous les dossiers nécessaires au bon fonctionnement du plugin cloner bine le repo de puis la brache main. Les autres branches correspondent aux projets Wordpress sur lequel est présent ce plugin.

## Étapes

1. Dans le dossier "mu-plugins" créer un fichier _"ovs.php"_. Ajouter le code suivant à l'intérieur du fichier nouvellement créé.

```php
<?php
/**
 * Plugin Name: Ovs
 * Description: Plugin prsonnalisé d'Overscan pour Wordpress
 * Plugin URI:  https://www.overscan.com/
 * Version:     1
 * Author:      Clément Vacheron
 * Author URI:  https://www.overscan.com/
 * Text Domain: ovs
 * License: GPL v3 or later
 * License URI: https://www.gnu.org/licenses/quick-guide-gplv3.html
 */

/**
 *
 * @package OVS
 * @author Clément Vacheron
 * @link https://www.overscan.com
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

//Package d'installation
require WPMU_PLUGIN_DIR . '/OVS-WP-Setup/ovs.php';

if(get_option('custom_plugins') !== false) {
    foreach (get_option('custom_plugins') as $plugin) {
        if(file_exists(WPMU_PLUGIN_DIR . '/'. $plugin.'/init.php')) {
            require WPMU_PLUGIN_DIR . '/'. $plugin.'/init.php';
        }
    }
}
```

2. Accédez au répertoire cloné :
     `cd OVS-WP-Setup`

3. Supprimer la connexion/synchronisation au repo.
   `git remote remove origin`
   Ainsi les modification n'impacteront pas le code source se trouvant sur le repo.

