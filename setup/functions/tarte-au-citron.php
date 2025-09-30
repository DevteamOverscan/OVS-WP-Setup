<?php
/**
 *
 * @package OVS
 * @author Overscan
 * @link https://www.overscan.com
 */

// Ajout du système de Cookie Tarteaucitron v1.8.3 (https://tarteaucitron.io/fr/) //
// Ajout des code du système de cookie dans l'entête du site //

add_action('wp_footer', 'gdpr_consent');
function gdpr_consent()
{

    global $tarteaucitron_has_services;
    
    // Si aucun service n’est défini, on ne charge pas Tarteaucitron
    if (empty($tarteaucitron_has_services)) {
        return;
    }

    ?>

<script src="/<?= WP_CONTENT_FOLDERNAME  ?>/mu-plugins/<?= OVS_SETUP_PATH ?>/setup/functions/tarteaucitron.js-1.15.0/tarteaucitron.min.js">
</script>
<script>
    tarteaucitron.init({
        "privacyUrl": "/politique-de-confidentialite",
        /* URL de la politique de confidentialité */

        "hashtag": "#tarteaucitron",
        /* Ouvrez le panneau avec ce hashtag */

        "cookieName": "tarteaucitron",
        /* Nom du cookie */

        "orientation": "bottom",
        /* Position de la bannière (top - middle - bottom) */

        "showAlertSmall": false,
        /* Afficher la petite bannière en bas à droite */

        "cookieslist": false,
        /* Afficher la liste des cookies */

        "showIcon": true,
        /* Afficher l'icône de cookie pour gérer les cookies */

        "iconSrc": "/<?= WP_CONTENT_FOLDERNAME ?>/mu-plugins/<?= OVS_SETUP_PATH ?>/assets/img/cookie.png",

        "iconPosition": "BottomLeft",
        /* Positon de l'icone BottomRight, BottomLeft, TopRight et TopLeft */

        "adblocker": false,
        /* Afficher un avertissement si un bloqueur de publicités est détecté */

        "DenyAllCta": true,
        /* Afficher le bouton Refuser tout */

        "AcceptAllCta": true,
        /* Afficher le bouton Accepter tout lorsque la valeur est élevée */

        "highPrivacy": true,
        /* FORTEMENT RECOMMANDÉ Désactiver le consentement automatique */

        "handleBrowserDNTRequest": false,
        /* If Do Not Track == 1, disallow all */

        "removeCredit": false,
        /* Supprimer le lien de crédit */

        "moreInfoLink": true,
        /* Afficher plus de lien d'informations */

        "useExternalCss": false,
        /* Utilisation d'un css externe/custom. Si faux, le fichier tarteaucitron.css sera chargé */

        "useExternalJs": false,
        /* Utilisation d'un js externe/custom. Si faux, le fichier tarteaucitron.js sera chargé */

        // "cookieDomain": ".my-multisite-domaine.fr",
        /* Cookie partagé pour le multisite */

        "readmoreLink": "",
        /* Changer le lien readmore par défaut */

        "mandatory": true,
        /* Afficher un message sur les cookies obligatoires */
    })
</script>
<?php };
