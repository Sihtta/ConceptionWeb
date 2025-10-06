<?php
require_once "data_functions.php";

/* Affichage du menu de recherche */
function DisplayPath()
{
    $output = "";
    foreach ($_SESSION['path'] as $food) {
        $output .= "
                <form method='post' style='display:inline;'>
                    <input type='hidden' name='food' value='$food'>
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
                    <input type='hidden' name='food' value='$subFood'>
                    <button type='submit' class='linkButton'>$subFood</button>
                </form><br>
            ";
    }
    return $output;
}

/* Affichage des Cocktails */
function DisplaySimpleCocktails()
{
    $cocktailList = GetCocktails();
    global $Recettes;

    $output = "";
    $title = "";
    $img = "";
    foreach ($cocktailList as $value) {
        $title = $Recettes[$value]['titre'];
        $img = CocktailImage($title);

        $output .= "
                <section class='cocktailCard'>
                    <form method='post' style='display:inline;'>
                        <input type='hidden' name='selectedCocktail' value='$value'>
                        <button type='submit' style='all:unset;cursor:pointer;'>
                            <h3>$title</h3>
                        </button>
                    </form>
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

    $title = $Recettes[$_POST['selectedCocktail']]['titre'];
    $img = CocktailImage($title);
    $ingredientList = "<ul><li>";
    $ingredientList .= $Recettes[$_POST['selectedCocktail']]['ingredients'];
    $ingredientList = str_replace('|', '</li><li>', $ingredientList);
    $ingredientList .= "</li></ul>";
    $preparation = $Recettes[$_POST['selectedCocktail']]['preparation'];

    $output = "
            <section class='cocktailCard'>
                <h3>$title</h3>
                <img src='$img' alt='$title'>
                <h4>Liste des ingrédients :</h4>
                <p>$ingredientList</p>
                <h4>Préparation :</h4>
                <p>$preparation</p>
            </section>
        ";

    return $output;
}
?>
<link rel="stylesheet" href="style.css">