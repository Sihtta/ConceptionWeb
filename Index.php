<?php
require_once "FonctionsDonnees.php";
require_once "FonctionsAffichage.php";
require_once "RechercheAvancee.php";

session_start();


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['navigation'])) {
    if ($_POST['navigation'] === "navigation") {
        BoutonNavigation();
    }
} else {
    DefinirAlimentActuel();
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Gestion de cocktails</title>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="./css/Style.css">
</head>

<body>

    <form method="post">
        <input type="hidden" name="navigation" value="navigation">
        <input type="submit" value="Navigation">
    </form>

    <div class="PageContenu">

       
        <aside class="MenuAliments">
            <h2>Aliment courant</h2>
            <form method="post" action="#">
                <?php echo afficherChemin(); ?>
                <p>Sous-catégories :</p>
                <?php echo afficherSousCategories(); ?>
            </form>
        </aside>

      
        <main class="ContenuPrincipal">
            <!-- Zone de recherche -->
            <h2>Recherche de cocktails</h2>
            <form method="post" action="">
                <input type="text" name="requete" size="50" 
                       value="<?php echo isset($_POST['requete']) ? htmlspecialchars($_POST['requete']) : ''; ?>">
                <input type="submit" value="Rechercher">
            </form>

            <?php
            // Traitement de la recherche
            if (isset($_POST['requete']) && !empty($_POST['requete'])) {
                $resultat = traiterRequete($_POST['requete']);

                if (isset($resultat['erreur'])) {
                    echo "<p style='color:red;'>" . $resultat['erreur'] . "</p>";
                } else {
                    // Affichage des aliments reconnus
                    echo "<p><strong>Aliments souhaités :</strong> " . implode(", ", $resultat['souhaites']) . "</p>";
                    echo "<p><strong>Aliments non souhaités :</strong> " . implode(", ", $resultat['non_souhaites']) . "</p>";
                    if (!empty($resultat['non_rec'])) {
                        echo "<p><strong>Éléments non reconnus :</strong> " . implode(", ", $resultat['non_rec']) . "</p>";
                    }

                    // Affichage des cocktails
                    echo "<h3>Recettes trouvées :</h3>";
                    if (!empty($resultat['cocktails'])) {
                        echo "<ul>";
                        foreach ($resultat['cocktails'] as $cocktail) {
                            $img = ImageCocktail($cocktail['titre']);
                            echo "<li>
                                    <img src='$img' alt='".$cocktail['titre']."' style='width:50px;height:50px;vertical-align:middle;margin-right:5px;'>
                                    ".$cocktail['titre']." (".$cocktail['score']."%)
                                  </li>";
                        }
                        echo "</ul>";
                    } else {
                        echo "<p>Aucune recette ne correspond à vos critères.</p>";
                    }
                }
            } else {
                // Affichage normal des cocktails si aucun choix de recherche
                if (isset($_POST['cocktailChoisi'])) {
                    echo "<h2>Cocktail sélectionné :</h2>";
                    echo afficherCocktailsChoisi();
                } else {
                    echo "<h2>Liste des cocktails</h2>";
                    echo "<div class='ListeCocktails'>";
                    echo afficherCocktailsSimple();
                    echo "</div>";
                }
            }
            ?>
        </main>
    </div>
</body>
</html>
