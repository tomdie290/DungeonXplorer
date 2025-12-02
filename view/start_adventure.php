<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'core/Database.php';

if (!isset($_SESSION['id'])) {
    header("Location: login");
    exit;
}

$db = getDB();

// ---- 1) Vérifier héros ----
if (!isset($_GET['hero'])) {
    die("Aucun héros n'a été sélectionné.");
}

$heroId = intval($_GET['hero']);
$accountId = $_SESSION['id'];

// Vérifier que le héros appartient au compte
$sql = "SELECT * FROM Hero WHERE id = ? AND account_id = ?";
$stmt = $db->prepare($sql);
$stmt->execute([$heroId, $accountId]);
$hero = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$hero) {
    die("Héros introuvable ou ne vous appartient pas.");
}

// ---- 2) Mode "création d'aventure" ----
if (isset($_GET['adventure'])) {

    $startChapter = intval($_GET['adventure']);

    // Vérifier que le chapitre existe
    $stmt = $db->prepare("SELECT id FROM Chapter WHERE id = ?");
    $stmt->execute([$startChapter]);
    if (!$stmt->fetch()) {
        die("Aventure inconnue.");
    }

    // Vérifier que le héros n'a pas déjà une aventure en cours
    $stmt = $db->prepare("SELECT id FROM Adventure WHERE hero_id = ? AND end_date IS NULL");
    $stmt->execute([$heroId]);
    if ($stmt->fetch()) {
        die("Ce héros est déjà en aventure !");
    }

    // Créer aventure
    $sql = "INSERT INTO Adventure (hero_id, current_chapter_id) VALUES (?, ?)";
    $stmt = $db->prepare($sql);
    $stmt->execute([$heroId, $startChapter]);

    $adventureId = $db->lastInsertId();

    // Mise à jour du héros actif dans le compte
    $sql = "UPDATE Account SET current_hero = ? WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$heroId, $accountId]);

    header("Location: adventure?id=$adventureId");
    exit;
}

$sql = "
SELECT c.id, c.title, c.description, c.image
FROM Chapter c
WHERE c.id IN (1)  -- si un jour tu veux plusieurs aventures, ajoute les ID ici
";

$adventures = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php require 'head.php'; ?>
    <title>Choisir une aventure</title>

    <style>
        .adventure-card {
            background: #2e2e2e;
            padding: 20px;
            border-radius: 14px;
            color: white;
            border: 2px solid #C4975E;
            transition: 0.2s;
        }

        .adventure-card:hover {
            transform: scale(1.03);
        }

        .adventure-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 12px;
        }
    </style>
</head>

<body>

<h1 class="pirata-one-regular texte-principal text-center mt-3">
    Choisir une aventure pour : <?= htmlspecialchars($hero['name']) ?>
</h1>

<div class="container mt-4">
    <div class="row g-4">

        <?php foreach ($adventures as $adv): ?>
            <div class="col-md-4">
                <div class="adventure-card">
                    <img src="<?= htmlspecialchars($adv['image']) ?>" class="adventure-image">

                    <h2 class="texte-principal"><?= htmlspecialchars($adv['title']) ?></h2>

                    <p><?= nl2br(htmlspecialchars($adv['description'])) ?></p>

                    <a href="start_adventure?hero=<?= $heroId ?>&adventure=<?= $adv['id'] ?>"
                       class="btn btn-primary w-100 mt-2">
                        Commencer cette aventure
                    </a>
                </div>
            </div>
        <?php endforeach; ?>

    </div>
</div>

</body>
</html>
