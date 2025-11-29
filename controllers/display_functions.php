<?php
require_once "data_functions.php";

/* Affichage du menu de recherche (fil d'Ariane) */
function DisplayPath()
{
    $output = "";
    foreach ($_SESSION['path'] as $food) {
        // Formulaire pour chaque élément du chemin
        $output .= "
                <form method='post' style='display:inline;'>
                    <input type='hidden' name='food' value='" . htmlspecialchars($food, ENT_QUOTES) . "'>
                    <button type='submit' class='linkButton'>$food</button>
                </form>
            ";
        if ($food !== end($_SESSION['path'])) {
            $output .= "<span class='pathSeparator'>/</span>"; // séparateur
        }
    }
    return $output;
}

/* Affichage des sous-catégories */
function DisplaySubCategories()
{
    $output = "";
    foreach (GetSubCategories($_SESSION['currentFood']) as $subFood) {
        // Formulaire pour chaque sous-aliment
        $output .= "
                <form method='post' style='display:inline;'>
                    <input type='hidden' name='food' value='" . htmlspecialchars($subFood, ENT_QUOTES) . "'>
                    <button type='submit' class='linkButton'>$subFood</button>
                </form><br>
            ";
    }
    return $output;
}

/* Affichage d’un cocktail sélectionné */
function DisplaySelectedCocktail()
{
    global $Recettes;
    $favorites = $_SESSION['favorites'] ?? [];

    $selected = $_POST['selectedCocktail'];
    $title = $Recettes[$selected]['titre'];
    $img = CocktailImage($title);

    // Vérifie si le cocktail est favori
    $isFav = in_array($title, $favorites);
    $heartIcon = $isFav ? "<i class='fas fa-heart' style='color:#e74c3c;'></i>" : "<i class='far fa-heart' style='color:#95a5a6;'></i>";

    // Liste des ingrédients
    $ingredientsList = "<ul><li>";
    $ingredientsList .= $Recettes[$selected]['ingredients'];
    $ingredientsList = str_replace('|', '</li><li>', $ingredientsList);
    $ingredientsList .= "</li></ul>";

    $preparation = $Recettes[$selected]['preparation']; // préparation du cocktail

    // Génération du HTML complet
    $output = "
        <section class='cocktailCard' data-cocktail-title='" . htmlspecialchars($title, ENT_QUOTES) . "'>
            <div style='display:flex;justify-content:space-between;align-items:center;'>
                <h3>$title</h3>

                <form method='post' class='favoriteForm' style='margin:0;'>
                    <input type='hidden' name='cocktail' value='" . htmlspecialchars($title) . "'>
                    <button type='submit' class='heart-btn' style='background:none;border:none;font-size:24px;cursor:pointer;padding:5px;'>
                        $heartIcon
                    </button>
                </form>
            </div>

            <img src='$img' alt='$title'>

            <h4>Liste des ingrédients :</h4>
            $ingredientsList

            <h4>Préparation :</h4>
            <p>$preparation</p>
        </section>
    ";

    return $output;
}

/* Génération d’une carte cocktail réutilisable */
function RenderCocktailCard($id, $title, $img, $heartIcon, $contentHtml, $score = NULL)
{
    return "
        <section class='cocktailCard' data-cocktail-title='" . htmlspecialchars($title, ENT_QUOTES) . "'>
            <div style='display:flex;justify-content:space-between;align-items:center;'>

                <form method='post' style='display:inline;margin:0;'>
                    <input type='hidden' name='selectedCocktail' value='$id'>
                    <button type='submit' style='all:unset;cursor:pointer;'>
                        <h3>$title $score</h3>
                    </button>
                </form>

                <form method='post' class='favoriteForm' style='margin:0;'>
                    <input type='hidden' name='cocktail' value='" . htmlspecialchars($title) . "'>
                    <button type='submit' class='heart-btn' style='background:none;border:none;font-size:24px;cursor:pointer;padding:5px;'>
                        $heartIcon
                    </button>
                </form>
            </div>

            <img src='$img' alt='$title'>
            $contentHtml
        </section>
    ";
}

/* Affichage simple de la liste des cocktails */
function DisplaySimpleCocktails($favoritesOnly = false)
{
    $cocktailList = GetCocktails();
    global $Recettes;

    $favorites = $_SESSION['favorites'] ?? [];
    $output = "";

    foreach ($cocktailList as $id) {
        $title = $Recettes[$id]['titre'];

        if ($favoritesOnly && !in_array($title, $favorites)) continue;

        $img = CocktailImage($title);
        $isFav = in_array($title, $favorites);

        $heartIcon = $isFav
            ? "<i class='fas fa-heart' style='color:#e74c3c;'></i>"
            : "<i class='far fa-heart' style='color:#95a5a6;'></i>";

        $ingredientsList = "<ul>";
        foreach ($Recettes[$id]['index'] as $food) {
            $ingredientsList .= "<li>$food</li>";
        }
        $ingredientsList .= "</ul>";

        $output .= RenderCocktailCard($id, $title, $img, $heartIcon, $ingredientsList);
    }

    return $output;
}

/* Affichage avancé avec résultats et score */
function DisplayAdvancedResults($resultats, $isApprox)
{
    global $Recettes;
    $favorites = $_SESSION['favorites'] ?? [];

    if (empty($resultats)) {
        return "<p>Aucun cocktail ne correspond à votre recherche.</p>";
    }

    $output = "<div class='resultsHeader'>
        <p style='font-weight:bold;margin-bottom:15px;'>" . count($resultats) . " résultat(s) trouvé(s)</p>
    </div>
    <div class='CocktailList AdvancedResults'>";

    foreach ($resultats as $result) { // chaque résultat
        $id = $result['id'];
        if (!isset($Recettes[$id])) continue;

        $title = $Recettes[$id]['titre'];
        $img = CocktailImage($title);

        $isFav = in_array($title, $favorites);
        $heartIcon = $isFav
            ? "<i class='fas fa-heart' style='color:#e74c3c;'></i>"
            : "<i class='far fa-heart' style='color:#95a5a6;'></i>";

        $score = $isApprox ? " <span style='font-size:14px;color:#888;'>(" . $result['score'] . "%)</span>" : "";

        // Liste des ingrédients du cocktail
        $ingredientsList = "<ul>";
        foreach ($Recettes[$id]['index'] as $food) {
            $ingredientsList .= "<li>$food</li>";
        }
        $ingredientsList .= "</ul>";

        // Génération de la carte avec score
        $output .= RenderCocktailCard($id, $title, $img, $heartIcon, $ingredientsList, $score);
    }
    $output .= "</div>";

    return $output;
}

?>
<link rel="stylesheet" href="style.css">