<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'core/Database.php';

if (!isset($_SESSION['id'])) {
    header("Location: login");
    exit;
}

if (!isset($_GET['id'])) {
    die("Aucune aventure sélectionnée.");
}

$adventureId = intval($_GET['id']);
$accountId = $_SESSION['id'];

$db = getDB();

// Charger aventure + héros associé
$sql = "
SELECT 
    a.*,
    h.name AS hero_name,
    h.image AS hero_image,
    h.pv, h.mana, h.strength, h.initiative,
    h.current_level, h.xp,
    c.name AS class_name
FROM Adventure a
JOIN Hero h ON a.hero_id = h.id
LEFT JOIN Class c ON h.class_id = c.id
WHERE a.id = ?
";
$stmt = $db->prepare($sql);
$stmt->execute([$adventureId]);
$adventure = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$adventure) {
    die("Cette aventure n'existe pas.");
}

// Vérifier que le héros appartient au joueur
$sql = "SELECT id FROM Hero WHERE id = ? AND account_id = ?";
$stmt = $db->prepare($sql);
$stmt->execute([$adventure['hero_id'], $accountId]);
$check = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$check) {
    die("Cette aventure ne vous appartient pas.");
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php require 'head.php'; ?>
    <title>Aventure - DungeonXplorer</title>
    </style>
</head>

<body>

<h1 class="pirata-one-regular texte-principal text-center">Aventure en cours</h1>

<div class="adventure-card">

    <div class="hero-image-wrapper">
        <img src="<?= $adventure['hero_image'] ?: 'img/HeroDefault.png' ?>" alt="">
    </div>

    <h2><?= htmlspecialchars($adventure['hero_name']) ?></h2>
    <p>Classe : <?= htmlspecialchars($adventure['class_name']) ?></p>

    <p>
        PV : <?= $adventure['pv'] ?> — Mana : <?= $adventure['mana'] ?><br>
        Force : <?= $adventure['strength'] ?> — Initiative : <?= $adventure['initiative'] ?>
    </p>

    <p>Niveau : <?= $adventure['current_level'] ?> — XP : <?= $adventure['xp'] ?></p>

    <hr>

    <h3>Chapitre actuel : <?= $adventure['current_chapter_id'] ?></h3>

    <a href="chapter?id=<?= $adventureId ?>" class="btn btn-primary w-100 mt-3">
        Continuer l'aventure
    </a>

    <a href="account" class="back-btn mt-2 d-flex justify-content-center">Retour</a>



</div>

</body>
</html>
