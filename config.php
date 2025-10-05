<?php
// On ne démarre la session que si elle n’est pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Chemin vers le dossier data à la racine
define('DATA_DIR', __DIR__ . '/data');
define('USERS_FILE', DATA_DIR . '/users.json');

// Création du dossier et du fichier JSON si absents
if (!is_dir(DATA_DIR)) {
    mkdir(DATA_DIR, 0755, true);
}
if (!file_exists(USERS_FILE)) {
    file_put_contents(USERS_FILE, json_encode([]));
}
