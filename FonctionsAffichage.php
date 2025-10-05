<?php
    require_once "FonctionsDonnees.php";

    /* Affichage du menu de recherche */
    function afficherChemin() {
        $renvoie = "";
        foreach($_SESSION['chemin'] as $aliment) {
            $renvoie .= "
                <form method='post' style='display:inline;'>
                    <input type='hidden' name='aliment' value='$aliment'>
                    <button type='submit' class='boutonLien'>$aliment</button>
                </form>
            ";
            if ($aliment !== end($_SESSION['chemin'])) {
                $renvoie .= "<span class='separateurChemin'>/</span>";
            }
        }
        return $renvoie;
    }

    function afficherSousCategories() {
        $renvoie = "";
        foreach(GetSousCategories($_SESSION['alimentActuel']) as $sousAliment) {
            $renvoie .= "
                <form method='post' style='display:inline;'>
                    <input type='hidden' name='aliment' value='$sousAliment'>
                    <button type='submit' class='boutonLien'>$sousAliment</button>
                </form><br>
            ";
        }
        return $renvoie;
    }

    /* Affichage des Cocktails */
    function afficherCocktailsSimple() {
        $listeCocktails = RecupererCocktails();
        global $Recettes;

        $renvoie = "";
        $titre = "";
        $img = "";
        foreach($listeCocktails as $value) {
            $titre = $Recettes[$value]['titre'];
            $img = ImageCocktail($titre);

            $renvoie .= "
                <section class='carteCocktail'>
                    <form method='post' style='display:inline;'>
                        <input type='hidden' name='cocktailChoisi' value='$value'>
                        <button type='submit' style='all:unset;cursor:pointer;'>
                            <h3>$titre</h3>
                        </button>
                    </form>
                    <img src='$img' alt='$titre'>
                    <ul>
            ";

            foreach($Recettes[$value]['index'] as $aliment) {
                $renvoie .= "<li>$aliment</li>";
            }

            $renvoie .= "
                </ul>
                </section>
            ";
        }
        return $renvoie;
    }

    function afficherCocktailsChoisi() {
        global $Recettes;

        $titre = $Recettes[$_POST['cocktailChoisi']]['titre'];
        $img = ImageCocktail($titre);
        $listeIng = "<ul><li>";
        $listeIng .= $Recettes[$_POST['cocktailChoisi']]['ingredients'];
        $listeIng = str_replace('|', '</li><li>', $listeIng);
        $listeIng .= "</li></ul>";
        $preparation = $Recettes[$_POST['cocktailChoisi']]['preparation'];

        $renvoie = "
            <section class='carteCocktail'>
                <h3>$titre</h3>
                <img src='$img' alt='$titre'>
                <h4>Liste des ingrédients :</h4>
                <p>$listeIng</p>
                <h4>Préparation :</h4>
                <p>$preparation</p>
            </section>
        ";
        
        return $renvoie;
    }
?>
<link rel="stylesheet" href="style.css">