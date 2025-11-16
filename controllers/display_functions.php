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

/* Affichage des Cocktails */
function DisplaySimpleCocktails($favoritesOnly = false)
{
    $cocktailList = GetCocktails();
    global $Recettes;

    // Favoris de la session (liste de titres)
    $favorites = $_SESSION['favorites'] ?? [];

    $output = "";

    foreach ($cocktailList as $value) {
        $title = $Recettes[$value]['titre'];

        // Si on affiche uniquement les favoris, on saute ceux qui ne le sont pas
        if ($favoritesOnly && !in_array($title, $favorites)) {
            continue;
        }

        $img = CocktailImage($title);

        // Icône Font Awesome : plein ou vide
        $isFav = in_array($title, $favorites);
        $heartIcon = $isFav ? "<i class='fas fa-heart' style='color:#e74c3c;'></i>" : "<i class='far fa-heart' style='color:#95a5a6;'></i>";

        $output .= "
            <section class='cocktailCard' data-cocktail-title='" . htmlspecialchars($title, ENT_QUOTES) . "'>
                <div style='display:flex;justify-content:space-between;align-items:center;'>
                    <form method='post' style='display:inline;margin:0;'>
                        <input type='hidden' name='selectedCocktail' value='$value'>
                        <button type='submit' style='all:unset;cursor:pointer;'>
                            <h3>$title</h3>
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
                <ul>
        ";

        foreach ($Recettes[$value]['index'] as $food) {
            $output .= "<li>$food</li>";
        }

        $output .= "
                </ul>
            </section>
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

?>
<link rel="stylesheet" href="style.css">