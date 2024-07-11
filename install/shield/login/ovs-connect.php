<?php

require_once('wp-load.php');
$auth = get_option('ovs_auth', false);

if (isset($_COOKIE['ovs-key']) && $_COOKIE['ovs-key'] !== $auth) {
    exit();
} elseif (!isset($_COOKIE['ovs-key'])) {
    setcookie("ovs-key", $auth, strtotime("+1 week"), '/');
}
header("Location:ovs-authentification.php");
exit();
