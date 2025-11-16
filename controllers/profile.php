<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/user_functions.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit;
}

$errors = [];
$success = false;

$currentUserLogin = $_SESSION['user']['login'];
$users = loadUsers();
$currentUser = findUserByLogin($currentUserLogin);

if (!$currentUser) {
    header('Location: ../index.php');
    exit;
}

// Traitement uniquement si c'est le formulaire utilisateur qui est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_form_submit'])) {
    $updatedData = [
        'login' => $currentUser['login'],
        'password' => $currentUser['password'],
        'nom' => trim($_POST['nom'] ?? ''),
        'prenom' => trim($_POST['prenom'] ?? ''),
        'sexe' => $_POST['sexe'] ?? '',
        'date_naissance' => $_POST['date_naissance'] ?? '',
        'favorites' => $currentUser['favorites'] ?? []
    ];

    $errors = validateUserData($updatedData);

    if (empty($errors)) {
        foreach ($users as &$u) {
            if ($u['login'] === $currentUserLogin) {
                $u = $updatedData;
                break;
            }
        }
        unset($u);

        saveUsers($users);

        // Mettre à jour la session 
        $_SESSION['user'] = [
            'login' => $updatedData['login'],
            'nom' => $updatedData['nom'],
            'prenom' => $updatedData['prenom'],
            'sexe' => $updatedData['sexe'],
            'date_naissance' => $updatedData['date_naissance']
        ];

        $success = true;
    }
}

require_once __DIR__ . '/../views/user_form.php';
