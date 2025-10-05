<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/user_functions.php';

$errors = [];

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'login' => trim($_POST['login'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'nom' => trim($_POST['nom'] ?? ''),
        'prenom' => trim($_POST['prenom'] ?? ''),
        'sexe' => $_POST['sexe'] ?? '',
        'date_naissance' => $_POST['date_naissance'] ?? ''
    ];

    $errors = validateUserData($data);

    // Vérification de l’unicité du login
    if (findUserByLogin($data['login'])) {
        $errors[] = "Ce login est déjà utilisé.";
    }

    // Si pas d’erreurs → on enregistre l’utilisateur
    if (empty($errors)) {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        $users = loadUsers();
        $users[] = $data;
        saveUsers($users);

        // Connexion automatique
        $_SESSION['user'] = [
            'login' => $data['login'],
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'sexe' => $data['sexe'],
            'date_naissance' => $data['date_naissance']
        ];

        // Redirection vers l'accueil
        header('Location: ../index.php');
        exit;
    }
}

require_once __DIR__ . '/../views/register_form.php';
