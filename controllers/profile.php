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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updatedData = [
        'login' => $currentUser['login'],
        'password' => $currentUser['password'],
        'nom' => trim($_POST['nom'] ?? ''),
        'prenom' => trim($_POST['prenom'] ?? ''),
        'sexe' => $_POST['sexe'] ?? '',
        'date_naissance' => $_POST['date_naissance'] ?? ''
    ];

    $errors = validateUserData($updatedData, true);

    if (empty($errors)) {
        // Mise Ã  jour dans le tableau des utilisateurs
        foreach ($users as &$u) {
            if ($u['login'] === $currentUserLogin) {
                $u = $updatedData;
                break;
            }
        }
        unset($u);

        saveUsers($users);

        // Mise Ã  jour de la session
        $_SESSION['user'] = $updatedData;

        $success = true;
    }
}

// **Mettre Ã  jour $user pour le formulaire**
$user = $_SESSION['user'];

require_once __DIR__ . '/../views/profile_form.php';
