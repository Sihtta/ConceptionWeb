
<?php
require_once __DIR__ . "/../Donnees.inc.php";
require_once __DIR__ . "/data_functions.php";

/* Normalisation lowercase */
function normalize($str)
{
    return trim(mb_strtolower($str, "UTF-8"));
}

/* Trouver la vraie clé dans la hiérarchie */
function findRealKey($aliment)
{
    global $Hierarchie;

    $aliment_norm = normalize($aliment);

    foreach ($Hierarchie as $key => $value) {
        if (normalize($key) === $aliment_norm) {
            return $key;
        }
    }

    return null;
}

/* Analyse de la requête  */
function analyserRequete($requete)
{
    $requete = trim($requete);

    // Vérification quotes
    if (substr_count($requete, '"') % 2 != 0) {
        return ['erreur' => 'Problème de syntaxe : nombre impair de double-quotes'];
    }

    $souhaites = [];
    $non_souhaites = [];
    $non_rec = [];

    preg_match_all('/"([^"]+)"/iu', $requete, $matches_quotes);
    $entre_quotes = $matches_quotes[1];

    $reste = preg_replace('/"([^"]+)"/iu', '', $requete);
    $mots = preg_split('/\s+/', trim($reste));

    $elements = array_merge($entre_quotes, $mots);

    foreach ($elements as $element) {
        $element = trim($element);
        if ($element === "") continue;

        $prefix = $element[0];
        $alim_brut = ($prefix === '+' || $prefix === '-') ? substr($element, 1) : $element;

        $realKey = findRealKey($alim_brut);

        if ($prefix === '+') {
            if ($realKey) $souhaites[] = $realKey;
            else $non_rec[] = $element;
        } elseif ($prefix === '-') {
            if ($realKey) $non_souhaites[] = $realKey;
            else $non_rec[] = $element;
        } else {
            if ($realKey) $souhaites[] = $realKey;
            else $non_rec[] = $element;
        }
    }

    return [
        'souhaites' => $souhaites,
        'non_souhaites' => $non_souhaites,
        'non_rec' => $non_rec
    ];
}

/* Recherche + calcul du score */
function rechercherCocktails($souhaites, $non_souhaites)
{
    global $Recettes;
    $resultats = [];

    $total_criteres = count($souhaites) + count($non_souhaites);

    foreach ($Recettes as $key => $recette) {

        $score = 0;

        // + souhaités
        foreach ($souhaites as $alim) {
            $desc = GetSelectedFood($alim);
            if (array_intersect($recette['index'], $desc)) {
                $score++;
            }
        }

        // - non souhaités
        $compatible = true;
        foreach ($non_souhaites as $alim) {
            $desc = GetSelectedFood($alim);
            if (array_intersect($recette['index'], $desc)) {
                $compatible = false;
                break;
            }
        }

        if ($compatible) $score += count($non_souhaites);

        if ($total_criteres > 0 && $score > 0) {
            $pourcentage = round(($score / $total_criteres) * 100);

            $resultats[] = [
                'titre' => $recette['titre'],
                'score' => $pourcentage,
                'id' => $key
            ];
        }
    }

    usort($resultats, function ($a, $b) {
        return $b['score'] <=> $a['score'];
    });

    return $resultats;
}

/* Affichage des résultats avec DisplayAdvancedResults, fonction dans le fichier display_functions */

/* Traitement final */
function traiterRequete($requete)
{
    $analyse = analyserRequete($requete);

    if (isset($analyse['erreur'])) {
        return $analyse;
    }

    $souhaites = $analyse['souhaites'];
    $non_souhaites = $analyse['non_souhaites'];

    $total = count($souhaites) + count($non_souhaites);

    if ($total == 0) {
        return ['erreur' =>
            "Problème dans votre requête : recherche impossible"
        ];
    }

    $cocktails = rechercherCocktails($souhaites, $non_souhaites);

    return [
        'souhaites' => $souhaites,
        'non_souhaites' => $non_souhaites,
        'non_rec' => $analyse['non_rec'],
        'cocktails' => $cocktails,
        'approx' => ($total >= 2) // vrai si recherche approximative
    ];
}
?>
