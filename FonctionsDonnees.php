<?php
    require_once "Donnees.inc.php";

    /* Fonctions pour le fil d'ariane */
    /* Définit le fil d'ariane par rapport à l'aliment courant, agit celon s'il doit
    remonter ou continuer à évoluer */
    function SauvegarderChemin(string $aliment) {
        if(in_array($aliment, $_SESSION['chemin'])) {
            $cle = array_search($aliment, $_SESSION['chemin']);
            if ($cle !== false) {
                $_SESSION['chemin'] = array_slice($_SESSION['chemin'], 0, $cle + 1);
            }
        } else {
            if(isset($_SESSION['chemin']) && (!in_array($aliment, $_SESSION['chemin']))) {
                $_SESSION['chemin'][] = $aliment;
            } elseif(!isset($_SESSION['chemin'])) {
                $_SESSION['chemin'] = [$aliment];
            }
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
    function GetSousCategories(string $aliment) {
        global $Hierarchie;
        return $Hierarchie[$aliment]['sous-categorie'] ?? [];
    }

    function alimentActuel() {
        if(isset($_POST['alimentActuel'])) {
            return $_POST['alimentActuel'];
        }
        return 'Aliment';
    }

    /* Fonctions de récupérations des cocktails recherchés */
    /* Cette fonction renvoie un tableau contenant tous les aliments descendant
    de l'aliment séléctionné (lui compris), ex : fruit contient les agrumes, les baies..
    ensuite baie contient cassis, fraise ect.. */
    function RecupererAlimentsSelectionees() {
        if(!isset($_SESSION['alimentActuel'])) {
            $_SESSION['alimentActuel'] = "Aliment";
        }

        $i = 0;
        $sousCat = [];
        $alimentsSelectionees = [];
        $alimentsSelectionees[] = $_SESSION['alimentActuel'];

        while($i < count($alimentsSelectionees)) {
            $aliment = $alimentsSelectionees[$i];
            $sousCat = GetSousCategories($aliment);
            if(!empty($sousCat)) {
                foreach($sousCat as $alimentSousCat) {
                    if (!in_array($alimentSousCat, $alimentsSelectionees)) {
                        array_push($alimentsSelectionees, $alimentSousCat);
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