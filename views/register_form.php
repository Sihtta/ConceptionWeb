<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config.php';

$errors = $errors ?? [];
$success = $success ?? '';
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Inscription</title>
</head>

<body>
    <h1>Inscription</h1>

    <?php if (!empty($errors)): ?>
        <ul style="color:red;">
            <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="post" action="../controllers/register.php">
        <fieldset>
            <legend>Informations personnelles</legend>

            <label for="login">Login :</label>
            <input type="text" id="login" name="login" required value="<?= htmlspecialchars($_POST['login'] ?? '') ?>"><br><br>

            <label for="password">Mot de passe :</label>
            <input type="password" id="password" name="password" required><br><br>

            <label for="nom">Nom :</label>
            <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>"><br><br>

            <label for="prenom">Prénom :</label>
            <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>"><br><br>

            Vous êtes :
            <input type="radio" id="femme" name="sexe" value="f" <?= (($_POST['sexe'] ?? '') === 'f') ? 'checked' : '' ?>>
            <label for="femme">une femme</label>
            <input type="radio" id="homme" name="sexe" value="h" <?= (($_POST['sexe'] ?? '') === 'h') ? 'checked' : '' ?>>
            <label for="homme">un homme</label><br><br>

            <label for="date_naissance">Date de naissance :</label>
            <input type="date" id="date_naissance" name="date_naissance" value="<?= htmlspecialchars($_POST['date_naissance'] ?? '') ?>"><br><br>
        </fieldset>

        <input type="submit" value="Valider">
        <p> Déjà un compte ? <a href="../index.php">Se connecter</a></p>
    </form>
</body>

</html>