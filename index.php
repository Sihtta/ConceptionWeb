<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/controllers/data_functions.php';
require_once __DIR__ . '/controllers/display_functions.php';
require_once __DIR__ . '/controllers/advanced_research.php';

// RECHARGEMENT DES FAVORIS DEPUIS LE JSON
if (isset($_SESSION['user'])) {
    $users = json_decode(file_get_contents(USERS_FILE), true) ?? [];
    foreach ($users as $u) {
        if ($u['login'] === $_SESSION['user']['login']) {
            $_SESSION['favorites'] = $u['favorites'] ?? [];
            break;
        }
    }
} else {
    $_SESSION['favorites'] = $_SESSION['favorites'] ?? [];
}

// GESTION DE CONNEXION UTILISATEUR 
// Vérifie les identifiants et crée la session
$loginError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'], $_POST['password'])) {
    $users = json_decode(file_get_contents(USERS_FILE), true) ?? [];
    foreach ($users as $user) {
        if ($user['login'] === $_POST['login'] && password_verify($_POST['password'], $user['password'])) {
            $_SESSION['user'] = $user; // Stocke l'utilisateur connecté
            header('Location: index.php');
            exit;
        }
    }
    $loginError = "Login ou mot de passe incorrect";
}

// Affiche ou masque le menu alimentaire
$showFoodMenu = true;
if (!empty($_POST['requete']) || isset($_POST['showFavorites']) || isset($_POST['selectedCocktail'])) {
    $showFoodMenu = false;
}

// Navigation standard
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['navigation'])) {
    if ($_POST['navigation'] === "navigation") {
        NavigationButton(); // Appel fonction navigation
    }
} else {
    SetCurrentFood(); // Définit l'aliment courant
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
                    <?php echo DisplayPath(); // Affiche le chemin actuel 
                    ?>
                    <p>Sous-catégories :</p>
                    <?php echo DisplaySubCategories(); // Affiche sous-catégories 
                    ?>
                </form>
            </div>
        <?php endif; ?>

        <main class="MainContent">
            <?php
            // RECHERCHE AVANCÉE 
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['requete'])) {
                $result = traiterRequete($_POST['requete']); // Traite la requête de recherche
                if (isset($result['erreur'])) {
                    echo "<p style='color:red;'>" . htmlspecialchars($result['erreur']) . "</p>";
                } else {
                    // Affiche critères si remplis
                    if (!empty($result['souhaites'])) {
                        echo "<p><strong>Aliments souhaités :</strong> " . htmlspecialchars(implode(", ", $result['souhaites'])) . "</p>";
                    }
                    if (!empty($result['non_souhaites'])) {
                        echo "<p><strong>Aliments non souhaités :</strong> " . htmlspecialchars(implode(", ", $result['non_souhaites'])) . "</p>";
                    }
                    if (!empty($result['non_rec'])) {
                        echo "<p><strong>Éléments non reconnus :</strong> " . htmlspecialchars(implode(", ", $result['non_rec'])) . "</p>";
                    }

                    $exact = 0; // Recettes entièrement satisfaites
                    $partial = 0; // Recettes partiellement satisfaites
                    foreach ($result['cocktails'] as $c) {
                        if ($c['score'] == 100) $exact++;
                        elseif ($c['score'] > 0) $partial++;
                    }

                    echo "<h3>Recettes trouvées :</h3>";
                    // Affiche nombre exact / approximatif
                    if ($result['approx']) {
                        echo "<p><strong>Recettes satisfaites entièrement :</strong> $exact</p>";
                        echo "<p><strong>Recettes satisfaites partiellement :</strong> $partial</p>";
                    } else {
                        echo "<p><strong>Résultats exacts :</strong> $exact</p>";
                    }

                    // Affiche les cocktails
                    if (!empty($result['cocktails'])) {
                        echo DisplayAdvancedResults($result['cocktails'], $result['approx']);
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
                    echo DisplaySimpleCocktails(true); // Affiche favoris
                    echo "</div>";
                }
            } elseif (isset($_POST['selectedCocktail'])) {
                echo "<h2>Cocktail sélectionné :</h2>";
                echo DisplaySelectedCocktail(); // Affiche cocktail sélectionné
            } else {
                echo "<h2>Liste des cocktails</h2>";
                echo "<div class='CocktailList'>";
                echo DisplaySimpleCocktails(false); // Affiche tous les cocktails
                echo "</div>";
            }
            ?>
        </main>
    </div>

    <script src="./js/favorites.js"></script>
</body>

</html>
```