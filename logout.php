<?php
session_start();

// Détruire la session et déconnecter l'utilisateur
$_SESSION = [];
session_destroy();

// Redirection vers la page d'accueil
header('Location: index.php');
exit;
