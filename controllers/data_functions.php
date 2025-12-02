<?php
require_once __DIR__ . "/../Donnees.inc.php";

/* Fonctions pour le fil d'Ariane */
/* Définit le fil d'Ariane par rapport à l'aliment courant */
function SavePath($food)
{
    if (isset($_SESSION['path']) && in_array($food, $_SESSION['path'])) {
        $key = array_search($food, $_SESSION['path']);
        if ($key !== false) {
            $_SESSION['path'] = array_slice($_SESSION['path'], 0, $key + 1); // remonte si déjà présent
        }
    } else {
        if (isset($_SESSION['path']) && (!in_array($food, $_SESSION['path']))) {
            $_SESSION['path'][] = $food; // ajoute nouvel aliment
        } elseif (!isset($_SESSION['path'])) {
            $_SESSION['path'] = [$food]; // initialise le chemin
        }
    }
}

/* Définit l'aliment actuellement sélectionné */
function SetCurrentFood()
{
    if (isset($_POST['food'])) {
        $_SESSION['currentFood'] = $_POST['food'];
    } else {
        $_SESSION['currentFood'] = "Aliment"; // valeur par défaut
    }
    SavePath($_SESSION['currentFood']); // met à jour le fil d'Ariane
}

/* Bouton navigation → réinitialise le chemin */
function NavigationButton()
{
    $_SESSION['currentFood'] = 'Aliment';
    $_SESSION['path'] = [$_SESSION['currentFood']];
}

/* Fonctions de recherche dans les aliments */
function GetSubCategories($food)
{
    global $Hierarchie;
    return isset($Hierarchie[$food]['sous-categorie']) ? $Hierarchie[$food]['sous-categorie'] : [];  // retourne les sous-catégories
}

function CurrentFood()
{
    if (isset($_POST['currentFood'])) {
        return $_POST['currentFood'];
    }
    return 'Aliment'; // valeur par défaut
}

/* Récupère tous les aliments descendants de l'aliment sélectionné */
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
        $food = $selectedFoods[$i]; // aliment courant
        $subCat = GetSubCategories($food);
        if (!empty($subCat)) {
            foreach ($subCat as $subFood) { // sous-aliment
                if (!in_array($subFood, $selectedFoods)) {
                    array_push($selectedFoods, $subFood); // ajoute si non déjà présent
                }
            }
        }
        $i++;
    }

    return $selectedFoods;
}

/* Récupère les aliments sélectionnés, incluant l'aliment lui-même et ses descendants */
function GetSelectedFood($food = null)
{
    global $Hierarchie;

    // Si aucun aliment spécifié, prendre celui de la session ou "Aliment" par défaut
    if ($food === null) {
        $food = isset($_SESSION['alimentActuel']) ? $_SESSION['alimentActuel'] : "Aliment";
    }

    $selectedFoods = [$food]; // Commence avec l'aliment lui-même
    $i = 0;

    while ($i < count($selectedFoods)) {
        $currentFood = $selectedFoods[$i];
        $sousCat = isset($Hierarchie[$currentFood]['sous-categorie']) ? $Hierarchie[$currentFood]['sous-categorie'] : [];

        foreach ($sousCat as $subFood) {
            if (!in_array($subFood, $selectedFoods)) {
                $selectedFoods[] = $subFood;
            }
        }

        $i++;
    }

    return $selectedFoods;
}

/* Récupère tous les cocktails contenant l'aliment sélectionné ou un descendant */
function GetCocktails()
{
    $selectedFoods = GetSelectedFoods();
    $selectedCocktails = [];
    global $Recettes;

    foreach ($Recettes as $key => $recipe) {
        foreach ($recipe['index'] as $food) {
            if (in_array($food, $selectedFoods)) {
                array_push($selectedCocktails, $key); // ajoute le cocktail
                break;
            }
        }
    }

    return $selectedCocktails;
}

/* Fonctions pour formater les titres en noms d'images */
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

/* Récupère le chemin vers la photo du cocktail */
function CocktailImage($title)
{
    $photoPath = "";

    $photoPath = "./Photos/";
    $photoPath .= FormatImageName($title) . ".jpg";

    if (!file_exists($photoPath)) {
        $photoPath = "./Photos/default.jpg"; // image par défaut
    }

    return $photoPath;
}
