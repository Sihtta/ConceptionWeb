<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/controllers/data_functions.php';
require_once __DIR__ . '/controllers/display_functions.php';
require_once __DIR__ . '/controllers/advancedResearch.php'; // ajout pour la recherche avancée

// --- GESTION DE CONNEXION UTILISATEUR ---
$loginError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'], $_POST['password'])) {
    $users = json_decode(file_get_contents(USERS_FILE), true) ?? [];

    foreach ($users as $user) {
        if ($user['login'] === $_POST['login'] && password_verify($_POST['password'], $user['password'])) {
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

// --- GESTION DU CONTENU COCKTAIL ---
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
            <!-- === Zone de recherche avancée === -->
            <section class="AdvancedSearch">
                <h2>Recherche avancée de cocktails</h2>
                <form method="post" action="">
                    <input type="text" name="requete" size="50"
                        placeholder="Ex : citron + rhum -sucre"
                        value="<?php echo isset($_POST['requete']) ? htmlspecialchars($_POST['requete']) : ''; ?>">
                    <input type="submit" value="Rechercher">
                </form>

                <?php
                // Traitement de la recherche avancée
                if (isset($_POST['requete']) && !empty($_POST['requete'])) {
                    $resultat = traiterRequete($_POST['requete']);
                    if (isset($resultat['erreur'])) {
                        echo "<p style='color:red;'>" . htmlspecialchars($resultat['erreur']) . "</p>";
                    } else {
                        // Affichage des aliments reconnus
                        echo "<p><strong>Aliments souhaités :</strong> " . implode(", ", $resultat['souhaites']) . "</p>";
                        echo "<p><strong>Aliments non souhaités :</strong> " . implode(", ", $resultat['non_souhaites']) . "</p>";
                        if (!empty($resultat['non_rec'])) {
                            echo "<p><strong>Éléments non reconnus :</strong> " . implode(", ", $resultat['non_rec']) . "</p>";
                        }

                        echo "<h3>Recettes trouvées :</h3>";
                        if (!empty($resultat['cocktails'])) {
                            echo "<ul>";
                            foreach ($resultat['cocktails'] as $cocktail) {
                                $img = CocktailImage($cocktail['titre']);
                                echo "<li>
                                        <img src='$img' alt='" . htmlspecialchars($cocktail['titre']) . "' 
                                             style='width:50px;height:50px;vertical-align:middle;margin-right:5px;'>
                                        " . htmlspecialchars($cocktail['titre']) . " (" . $cocktail['score'] . "%)
                                      </li>";
                            }
                            echo "</ul>";
                        } else {
                            echo "<p>Aucune recette ne correspond à vos critères.</p>";
                        }
                    }
                } else {
                    // === Affichage normal des cocktails ===
                    if (isset($_POST['selectedCocktail'])) {
                        echo "<h2>Cocktail sélectionné :</h2>";
                        echo DisplaySelectedCocktail();
                    } else {
                        echo "<h2>Liste des cocktails</h2>";
                        echo "<div class='CocktailList'>";
                        echo DisplaySimpleCocktails();
                        echo "</div>";
                    }
                }
                ?>
            </section>
        </main>
    </div>
</body>

</html>