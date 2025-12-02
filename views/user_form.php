<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config.php';

// Vérifie si on est sur la page d'inscription ou de profil
$isRegister = false;
if (!isset($_SESSION['user'])) {
    $isRegister = true;
} else {
    $isRegister = basename($_SERVER['SCRIPT_NAME']) === 'register.php';
}

// Détermine l'action du formulaire et le titre de la page
$action = $isRegister ? '../controllers/register.php' : '../controllers/profile.php';
$title = $isRegister ? 'Inscription' : 'Modifier votre profil';

// Initialisation
$errors = isset($errors) ? $errors : [];
$success = isset($success) ? $success : false;
$data = [];

// Préremplissage des champs selon le contexte
if ($isRegister) {
    $data = [
        'login' => isset($_POST['login']) ? $_POST['login'] : '',
        'nom' => isset($_POST['nom']) ? $_POST['nom'] : '',
        'prenom' => isset($_POST['prenom']) ? $_POST['prenom'] : '',
        'sexe' => isset($_POST['sexe']) ? $_POST['sexe'] : '',
        'date_naissance' => isset($_POST['date_naissance']) ? $_POST['date_naissance'] : ''
    ];
} else {
    $data = $_SESSION['user'] ?? [];
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="stylesheet" href="../css/style.css?v=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <?php include __DIR__ . '/../navbar.php'; ?>

    <div style="max-width:600px;margin:50px auto;padding:20px;background:#fff;border-radius:8px;box-shadow:0 2px 6px rgba(0,0,0,0.08);">
        <h1><?= htmlspecialchars($title) ?></h1>

        <?php if ($success && !$isRegister): ?>
            <p style="color:green;">Profil mis à jour avec succès.</p>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <ul style="color:red;">
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form method="post" action="<?= htmlspecialchars($action) ?>">
            <input type="hidden" name="user_form_submit" value="1">

            <fieldset>
                <legend>Informations personnelles</legend>

                <?php if ($isRegister): ?>
                    <label for="login">Login :</label>
                    <input type="text" id="login" name="login" required value="<?= htmlspecialchars($data['login']) ?>"><br><br>

                    <label for="password">Mot de passe :</label>
                    <input type="password" id="password" name="password" required><br><br>
                <?php else: ?>
                    <p><strong>Login :</strong> <?= isset($data['login']) ? htmlspecialchars($data['login']) : '' ?></p>
                <?php endif; ?>

                <label for="nom">Nom :</label>
                <input type="text" id="nom" name="nom" value="<?= isset($data['nom']) ? htmlspecialchars($data['nom']) : '' ?>"><br><br>

                <label for="prenom">Prénom :</label>
                <input type="text" id="prenom" name="prenom" value="<?= isset($data['prenom']) ? htmlspecialchars($data['prenom']) : '' ?>"><br><br>

                Vous êtes :
                <input type="radio" id="femme" name="sexe" value="f" <?= (isset($data['sexe']) && $data['sexe'] === 'f') ? 'checked' : '' ?>>
                <label for="femme">une femme</label>
                <input type="radio" id="homme" name="sexe" value="h" <?= (isset($data['sexe']) && $data['sexe'] === 'h') ? 'checked' : '' ?>>
                <label for="homme">un homme</label><br><br>

                <label for="date_naissance">Date de naissance :</label>
                <input type="date" id="date_naissance" name="date_naissance" value="<?= isset($data['date_naissance']) ? htmlspecialchars($data['date_naissance']) : '' ?>"><br><br>
            </fieldset>

            <input type="submit" value="<?= $isRegister ? 'Valider' : 'Mettre à jour' ?>">

            <?php if ($isRegister): ?>
                <p>Déjà un compte ? <a href="../index.php">Se connecter</a></p>
            <?php endif; ?>
            <?php if (!$isRegister): ?>
                <p><a href="../index.php">Retour à l'accueil</a></p>
            <?php endif; ?>
        </form>
    </div>
</body>

</html>