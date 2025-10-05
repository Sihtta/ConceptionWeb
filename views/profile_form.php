<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$errors = $errors ?? [];
$success = $success ?? false;
$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Modifier Profil</title>
</head>

<body>
    <h1>Modifier votre profil</h1>

    <?php if ($success): ?>
        <p style="color:green;">Profil mis Ã jour avec succÃ¨s.</p>
        <p><a href="../index.php">Retour Ã l'accueil</a></p>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <ul style="color:red;">
            <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="post">
        <label for="nom">Nom :</label>
        <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($user['nom'] ?? '') ?>"><br><br>

        <label for="prenom">Prénom :</label>
        <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($user['prenom'] ?? '') ?>"><br><br>

        Vous Ãªtes :
        <input type="radio" id="femme" name="sexe" value="f" <?= ($user['sexe'] ?? '') === 'f' ? 'checked' : '' ?>>
        <label for="femme">une femme</label>
        <input type="radio" id="homme" name="sexe" value="h" <?= ($user['sexe'] ?? '') === 'h' ? 'checked' : '' ?>>
        <label for="homme">un homme</label><br><br>

        <label for="date_naissance">Date de naissance :</label>
        <input type="date" id="date_naissance" name="date_naissance" value="<?= htmlspecialchars($user['date_naissance'] ?? '') ?>"><br><br>

        <input type="submit" value="Valider">
    </form>
</body>

</html>