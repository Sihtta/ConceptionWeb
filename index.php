<?php
session_start();
require_once __DIR__ . '/config.php'; // si nécessaire pour la gestion des données

// Traitement de la connexion (si formulaire soumis)
$loginError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'], $_POST['password'])) {
    $users = json_decode(file_get_contents(USERS_FILE), true) ?? [];

    foreach ($users as $user) {
        if ($user['login'] === $_POST['login'] && password_verify($_POST['password'], $user['password'])) {
            // Connexion réussie
            $_SESSION['user'] = [
                'login' => $user['login'],
                'nom' => $user['nom'],
                'prenom' => $user['prenom'],
                'sexe' => $user['sexe'],
                'date_naissance' => $user['date_naissance']
            ];
            header('Location: index.php'); // recharge la page pour afficher la zone connectée
            exit;
        }
    }

    $loginError = "Login ou mot de passe incorrect";
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Accueil</title>
</head>

<body>
    <h1>Bienvenue sur le site</h1>

    <div class="zone-connexion">
        <?php if (isset($_SESSION['user'])): ?>
            <!-- Utilisateur connecté -->
            <p>
                <?= htmlspecialchars($_SESSION['user']['login']) ?>
                <a href="./controllers/profile.php">Profil</a> |
                <a href="logout.php">Se déconnecter</a>
            </p>
        <?php else: ?>
            <!-- Utilisateur non connecté -->
            <?php if ($loginError): ?>
                <p style="color:red;"><?= htmlspecialchars($loginError) ?></p>
            <?php endif; ?>
            <form method="post" action="">
                <label for="login">Login :</label>
                <input type="text" id="login" name="login" required>

                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required>

                <input type="submit" value="Connexion">
                <span><a href="views/register_form.php">S'inscrire</a></span>

            </form>
        <?php endif; ?>
    </div>
</body>

</html>