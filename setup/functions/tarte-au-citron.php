<?php
/**
 * Intègre TarteAuCitron pour la gestion du consentement aux cookies.
 *
 * @package OVS
 * @author Overscan
 * @link https://www.overscan.com
 */

if (!defined('ABSPATH')) {
    exit; /* Sortie si accès direct. */
}

// Injecter le script de consentement aux cookies dans le pied de page.
add_action('wp_footer', 'gdpr_consent');

/**
 * Affiche la configuration TarteAuCitron si des services sont déclarés.
 */
function gdpr_consent()
{

    global $tarteaucitron_has_services;

    // Ne pas charger TarteAuCitron si aucun service n'est configuré.
    if (empty($tarteaucitron_has_services)) {
        return;
    }

    ?>

<script src="/<?= WP_CONTENT_FOLDERNAME  ?>/mu-plugins/<?= OVS_SETUP_PATH ?>/setup/functions/tarteaucitron.js-1.15.0/tarteaucitron.min.js">
</script>
<script>
    tarteaucitron.init({
        "privacyUrl": "/politique-de-confidentialite",
        /* URL de la politique de confidentialité. */

        "hashtag": "#tarteaucitron",
        /* Ouvrir le panneau de gestion avec ce hashtag. */

        "cookieName": "tarteaucitron",
        /* Nom du cookie de consentement. */

        "orientation": "bottom",
        /* Position de la bannière : top, middle ou bottom. */

        "showAlertSmall": false,
        /* Afficher la bannière réduite en bas de l'écran. */

        "cookieslist": false,
        /* Afficher la liste détaillée des cookies. */

        "showIcon": true,
        /* Afficher l'icône permettant de rouvrir le panneau cookies. */

        "iconSrc": "/<?= WP_CONTENT_FOLDERNAME ?>/mu-plugins/<?= OVS_SETUP_PATH ?>/assets/img/cookie.png",

        "iconPosition": "BottomLeft",
        /* Position de l'icône : BottomRight, BottomLeft, TopRight ou TopLeft. */

        "adblocker": false,
        /* Afficher un avertissement si un bloqueur de publicité est détecté. */

        "DenyAllCta": true,
        /* Afficher le bouton de refus global. */

        "AcceptAllCta": true,
        /* Afficher le bouton d'acceptation globale. */

        "highPrivacy": true,
        /* Désactiver le consentement automatique. */

        "handleBrowserDNTRequest": false,
        /* Refuser automatiquement les services si le navigateur envoie Do Not Track. */

        "removeCredit": false,
        /* Conserver le lien de crédit TarteAuCitron. */

        "moreInfoLink": true,
        /* Afficher un lien d'information complémentaire. */

        "useExternalCss": false,
        /* Charger une feuille de style externe personnalisée à la place du CSS natif. */

        "useExternalJs": false,
        /* Charger un script externe personnalisé à la place du JS natif. */

        // "cookieDomain": ".my-multisite-domaine.fr",
        /* Partager le cookie de consentement sur un multisite. */

        "readmoreLink": "",
        /* Remplacer le lien « read more » par défaut. */

        "mandatory": true,
        /* Afficher un message concernant les cookies obligatoires. */
    })
</script>
<?php };
