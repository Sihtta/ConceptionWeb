<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/controllers/data_functions.php';
require_once __DIR__ . '/controllers/display_functions.php';

// --- GESTION DE CONNEXION UTILISATEUR ---

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
            header('Location: index.php');
            exit;
        }
    }

    $loginError = "Login ou mot de passe incorrect";
}

// --- GESTION DU CONTENU COCKTAIL (disponible pour tous) ---

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['navigation'])) {
    if ($_POST['navigation'] === "navigation") {
        NavigationButton();
    }
} else {
    SetCurrentFood();
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <title>Accueil - Cocktail Management</title>
    <link rel="stylesheet" href="./css/style.css">
</head>

<body>
    <h1>Bienvenue sur le site</h1>

    <header class="navbar">
        <div class="nav-left">
            <form method="post">
                <input type="hidden" name="navigation" value="navigation">
                <input type="submit" value="Navigation">
            </form>
        </div>

        <div class="nav-right zone-connexion">
            <?php if (isset($_SESSION['user'])): ?>
                <p>
                    <?= htmlspecialchars($_SESSION['user']['login']) ?>
                    <a href="./controllers/profile.php">Profil</a> |
                    <a href="logout.php">Se déconnecter</a>
                </p>
            <?php else: ?>
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
    </header>

    <!-- Contenu du site -->
    <div class="PageContent">
        <aside class="FoodMenu">
            <h2>Aliment courant</h2>
            <form method="post" action="#">
                <?php echo DisplayPath(); ?>
                <p>Sous-catégories :</p>
                <?php echo DisplaySubCategories(); ?>
            </form>
        </aside>

        <main class="MainContent">
            <?php if (isset($_POST['selectedCocktail'])): ?>
                <h2>Cocktail sélectionné :</h2>
                <?php echo DisplaySelectedCocktail(); ?>
            <?php else: ?>
                <h2>Liste des cocktails</h2>
                <div class="CocktailList">
                    <?php echo DisplaySimpleCocktails(); ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>

</html>