<?php

require_once('wp-load.php');

$auth = get_option('ovs_auth', false);

if (!empty($_GET['action']) && $_GET['action'] !== 'rp') {
    if (!isset($_COOKIE['ovs-key']) || $_COOKIE['ovs-key'] !== $auth) {
        // Rediriger vers la page d'accueil avec un paramètre access_denied
        header("Location: index.php?access_denied=1");
        exit();
    }
} elseif (!isset($_COOKIE['ovs-key'])) {
    // Générer une nouvelle clé aléatoire si le cookie n'est pas présent
    $str = bin2hex(random_bytes(32));
    $new_auth = hash("sha256", $str);
    update_option('ovs_auth', $new_auth);
    setcookie("ovs-key", $new_auth, strtotime("+1 week"), '/');
}

header("Location: ovs-authentification.php");
exit();
