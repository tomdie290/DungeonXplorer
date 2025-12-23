<!DOCTYPE html>
<html lang="fr">
<head>
    <?php require 'head.php'; ?>
    <title>Choisir une aventure</title>
</head>

<body>

<?php require 'navbar.php'; ?>

<h1 class="text-center mt-4">
    Choisir une aventure pour <?= htmlspecialchars($hero['name']) ?>
</h1>

<div class="container mt-4">
    <div class="row">
        <?php foreach ($adventures as $adv): ?>
            <div class="col-md-4">
                <div class="card mb-3 p-3 bg-dark text-white">

                    <?php if (!empty($adv['image'])): ?>
                        <img src="/DungeonXplorer/<?= htmlspecialchars($adv['image']) ?>"
                             class="img-fluid mb-2" alt="<?= htmlspecialchars($adv['title'] ?? 'Image de l\'aventure') ?>">
                    <?php endif; ?>

                    <h3><?= htmlspecialchars($adv['title']) ?></h3>
                    <p><?= nl2br(htmlspecialchars($adv['description'])) ?></p>

                    <a class="btn btn-primary w-100"
                       href="/DungeonXplorer/start_adventure?hero=<?= $hero['id'] ?>&adventure=<?= $adv['id'] ?>">
                        Commencer l’aventure
                    </a>

                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

</body>
</html>
