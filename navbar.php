<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$currentPage = basename($_SERVER['SCRIPT_NAME']);

// Détection du chemin : si on est dans controllers/ ou views/, on remonte d'un niveau
$isInSubfolder = (strpos($_SERVER['SCRIPT_NAME'], '/controllers/') !== false ||
    strpos($_SERVER['SCRIPT_NAME'], '/views/') !== false);

$indexPath = $isInSubfolder ? '../index.php' : 'index.php';
$profilePath = $isInSubfolder ? './profile.php' : './controllers/profile.php';
$registerPath = $isInSubfolder ? './register.php' : './controllers/register.php';
$logoutPath = $isInSubfolder ? '../logout.php' : './logout.php';
?>

<header class="navbar">
    <div class="nav-left">
        <form method="post" action="<?= $indexPath ?>">
            <input type="hidden" name="navigation" value="navigation">
            <input type="submit" value="Navigation">
        </form>

        <form method="post" action="<?= $indexPath ?>">
            <input type="hidden" name="showFavorites" value="1">
            <button type="submit">
                Recettes <i class="fas fa-heart" style="color:#e74c3c;"></i>
            </button>
        </form>

        <div class="search-container">
            <form method="post" action="<?= $indexPath ?>" style="display:flex;align-items:center;gap:5px;">
                <label for="requete">Recherche :</label>
                <input type="text" id="requete" name="requete"
                    placeholder="Ex : citron + rhum -sucre"
                    value="<?php echo isset($_POST['requete']) ? htmlspecialchars($_POST['requete']) : ''; ?>">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>
    </div>

    <div class="nav-right zone-connexion">
        <?php if (isset($_SESSION['user'])): ?>
            <span><?= htmlspecialchars($_SESSION['user']['login']) ?></span>
            <a href="<?= $profilePath ?>">Profil</a>
            <span>|</span>
            <form method="post" action="<?= $logoutPath ?>">
                <input type="submit" value="Se déconnecter">
            </form>
        <?php else: ?>
            <?php if (!empty($loginError)): ?>
                <p style="color:red;"><?= htmlspecialchars($loginError) ?></p>
            <?php endif; ?>
            <form method="post" action="<?= $indexPath ?>">
                <label for="login">Login :</label>
                <input type="text" id="login" name="login" required>

                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required>

                <input type="submit" value="Connexion">
                <?php if ($currentPage !== 'register.php'): ?>
                    <span><a href="<?= $registerPath ?>">S'inscrire</a></span>
                <?php endif; ?>
            </form>
        <?php endif; ?>
    </div>
</header>