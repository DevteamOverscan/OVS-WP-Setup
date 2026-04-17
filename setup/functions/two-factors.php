<?php

/**
 * Génère un code temporaire pour la double authentification.
 */
function generate_random_code()
{
    return wp_generate_password(6, false); // Générer un code de 6 caractères.
}

/**
 * Envoie le code de vérification à l'utilisateur par e-mail.
 */
function send_verification_code($user_id, $code)
{
    $user = get_userdata($user_id);
    $to = $user->user_email;
    $subject = 'Code de vérification pour votre authentification à deux facteurs';
    $message = 'Votre code de vérification est : ' . $code;
    wp_mail($to, $subject, $message);
}

// Déclencher l'envoi du code après une connexion réussie.
add_action('wp_login', 'send_verification_code_after_login', 10, 2);

/**
 * Génère et envoie un code de vérification après la connexion.
 */
function send_verification_code_after_login($user_login, $user)
{
    $verification_code = generate_random_code();
    update_user_meta($user->ID, 'verification_code', $verification_code);
    send_verification_code($user->ID, $verification_code);
}

// Vérifier le code saisi par l'utilisateur.
function verify_verification_code($user_id, $code)
{
    $stored_code = get_user_meta($user_id, 'verification_code', true);
    return ($stored_code === $code);
}

// Prévoir une page dédiée contenant le formulaire de saisie du code reçu par e-mail.

// Traiter la soumission du formulaire de vérification.
if (isset($_POST['submit'])) {
    $user_id = get_current_user_id();
    $code = $_POST['verification_code'];
    if (verify_verification_code($user_id, $code)) {
        // Autoriser l'accès au compte lorsque le code est valide.
    } else {
        // Afficher une erreur lorsque le code saisi est invalide.
    }
}
