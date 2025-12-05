<!DOCTYPE html>
<html lang="fr">
<head>
    <?php require_once 'head.php'; ?>
    <title><?= $chapter->getTitle(); ?></title>
</head>

<body>
<div class="chapter-container">
    <h1><?= $chapter->getTitle(); ?></h1>
    <img class="chapter-image" src="/DungeonXplorer/<?php echo $chapter->getImage(); ?>" alt="Illustration du chapitre">

    <p class="texte-principal">
        <?= $chapter->getDescription(); ?>
    </p>
    <?php $choices = $chapter->getChoices(); ?>
    <form method="POST" action="/DungeonXplorer/chapter/choice">
        <?php if (!empty($choices)): ?>
            <h2>Choisissez votre chemin :</h2>
            <ul class="choice-list">
                <?php foreach ($choices as $choice): ?>
                    <li class="choice-item">
                        <label>
                            <input type="radio" name="choice_id" value="<?= $choice['id']; ?>" required>
                            <?= $choice['text']; ?>
                        </label>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <button class="btn btn-primary w-20" type="submit">Continuer</button>
    </form>

</div>

</body>
</html>
