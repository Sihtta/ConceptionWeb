<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/user_functions.php';

$errors = [];

// Traitement UNIQUEMENT si c'est le formulaire utilisateur qui est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_form_submit'])) {
    $data = [
        'login' => trim($_POST['login'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'nom' => trim($_POST['nom'] ?? ''),
        'prenom' => trim($_POST['prenom'] ?? ''),
        'sexe' => $_POST['sexe'] ?? '',
        'date_naissance' => $_POST['date_naissance'] ?? ''
    ];

    $errors = validateUserData($data);

    if (findUserByLogin($data['login'])) {
        $errors[] = "Ce login est déjà utilisé.";
    }

    if (empty($errors)) {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        $users = loadUsers();
        $users[] = $data;
        saveUsers($users);

        $_SESSION['user'] = [
            'login' => $data['login'],
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'sexe' => $data['sexe'],
            'date_naissance' => $data['date_naissance']
        ];

        header('Location: ../index.php');
        exit;
    }
}

require_once __DIR__ . '/../views/user_form.php';
