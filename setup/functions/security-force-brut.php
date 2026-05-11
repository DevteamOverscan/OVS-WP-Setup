<?php
/**
 * Limite les tentatives de connexion répétées à l'administration.
 *
 * @package OVS
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Génération d'une clé unique stable basée sur IP uniquement
 */
function ovs_bruteforce_key() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    return 'ovs_login_fail_' . md5($ip);
}

/**
 * Avant l'authentification, vérifier si l'utilisateur est bloqué
 */
function check_attempted_login($user, $username, $password) {

    $attempt_limit = 5;

    $key = ovs_bruteforce_key();
    $data = get_transient($key);

    if (is_array($data) && ($data['tried'] ?? 0) >= $attempt_limit) {
        
        $timeout = get_option('_transient_timeout_' . $key);
        $remaining = $timeout ? ($timeout - time()) : 0;
        
        if ($remaining > 0) {
            $time = time_to_go($timeout);
            
            return new WP_Error(
                'too_many_attempts',
                sprintf(
                    __('<strong>ERREUR</strong>: Trop de tentatives. Réessayez dans %s.', 'ovs'),
                    $time
                )
            );
        }
    }

    return $user;
}
add_filter('authenticate', 'check_attempted_login', 30, 3);

/**
 * Incrémente le compteur après échec de login
 */
function login_failed($username) {

    $key = ovs_bruteforce_key();
    $data = get_transient($key);

    if (!is_array($data)) {
        $data = [
            'tried' => 1,
        ];
    } else {
        $data['tried'] = ($data['tried'] ?? 0) + 1;
    }

    set_transient($key, $data, 300);
}
add_action('wp_login_failed', 'login_failed', 10, 1);

/**
 * Formatage du temps restant
 */
function time_to_go($timestamp) {

    $periods = [
        "seconde",
        "minute",
        "heure",
        "jour",
        "semaine",
        "mois",
        "année"
    ];

    $lengths = [60, 60, 24, 7, 4.35, 12];

    $current = time();
    $diff = max(0, $timestamp - $current);

    $i = 0;

    for ($i = 0; $diff >= $lengths[$i] && $i < count($lengths) - 1; $i++) {
        $diff /= $lengths[$i];
    }

    $diff = round($diff);

    return $diff . ' ' . $periods[$i] . ($diff > 1 ? 's' : '');
}