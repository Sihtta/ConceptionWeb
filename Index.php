<?php
    require_once "FonctionsDonnees.php";
    require_once "FonctionsAffichage.php";

    session_start();

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['navigation'])) {
        if ($_POST['navigation'] === "navigation") {
            BoutonNavigation();
        }
    } else {
        DefinirAlimentActuel();
    }
?>
<!DOCTYPE html>
<html>

<head>
      <title>Gestion de cocktails</title>
	  <meta charset="utf-8" />
      <link rel="stylesheet" href="./css/Style.css">
</head>

<body>
    <form method="post">
        <input type="hidden" name="navigation" value="navigation">
        <input type="submit" value="navigation">
    </form>

    <div class="PageContenu">

        <aside class="MenuAliments">
            <h2>Aliment courant</h2>
            <form method="post" action="#" >
                    <?php echo afficherChemin();?>

                <p>Sous-catégories :</p>
                    <?php echo afficherSousCategories();?>
            </form>
        </aside>

        <main class="ContenuPrincipal">
            <?php if(isset($_POST['cocktailChoisi'])):?>
                <h2>Cocktail sélectionné :</h2>
                <?php echo afficherCocktailsChoisi(); ?>
            <?php else: ?>
                <h2>Liste des cocktails</h2>
                <div class="ListeCocktails">
                    <?php echo afficherCocktailsSimple(); ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>