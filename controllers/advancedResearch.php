<?php
require_once __DIR__ . "/../Donnees.inc.php";
require_once "data_functions.php";

/*
 * Analyse la requête utilisateur pour identifier les aliments souhaités et non souhaités.
 */
function analyserRequete($requete)
{
    global $Hierarchie;

    // Vérifier le nombre de double-quotes
    if (substr_count($requete, '"') % 2 != 0) {
        return array('erreur' => 'Problème de syntaxe : nombre impair de double-quotes');
    }

    $souhaites = array();
    $non_souhaites = array();
    $non_rec = array();

    // Extraire les aliments entre double-quotes
    preg_match_all('/"([^"]+)"/', $requete, $matches_quotes);
    $entre_quotes = $matches_quotes[1];

    // Supprimer les quotes pour analyser le reste
    $requete_sans_quotes = preg_replace('/"([^"]+)"/', '', $requete);
    $mots = preg_split('/\s+/', trim($requete_sans_quotes));

    $elements = array_merge($entre_quotes, $mots);

    foreach ($elements as $element) {
        $element = trim($element);
        if ($element == "") continue;

        $prefix = substr($element, 0, 1);
        if ($prefix == '+') {
            $alim = substr($element, 1);
            if (isset($Hierarchie[$alim])) $souhaites[] = $alim;
            else $non_rec[] = $element;
        } elseif ($prefix == '-') {
            $alim = substr($element, 1);
            if (isset($Hierarchie[$alim])) $non_souhaites[] = $alim;
            else $non_rec[] = $element;
        } else {
            if (isset($Hierarchie[$element])) $souhaites[] = $element;
            else $non_rec[] = $element;
        }
    }

    return array(
        'souhaites' => $souhaites,
        'non_souhaites' => $non_souhaites,
        'non_rec' => $non_rec
    );
}

/**
 * Recherche les cocktails correspondant aux aliments souhaités et non souhaités.
 * Calcule un score pour la satisfaction de la requête.
 */
function rechercherCocktails($souhaites, $non_souhaites)
{
    global $Recettes;
    $resultats = array();

    foreach ($Recettes as $key => $recette) {
        $score = 0;
        $total_criteres = count($souhaites) + count($non_souhaites);

        // Aliments souhaités
        foreach ($souhaites as $alim) {
            $descendants = GetSelectedFood($alim); // inclus l'aliment lui-même
            if (count(array_intersect($recette['index'], $descendants)) > 0) {
                $score++;
            }
        }

        // Aliments non souhaités
        $alim_non_present = true;
        foreach ($non_souhaites as $alim) {
            $descendants = GetSelectedFood($alim);
            if (count(array_intersect($recette['index'], $descendants)) > 0) {
                $alim_non_present = false; // contient un aliment non souhaité
                break;
            }
        }
        if ($alim_non_present) $score += count($non_souhaites);

        if ($score > 0 && $total_criteres > 0) {
            $pourcentage = round(($score / $total_criteres) * 100);
            $resultats[] = array(
                'titre' => $recette['titre'],
                'score' => $pourcentage
            );
        }
    }

    // Trier par score décroissant
    usort($resultats, function ($a, $b) {
        return $b['score'] - $a['score'];
    });

    return $resultats;
}

/**
 * Fonction principale qui reçoit la requête utilisateur et renvoie le résultat complet
 */
function traiterRequete($requete)
{
    $analyse = analyserRequete($requete);
    if (isset($analyse['erreur'])) {
        return $analyse;
    }

    $souhaites = $analyse['souhaites'];
    $non_souhaites = $analyse['non_souhaites'];

    $cocktails = array();
    if (count($souhaites) + count($non_souhaites) > 0) {
        $cocktails = rechercherCocktails($souhaites, $non_souhaites);
    }

    return array(
        'souhaites' => $souhaites,
        'non_souhaites' => $non_souhaites,
        'non_rec' => $analyse['non_rec'],
        'cocktails' => $cocktails
    );
}
