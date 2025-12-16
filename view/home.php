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
    <div>
        <p class="texte-principal">
            <br>
            Bienvenue sur DungeonXplorer, l'univers de dark fantasy où se mêlent aventure, stratégie et immersion
            totale dans les récits interactifs.
            Ce projet est né de la volonté de l’association Les Aventuriers du Val Perdu de raviver l’expérience unique
            des livres dont vous êtes le héros. Notre vision : offrir à la communauté un espace où chacun peut
            incarner un personnage et plonger dans des quêtes épiques et personnalisées.
            Dans sa première version, DungeonXplorer permettra aux joueurs de créer un personnage parmi trois
            classes emblématiques — guerrier, voleur, magicien — et d’évoluer dans un scénario captivant, tout en
            assurant à chacun la possibilité de conserver sa progression.
            Nous sommes enthousiastes de partager avec vous cette application et espérons qu'elle saura vous
            plonger au cœur des mystères du Val Perdu !
        </p>
    </div>
</div>

</body>
</html>
