<?php
session_start();

header('Content-Type: application/json');

// Initialisation du tableau de favoris en session
if (!isset($_SESSION['favorites'])) {
    $_SESSION['favorites'] = [];
}

if (isset($_POST['cocktail'])) {
    $cocktail = $_POST['cocktail'];
    $isFavorite = false;

    // Ajout ou suppression du favori
    if (in_array($cocktail, $_SESSION['favorites'])) {
        $_SESSION['favorites'] = array_diff($_SESSION['favorites'], [$cocktail]);
        $isFavorite = false;
    } else {
        $_SESSION['favorites'][] = $cocktail;
        $isFavorite = true;
    }

    // Sauvegarde durable si l'utilisateur est connecté
    if (isset($_SESSION['user'])) {
        $users = json_decode(file_get_contents(__DIR__ . '/../data/users.json'), true);
        if (!is_array($users)) {
            $users = [];
        }
        foreach ($users as &$user) {
            if ($user['login'] === $_SESSION['user']['login']) {
                $user['favorites'] = $_SESSION['favorites'];
                break;
            }
        }
        file_put_contents(__DIR__ . '/../data/users.json', json_encode($users));
    }

    // Réponse JSON
    echo json_encode([
        'success' => true,
        'isFavorite' => $isFavorite,
        'cocktail' => $cocktail
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Aucun cocktail spécifié'
    ]);
}
exit;
