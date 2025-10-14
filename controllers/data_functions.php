<?php
require_once __DIR__ . "/../Donnees.inc.php";

/* Fonctions pour le fil d'Ariane */
/* Définit le fil d'Ariane par rapport à l'aliment courant, agit selon s'il doit
    remonter ou continuer à évoluer */
function SavePath(string $food)
{
    if (isset($_SESSION['path']) && in_array($food, $_SESSION['path'])) {
        $key = array_search($food, $_SESSION['path']);
        if ($key !== false) {
            $_SESSION['path'] = array_slice($_SESSION['path'], 0, $key + 1);
        }
    } else {
        if (isset($_SESSION['path']) && (!in_array($food, $_SESSION['path']))) {
            $_SESSION['path'][] = $food;
        } elseif (!isset($_SESSION['path'])) {
            $_SESSION['path'] = [$food];
        }
    }
}

/* Définit l'aliment actuellement sélectionné qui sera par défaut "Aliment"
    puis à partir de celui-ci le fil d'Ariane actuel */
function SetCurrentFood()
{
    if (isset($_POST['food'])) {
        $_SESSION['currentFood'] = $_POST['food'];
    } else {
        $_SESSION['currentFood'] = "Aliment";
    }
    SavePath($_SESSION['currentFood']);
}

/* Bouton navigation */
function NavigationButton()
{
    $_SESSION['currentFood'] = 'Aliment';
    $_SESSION['path'] = [$_SESSION['currentFood']];
}

/* Fonctions de recherche dans les aliments */
function GetSubCategories(string $food)
{
    global $Hierarchie;
    return $Hierarchie[$food]['sous-categorie'] ?? [];
}

function CurrentFood()
{
    if (isset($_POST['currentFood'])) {
        return $_POST['currentFood'];
    }
    return 'Aliment';
}

/* Fonctions de récupération des cocktails recherchés */
/* Cette fonction renvoie un tableau contenant tous les aliments descendants
    de l'aliment sélectionné (lui compris), ex : fruit contient les agrumes, les baies...
    ensuite baie contient cassis, fraise, etc. */
function GetSelectedFoods()
{
    if (!isset($_SESSION['currentFood'])) {
        $_SESSION['currentFood'] = "Aliment";
    }

    $i = 0;
    $subCat = [];
    $selectedFoods = [];
    $selectedFoods[] = $_SESSION['currentFood'];

    while ($i < count($selectedFoods)) {
        $food = $selectedFoods[$i];
        $subCat = GetSubCategories($food);
        if (!empty($subCat)) {
            foreach ($subCat as $subFood) {
                if (!in_array($subFood, $selectedFoods)) {
                    array_push($selectedFoods, $subFood);
                }
            }
        }
        $i++;
    }

    return $selectedFoods;
}

/*A partire d'un element en parametre, en rocuperant les cocktailes*/
function GetSelectedFood($aliment = null)
{
      global $Hierarchie;

    // Si aucun aliment spécifié, prendre celui de la session ou "Aliment" par défaut
    if ($aliment === null) {
        $aliment = isset($_SESSION['alimentActuel']) ? $_SESSION['alimentActuel'] : "Aliment";
    }

    $alimentsSelectionees = [$aliment]; // Commence avec l'aliment lui-même
    $i = 0;

    while ($i < count($alimentsSelectionees)) {
        $alimentCourant = $alimentsSelectionees[$i];
        $sousCat = $Hierarchie[$alimentCourant]['sous-categorie'] ?? [];

        foreach ($sousCat as $alimSous) {
            if (!in_array($alimSous, $alimentsSelectionees)) {
                $alimentsSelectionees[] = $alimSous;
            }
        }

        $i++;
    }

    return $alimentsSelectionees;
}

/* À partir du tableau précédent on récupère tous les cocktails contenant l'aliment
    sélectionné ou un de ses descendants */
function GetCocktails()
{
    $selectedFoods = GetSelectedFoods();
    $selectedCocktails = [];
    global $Recettes;

    foreach ($Recettes as $key => $recipe) {
        foreach ($recipe['index'] as $food) {
            if (in_array($food, $selectedFoods)) {
                array_push($selectedCocktails, $key);
                break;
            }
        }
    }

    return $selectedCocktails;
}

/* Fonctions de récupération des données des cocktails */
/* Met les titres au format du nom des images */
function FormatImageName($string)
{
    // Normalisation de la chaîne en UTF-8
    $string = trim($string);
    $string = mb_convert_encoding($string, 'UTF-8', mb_list_encodings());

    // Mise en minuscules
    $string = mb_strtolower($string, 'UTF-8');

    // Conversion des caractères spéciaux ou accentués en ASCII
    $transliterationMap = [
        'ñ' => 'n',
        'Ñ' => 'n',
        'ø' => 'o',
        'Ø' => 'o',
        'ß' => 'ss',
        'œ' => 'oe',
        'Œ' => 'oe',
        'æ' => 'ae',
        'Æ' => 'ae',
        'ç' => 'c',
        'Ç' => 'c'
    ];

    $string = strtr($string, $transliterationMap);
    $string = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string);

    // Remplace les espaces, tirets et apostrophes par des underscores
    $string = preg_replace('/[\s\'\-]+/', '_', $string);

    // Première lettre en majuscule
    $string = ucfirst($string);

    return $string;
}

/* Récupère le chemin vers la photo à partir du titre du cocktail */
function CocktailImage($title)
{
    $photoPath = "";

    $photoPath = "./Photos/";
    $photoPath .= FormatImageName($title) . ".jpg";

    if (!file_exists($photoPath)) {
        $photoPath = "./Photos/default.jpg";
    }

    return $photoPath;
}