<!doctype html>
<html lang="fr">
<head>
    <?php require_once 'head.php'; ?>
    <meta charset="UTF-8">
    <title>DungeonXplorer - Home</title>
    <link rel="stylesheet" href="styles/styles.css">
</head>

<body>

<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<?php if (!empty($_SESSION['flash'])): ?>
    <div class="container mt-3">
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['flash']) ?></div>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<div class="login-container">
    <img class="mb d-block mx-auto" src="img/LogoVide.png" alt="logo application" width="400" height="400">
    <div class="d-flex justify-content-center gap-3 mt-4">
        <a href="login" class="btn btn-primary w-50">Se connecter</a>
        <a href="register" class="btn btn-primary w-50">Créer un compte</a>
    </div>
</div>

</body>
</html>
