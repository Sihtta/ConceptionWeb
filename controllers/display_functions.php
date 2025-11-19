<?php
require_once "data_functions.php";

/* Affichage du menu de recherche */
function DisplayPath()
{
    $output = "";
    foreach ($_SESSION['path'] as $food) {
        $output .= "
                <form method='post' style='display:inline;'>
                    <input type='hidden' name='food' value='" . htmlspecialchars($food, ENT_QUOTES) . "'>
                    <button type='submit' class='linkButton'>$food</button>
                </form>
            ";
        if ($food !== end($_SESSION['path'])) {
            $output .= "<span class='pathSeparator'>/</span>";
        }
    }
    return $output;
}

function DisplaySubCategories()
{
    $output = "";
    foreach (GetSubCategories($_SESSION['currentFood']) as $subFood) {
        $output .= "
                <form method='post' style='display:inline;'>
                    <input type='hidden' name='food' value='" . htmlspecialchars($subFood, ENT_QUOTES) . "'>
                    <button type='submit' class='linkButton'>$subFood</button>
                </form><br>
            ";
    }
    return $output;
}

function DisplaySelectedCocktail()
{
    global $Recettes;
    $favorites = $_SESSION['favorites'] ?? [];

    $selected = $_POST['selectedCocktail'];
    $title = $Recettes[$selected]['titre'];
    $img = CocktailImage($title);

    // Vérifie si ce cocktail est favori
    $isFav = in_array($title, $favorites);
    $heartIcon = $isFav ? "<i class='fas fa-heart' style='color:#e74c3c;'></i>" : "<i class='far fa-heart' style='color:#95a5a6;'></i>";

    // Ingrédients
    $ingredientList = "<ul><li>";
    $ingredientList .= $Recettes[$selected]['ingredients'];
    $ingredientList = str_replace('|', '</li><li>', $ingredientList);
    $ingredientList .= "</li></ul>";

    $preparation = $Recettes[$selected]['preparation'];

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
            $ingredientList

            <h4>Préparation :</h4>
            <p>$preparation</p>
        </section>
    ";

    return $output;
}

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

/* Affichage des Cocktails */
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

    foreach ($resultats as $res) {
        $id = $res['id'];
        if (!isset($Recettes[$id])) continue;

        $title = $Recettes[$id]['titre'];
        $img = CocktailImage($title);

        $isFav = in_array($title, $favorites);
        $heartIcon = $isFav
            ? "<i class='fas fa-heart' style='color:#e74c3c;'></i>"
            : "<i class='far fa-heart' style='color:#95a5a6;'></i>";

        $score = $isApprox ? " <span style='font-size:14px;color:#888;'>(" . $res['score'] . "%)</span>" : "";

        // Liste des ingrédients
        $ingredientsList = "<ul>";
        foreach ($Recettes[$id]['index'] as $food) {
            $ingredientsList .= "<li>$food</li>";
        }
        $ingredientsList .= "</ul>";

        // Injection du score directement dans le titre
        $output .= RenderCocktailCard($id, $title, $img, $heartIcon, $ingredientsList, $score);
    }
    $output .= "</div>";

    return $output;
}

?>
<link rel="stylesheet" href="style.css">