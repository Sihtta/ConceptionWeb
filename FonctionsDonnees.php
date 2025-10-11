<?php
    require_once "Donnees.inc.php";

    /* Fonctions pour le fil d'ariane */
    /* Définit le fil d'ariane par rapport à l'aliment courant, agit celon s'il doit
    remonter ou continuer à évoluer */
    function SauvegarderChemin($aliment) {
    // Start session if it hasn't been started already
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Initialize $_SESSION['chemin'] if it's not set
    if (!isset($_SESSION['chemin'])) {
        $_SESSION['chemin'] = [];
    }

    // If the aliment is already in the chemin array
    if (in_array($aliment, $_SESSION['chemin'])) {
        $cle = array_search($aliment, $_SESSION['chemin']);
        if ($cle !== false) {
            // Cut the array to the found index (to maintain history up to this aliment)
            $_SESSION['chemin'] = array_slice($_SESSION['chemin'], 0, $cle + 1);
        }
    } else {
        // If aliment is not in the array, add it
        $_SESSION['chemin'][] = $aliment;
    }
}


    /* Définit l'aliment actuellement sélectionné qui sera par défaut "Aliment"
    puis à partir de celui-ci le fil d'ariane actuel */
    function DefinirAlimentActuel() {
        if(isset($_POST['aliment'])) {
            $_SESSION['alimentActuel'] = $_POST['aliment'];
        } else  {
            $_SESSION['alimentActuel'] = "Aliment";
        }
        SauvegarderChemin($_SESSION['alimentActuel']);
    }

    /* Bouton navigation */
    function BoutonNavigation() {
        $_SESSION['alimentActuel'] = 'Aliment';
        $_SESSION['chemin'] = [$_SESSION['alimentActuel']];
    }

    /* Fonctions de recherche dans les aliments */
   function GetSousCategories($aliment) {
    global $Hierarchie;
    if (isset($Hierarchie[$aliment]['sous-categorie'])) {
        return $Hierarchie[$aliment]['sous-categorie'];
    } else {
        return array();
    }
}

    function alimentActuel() {
        if(isset($_POST['alimentActuel'])) {
            return $_POST['alimentActuel'];
        }
        return 'Aliment';
    }

/**
 * Récupère tous les aliments descendants d'un aliment donné, y compris l'aliment lui-même.
 * 
 * Modification : ajout d'un paramètre $aliment pour permettre la recherche d'un aliment spécifique
 * sans dépendre de la variable de session $_SESSION['alimentActuel']. 
 * Si aucun paramètre n'est fourni, la fonction utilise l'aliment courant de la session.
 */
    function RecupererAlimentsSelectionees($aliment = null) {
    global $Hierarchie;

    if ($aliment === null) {
        $aliment = isset($_SESSION['alimentActuel']) ? $_SESSION['alimentActuel'] : "Aliment";
    }

    $alimentsSelectionees = [$aliment];
    $i = 0;

    while ($i < count($alimentsSelectionees)) {
        $alimentCourant = $alimentsSelectionees[$i];
        $sousCat = GetSousCategories($alimentCourant);
        if (!empty($sousCat)) {
            foreach ($sousCat as $alimSous) {
                if (!in_array($alimSous, $alimentsSelectionees)) {
                    $alimentsSelectionees[] = $alimSous;
                }
            }
        }
        $i++;
    }

    return $alimentsSelectionees;
}

    /* A partir du tableau précédent on récupère tous les cocktail contenant l'aliment
    sélectionné ou un de ses descendants*/
    function RecupererCocktails() {
        $alimentsSelectionees = RecupererAlimentsSelectionees();
        $cocktailsSelectionees = [];
        global $Recettes;

        foreach($Recettes as $key => $recette) {
            foreach($recette['index'] as $aliment) {
                if(in_array($aliment, $alimentsSelectionees)) {
                    array_push($cocktailsSelectionees, $key);
                    break;
                }
            }
        }

        return $cocktailsSelectionees;
    }

    /* Fonctions de récupération des données des cocktails */
    /* Mettre les titres au format du noms des images */
    function formatNomImage($string) {
        // Normalisation de la chaîne en UTF-8
        $string = trim($string);
        $string = mb_convert_encoding($string, 'UTF-8', mb_list_encodings());

        // Mise en minuscules
        $string = mb_strtolower($string, 'UTF-8');

        // Conversion des caractères spéciaux ou accentués en ASCII
        $transliterationMap = [
            'ñ' => 'n', 'Ñ' => 'n',
            'ø' => 'o', 'Ø' => 'o',
            'ß' => 'ss',
            'œ' => 'oe', 'Œ' => 'oe',
            'æ' => 'ae', 'Æ' => 'ae',
            'ç' => 'c', 'Ç' => 'c'
        ];

        $string = strtr($string, $transliterationMap);
        $string = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string);

        // Remplace les espaces, tirets et apostrophes par des underscores
        $string = preg_replace('/[\s\'\-]+/', '_', $string);

        // Première lettre en majuscule
        $string = ucfirst($string);

        return $string;
    }

    /* Récupérer le chemin vers la photo à partir du titre du cocktail */
    function ImageCocktail($titre) {
        $cheminPhotos = "";

        $cheminPhotos = "./Photos/";
        $cheminPhotos .= formatNomImage($titre) . ".jpg";

        if (!file_exists($cheminPhotos)) {
            $cheminPhotos = "./Photos/default.jpg";
        }

        return $cheminPhotos;
    }
?>
