<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?php echo $chapter->getTitle(); ?></title>
</head>
<body>
<h1><?php echo $chapter->getTitle(); ?></h1>
<img src="<?php echo $chapter->getImage(); ?>" alt="Image de chapitre" style="max-width: 100%; height: auto;">
<p><?php echo $chapter->getDescription(); ?></p>

<?php $choices = $chapter->getChoices(); ?>

<form method="POST" action="/DungeonXplorer/chapter/choice">
    <?php if (!empty($choices)): ?>
        <h2>Choisissez votre chemin :</h2>
        <ul>
            <?php foreach ($choices as $choice): ?>
                <li>
                    <label>
                        <input type="radio" name="choice_id" value="<?php echo $choice['id']; ?>" required>
                        <?php echo $choice['text']; ?>
                    </label>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <button type="submit" name="choice" value="Valider">Valider</button>
</form>
</body>
</html>
