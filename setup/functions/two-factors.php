<?php

// Fonction pour générer un code aléatoire
function generate_random_code()
{
    return wp_generate_password(6, false); // Génère un code de 6 caractères
}

// Fonction pour envoyer le code par e-mail
function send_verification_code($user_id, $code)
{
    $user = get_userdata($user_id);
    $to = $user->user_email;
    $subject = 'Code de vérification pour votre authentification à deux facteurs';
    $message = 'Votre code de vérification est : ' . $code;
    wp_mail($to, $subject, $message);
}

// Hook pour envoyer le code après la connexion réussie
add_action('wp_login', 'send_verification_code_after_login', 10, 2);
function send_verification_code_after_login($user_login, $user)
{
    $verification_code = generate_random_code();
    update_user_meta($user->ID, 'verification_code', $verification_code);
    send_verification_code($user->ID, $verification_code);
}

// Fonction pour vérifier le code saisi par l'utilisateur
function verify_verification_code($user_id, $code)
{
    $stored_code = get_user_meta($user_id, 'verification_code', true);
    return ($stored_code === $code);
}

// Page personnalisée pour la vérification du code
// Cette page doit inclure un formulaire où l'utilisateur peut saisir le code reçu par e-mail

// Traitement du formulaire de vérification du code
if (isset($_POST['submit'])) {
    $user_id = get_current_user_id();
    $code = $_POST['verification_code'];
    if (verify_verification_code($user_id, $code)) {
        // Code correct, permettre à l'utilisateur d'accéder à son compte
    } else {
        // Code incorrect, afficher un message d'erreur à l'utilisateur
    }
}
