    <?php
    require_once __DIR__ . "/../Donnees.inc.php";
    require_once __DIR__ . "/data_functions.php";

    /* -----------------------------------------------------------------------------
    Fonction utilitaire : normalisation lowercase pour comparer sans casse
    ----------------------------------------------------------------------------- */
    function normalize($str)
    {
        $str = trim(mb_strtolower($str, "UTF-8"));
        return $str;
    }

    /* -----------------------------------------------------------------------------
    Trouve un aliment dans la hiérarchie sans tenir compte de la casse
    Retourne la vraie clé utilisée dans $Hierarchie ou null  // renvoie la vraie clé (avec majuscule, accents…)
    ----------------------------------------------------------------------------- */
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

    /* -----------------------------------------------------------------------------
    Analyse la requête avancée de l’utilisateur
    ----------------------------------------------------------------------------- */
    function analyserRequete($requete)
    {
        
        $requete = trim($requete);

        // Vérif quotes
        if (substr_count($requete, '"') % 2 != 0) {
            return array('erreur' => 'Problème de syntaxe : nombre impair de double-quotes');
        }

        $souhaites = array();
        $non_souhaites = array();
        $non_rec = array();

        // Extraire les mots entre guillemets "multi mots" (insensible à la casse et aux accents)
        preg_match_all('/"([^"]+)"/iu', $requete, $matches_quotes);
        $entre_quotes = $matches_quotes[1];

        // Retirer les éléments entre guillemets de la requête principale
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
                if ($realKey !== null) $souhaites[] = $realKey;
                else $non_rec[] = $element;
            } elseif ($prefix === '-') {
                if ($realKey !== null) $non_souhaites[] = $realKey;
                else $non_rec[] = $element;
            } else {
                if ($realKey !== null) $souhaites[] = $realKey;
                else $non_rec[] = $element;
            }
        }

        return array(
            'souhaites' => $souhaites,
            'non_souhaites' => $non_souhaites,
            'non_rec' => $non_rec
        );
    }

    /* -----------------------------------------------------------------------------
    Recherche des cocktails + calcul du score
    ----------------------------------------------------------------------------- */
    function rechercherCocktails($souhaites, $non_souhaites)
    {
        global $Recettes;
        $resultats = array();

        foreach ($Recettes as $key => $recette) {
            $score = 0;
            $total = count($souhaites) + count($non_souhaites);

            // + aliments souhaités
            foreach ($souhaites as $alim) {
                $desc = GetSelectedFood($alim);
                if (count(array_intersect($recette['index'], $desc)) > 0) {
                    $score++;
                }
            }

            // - aliments non souhaités 
            $ok = true;
            foreach ($non_souhaites as $alim) {
                $desc = GetSelectedFood($alim);
                if (count(array_intersect($recette['index'], $desc)) > 0) {
                    $ok = false;
                    break;
                }
            }

            if ($ok) $score += count($non_souhaites);

            if ($total > 0 && $score > 0) {
                $pourcentage = round(($score / $total) * 100);

                $resultats[] = array(
                    'titre' => $recette['titre'],
                    'score' => $pourcentage,
                    'id' => $key
                );
            }
        }

        // Trier par score décroissant
        usort($resultats, function ($a, $b) {
            if ($a['score'] === $b['score']) return 0;
            return ($a['score'] < $b['score']) ? 1 : -1;
        });

        return $resultats;
    }

    /* -----------------------------------------------------------------------------
    Affichage complet d'un résultat de recherche
    ----------------------------------------------------------------------------- */
   function DisplayAdvancedResults($resultats)
{
    global $Recettes;
    $favorites = $_SESSION['favorites'] ?? [];

    if (empty($resultats)) {
        return "<p>Aucun cocktail ne correspond à votre recherche.</p>";
    }

    $output = "";

    foreach ($resultats as $res) {
        $id = $res['id'];
        if (!isset($Recettes[$id])) continue;

        $title = $Recettes[$id]['titre'];
        $img = CocktailImage($title);

        $isFav = in_array($title, $favorites);
        $heartIcon = $isFav 
            ? "<i class='fas fa-heart' style='color:#e74c3c;'></i>" 
            : "<i class='far fa-heart' style='color:#95a5a6;'></i>";

        
        $ingredients = "<ul><li>" . str_replace("|", "</li><li>", $Recettes[$id]['ingredients']) . "</li></ul>";
        $prep = $Recettes[$id]['preparation'];

        $output .= "
        <section class='cocktailCard' data-cocktail-title='" . htmlspecialchars($title, ENT_QUOTES) . "'>
            <div style='display:flex;justify-content:space-between;align-items:center;'>
                <form method='post' style='display:inline;margin:0;'>
                    <input type='hidden' name='selectedCocktail' value='$id'>
                    <button type='submit' style='all:unset;cursor:pointer;'>
                        <h3>$title <span style=\"font-size:14px;color:#888;\">({$res['score']}%)</span></h3>
                    </button>
                </form>
                <form method='post' class='favoriteForm' style='margin:0;'>
                    <input type='hidden' name='cocktail' value='" . htmlspecialchars($title, ENT_QUOTES) . "'>
                    <button type='submit' class='heart-btn' style='background:none;border:none;font-size:24px;cursor:pointer;padding:5px;'>
                        $heartIcon
                    </button>
                </form>
            </div>

            <img src='$img' alt='$title'>
            <h4>Ingrédients :</h4>
            $ingredients
            <h4>Préparation :</h4>
            <p>" . nl2br(htmlspecialchars($prep)) . "</p>
        </section>";
    }

    return $output;
}



    /* -----------------------------------------------------------------------------
    Fonction principale appelée par le formulaire
    ----------------------------------------------------------------------------- */
    function traiterRequete($requete)
    {
        $analyse = analyserRequete($requete);

        if (isset($analyse['erreur'])) {
            return $analyse;
        }

        $souhaites = isset($analyse['souhaites']) ? $analyse['souhaites'] : array();
        $non_souhaites = isset($analyse['non_souhaites']) ? $analyse['non_souhaites'] : array();

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
    ?>
