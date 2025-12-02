
<?php
require_once __DIR__ . "/../Donnees.inc.php";
require_once __DIR__ . "/data_functions.php";


/* Trouver la vraie clé dans la hiérarchie */
function findRealKey($food)
{
    global $Hierarchie;

    // Supprime les espaces
    $cleanFood = trim($food);

    // Vérification EXACTE avec la casse
    if (isset($Hierarchie[$cleanFood])) {
        return $cleanFood;
    }

    return null; // Non trouvé
}

/* Analyse de la requête utilisateur */
function analyserRequete($requete)
{
    $requete = trim($requete);

    // Vérification quotes équilibrées
    if (substr_count($requete, '"') % 2 != 0) {
        return ['erreur' => 'Problème de syntaxe : nombre impair de double-quotes'];
    }

    $wanted = [];      // ingrédients souhaités
    $notWanted = [];  // ingrédients à exclure
    $unrecognized = [];        // non reconnus

    preg_match_all('/"([^"]+)"/iu', $requete, $matches_quotes);
    $entre_quotes = $matches_quotes[1];

    $reste = preg_replace('/"([^"]+)"/iu', '', $requete);
    $mots = preg_split('/\s+/', trim($reste));

    $elements = array_merge($entre_quotes, $mots);

    foreach ($elements as $element) {
        $element = trim($element);
        if ($element === "") continue;

        $prefix = $element[0];
        $rawFood = ($prefix === '+' || $prefix === '-') ? substr($element, 1) : $element;

        $realKey = findRealKey($rawFood);

        if ($prefix === '+') {
            if ($realKey) $wanted[] = $realKey;
            else $unrecognized[] = $element;
        } elseif ($prefix === '-') {
            if ($realKey) $notWanted[] = $realKey;
            else $unrecognized[] = $element;
        } else {
            if ($realKey) $wanted[] = $realKey;
            else $unrecognized[] = $element;
        }
    }

    return [
        'souhaites' => $wanted,
        'non_souhaites' => $notWanted,
        'non_rec' => $unrecognized
    ];
}

/* Recherche des cocktails et calcul du score */
function rechercherCocktails($wanted, $notWanted)
{
    global $Recettes;
    $resultats = [];

    $total_criteres = count($wanted) + count($notWanted);

    foreach ($Recettes as $key => $recette) {

        $score = 0;

        // + souhaités
        foreach ($wanted as $alim) {
            $desc = GetSelectedFood($alim);
            if (array_intersect($recette['index'], $desc)) {
                $score++;
            }
        }

        // - non souhaités
        $compatible = true;
        foreach ($notWanted as $alim) {
            $desc = GetSelectedFood($alim);
            if (array_intersect($recette['index'], $desc)) {
                $compatible = false;
                break;
            }
        }

        if ($compatible) $score += count($notWanted);

        if ($total_criteres > 0 && $score > 0) {
            $pourcentage = round(($score / $total_criteres) * 100);

            $resultats[] = [
                'titre' => $recette['titre'],
                'score' => $pourcentage,
                'id' => $key
            ];
        }
    }

    // Tri des résultats par score décroissant
    usort($resultats, function ($a, $b) {
        return ($b['score'] > $a['score']) ? 1 : (($b['score'] < $a['score']) ? -1 : 0);
    });

    return $resultats;
}

/* Affichage des résultats avec DisplayAdvancedResults, fonction dans display_functions */

/* Traitement final de la requête */
function traiterRequete($requete)
{
    $analyse = analyserRequete($requete);

    if (isset($analyse['erreur'])) {
        return $analyse;
    }

    $wanted = $analyse['souhaites'];
    $notWanted = $analyse['non_souhaites'];

    $total = count($wanted) + count($notWanted);

    if ($total == 0) {
        return [
            'erreur' =>
            "Problème dans votre requête : recherche impossible"
        ];
    }

    $cocktails = rechercherCocktails($wanted, $notWanted);

    return [
        'souhaites' => $wanted,
        'non_souhaites' => $notWanted,
        'non_rec' => $analyse['non_rec'],
        'cocktails' => $cocktails,
        'approx' => ($total >= 2) // vrai si recherche approximative
    ];
}
?>
