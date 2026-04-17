<?php
/**
 * Limite les tentatives de connexion répétées à l'administration.
 *
 * @package OVS
 * @author Overscan
 * @link https://www.overscan.com
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Bloque temporairement l'authentification après plusieurs échecs.
 */
function check_attempted_login($user, $username, $password)
{
    if (get_transient('attempted_login')) {
        $datas = get_transient('attempted_login');

        if ($datas['tried'] >= 3) {
            $until = get_option('_transient_timeout_' . 'attempted_login');
            $time = time_to_go($until);

            return new WP_Error('too_many_tried', sprintf(__('<strong>ERREUR</strong>: Limite d\'authentification atteinte, réessayez dans %1$s.'), $time));
        }
    }

    return $user;
}
add_filter('authenticate', 'check_attempted_login', 30, 3);

/**
 * Incrémente le compteur après un échec de connexion.
 */
function login_failed($username)
{
    if (get_transient('attempted_login')) {
        $datas = get_transient('attempted_login');
        $datas['tried']++;

        if ($datas['tried'] <= 3) {
            set_transient('attempted_login', $datas, 300);
        }
    } else {
        $datas = array(
            'tried' => 1
        );
        set_transient('attempted_login', $datas, 300);
    }
}
add_action('wp_login_failed', 'login_failed', 10, 1);

/**
 * Convertit une échéance en durée lisible.
 */
function time_to_go($timestamp)
{
    // Convertir le délai restant en unité compréhensible pour l'utilisateur.
    $periods = array(
        "seconde",
        "minute",
        "heure",
        "jour",
        "semaine",
        "mois",
        "année"
    );
    $lengths = array(
        "60",
        "60",
        "24",
        "7",
        "4.35",
        "12"
    );
    $current_timestamp = time();
    $difference = abs($current_timestamp - $timestamp);
    for ($i = 0; $difference >= $lengths[$i] && $i < count($lengths) - 1; $i++) {
        $difference /= $lengths[$i];
    }
    $difference = round($difference);
    if (isset($difference)) {
        if ($difference != 1) {
            $periods[$i] .= "s";
        }
        $output = "$difference $periods[$i]";
        return $output;
    }
}
