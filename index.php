<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/controllers/data_functions.php';
require_once __DIR__ . '/controllers/display_functions.php';
require_once __DIR__ . '/controllers/advancedResearch.php';

// --- GESTION DE CONNEXION UTILISATEUR ---
$loginError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'], $_POST['password'])) {
    $users = json_decode(file_get_contents(USERS_FILE), true) ?? [];

    foreach ($users as $user) {
        if ($user['login'] === $_POST['login'] && password_verify($_POST['password'], $user['password'])) {
            $_SESSION['user'] = $user;
            header('Location: index.php');
            exit;
        }
    }

    $loginError = "Login ou mot de passe incorrect";
}


$showFoodMenu = true;
if (!empty($_POST['requete']) || isset($_POST['showFavorites']) || isset($_POST['selectedCocktail'])) {
    $showFoodMenu = false;
}

// Navigation standard
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <?php include __DIR__ . '/navbar.php'; ?>

    <div class="PageContent <?php echo $showFoodMenu ? '' : 'fullwidth'; ?>">

        <?php if ($showFoodMenu): ?>
        <div class="FoodMenu">
            <h2>Aliment courant</h2>
            <form method="post" action="#">
                <?php echo DisplayPath(); ?>
                <p>Sous-catégories :</p>
                <?php echo DisplaySubCategories(); ?>
            </form>
        </div>
        <?php endif; ?>

        <main class="MainContent">
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['requete'])) {
            $resultat = traiterRequete($_POST['requete']);

            if (isset($resultat['erreur'])) {
                echo "<p style='color:red;'>" . htmlspecialchars($resultat['erreur']) . "</p>";
            } else {
                echo "<p><strong>Aliments souhaités :</strong> " . (!empty($resultat['souhaites']) ? htmlspecialchars(implode(", ", $resultat['souhaites'])) : '—') . "</p>";
                echo "<p><strong>Aliments non souhaités :</strong> " . (!empty($resultat['non_souhaites']) ? htmlspecialchars(implode(", ", $resultat['non_souhaites'])) : '—') . "</p>";
                if (!empty($resultat['non_rec'])) {
                    echo "<p><strong>Éléments non reconnus :</strong> " . htmlspecialchars(implode(", ", $resultat['non_rec'])) . "</p>";
                }

                echo "<h3>Recettes trouvées :</h3>";
                if (!empty($resultat['cocktails'])) {
                    echo "<div class='CocktailList AdvancedResults'>";
                    echo DisplayAdvancedResults($resultat['cocktails']);
                    echo "</div>";
                } else {
                    echo "<p>Aucune recette ne correspond à vos critères.</p>";
                }
            }

        } elseif (isset($_POST['showFavorites'])) {
            echo "<h2>Mes recettes préférées</h2>";
            $favorites = $_SESSION['favorites'] ?? [];
            if (empty($favorites)) {
                echo "<p>Aucune recette favorite.</p>";
            } else {
                echo "<div class='CocktailList' data-page='favorites'>";
                echo DisplaySimpleCocktails(true);
                echo "</div>";
            }

        } elseif (isset($_POST['selectedCocktail'])) {
            echo "<h2>Cocktail sélectionné :</h2>";
            echo DisplaySelectedCocktail();

        } else {
            echo "<h2>Liste des cocktails</h2>";
            echo "<div class='CocktailList'>";
            echo DisplaySimpleCocktails(false);
            echo "</div>";
        }
        ?>
        </main>
    </div>

    <script src="./js/favorites.js"></script>
</body>
</html>
