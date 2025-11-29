<?php
require_once __DIR__ . '/../config.php';

// Valide les données saisies par l'utilisateur
function validateUserData($data)
{
    $errors = [];

    // Login : lettres et chiffres uniquement
    if (!isset($data['login']) || !preg_match('/^[a-zA-Z0-9]+$/', $data['login'])) {
        $errors[] = "Login invalide (lettres non accentuées et chiffres uniquement).";
    }

    // Mot de passe obligatoire
    if (!isset($data['password']) || empty($data['password'])) {
        $errors[] = "Mot de passe obligatoire.";
    }

    // Nom
    if (!empty($data['nom']) && !preg_match("/^([a-zA-ZÀ-ÖØ-öø-ÿ]+(([ ]+|[-'][a-zA-ZÀ-ÖØ-öø-ÿ]+))*)*$/u", $data['nom'])) {
        $errors[] = "Nom invalide.";
    }

    // Prénom
    if (!empty($data['prenom']) && !preg_match("/^([a-zA-ZÀ-ÖØ-öø-ÿ]+(([ ]+|[-'][a-zA-ZÀ-ÖØ-öø-ÿ]+))*)*$/u", $data['prenom'])) {
        $errors[] = "Prénom invalide.";
    }

    // Date de naissance ≥ 18 ans
    if (!empty($data['date_naissance'])) {
        $birthDate = new DateTime($data['date_naissance']);
        $today = new DateTime();
        if ($today->diff($birthDate)->y < 18) {
            $errors[] = "Vous devez avoir au moins 18 ans.";
        }
    }

    return $errors;
}

// Charge tous les utilisateurs depuis le fichier JSON
function loadUsers()
{
    $users = json_decode(file_get_contents(USERS_FILE), true);
    return is_array($users) ? $users : [];
}

// Sauvegarde tous les utilisateurs dans le fichier JSON
function saveUsers($users)
{
    file_put_contents(USERS_FILE, json_encode($users, JSON_PRETTY_PRINT));
}

// Cherche un utilisateur par son login
function findUserByLogin($login)
{
    $users = loadUsers();
    foreach ($users as $user) {
        if ($user['login'] === $login) {
            return $user;
        }
    }
    return null;
}
