
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php require 'head.php'; ?>
    <title><?= htmlspecialchars($chapter->getTitle() ?? 'Chapitre') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <style>
        body { background: #1b1b1b; color: #f5f5f5; font-family: 'Press Start 2P', cursive; padding: 20px; }
        .chapter-container { max-width: 900px; margin: auto; background: #2e2e2e; border: 3px solid #C4975E; border-radius: 12px; padding: 30px; }
        .chapter-image { width: 100%; border-radius: 8px; margin-bottom: 20px; }
        .choices { display: flex; flex-direction: column; gap: 15px; margin-top: 20px; }
        .choice-button { background: #3a3a3a; border: 2px solid #C4975E; border-radius: 8px; color: #f5f5f5; padding: 15px; text-align: center; cursor: pointer; transition: transform 0.15s, background 0.15s; font-size: 12px; }
        .choice-button:hover { background: #4a4a4a; transform: scale(1.05); }
        h1 { text-align: center; margin-bottom: 20px; color: #C4975E; }
        p.description { line-height: 1.6; font-size: 12px; }
    </style>
</head>
<body>

<div class="chapter-container">
    <h1><?= htmlspecialchars($chapter->getTitle() ?? 'Chapitre') ?></h1>

    <?php if ($chapter->getImage()): ?>
        <img src="/DungeonXplorer/<?= htmlspecialchars($chapter->getImage()) ?>" class="chapter-image" alt="Image chapitre">
    <?php endif; ?>

    <p class="description"><?= nl2br(htmlspecialchars($chapter->getDescription() ?? '')) ?></p>

    <h2>Choisissez votre chemin :</h2>
    <div class="choices">
        <?php foreach ($chapter->getChoices() ?? [] as $choice): ?>
            <?php
            $text = $choice['text'] ?? $choice['description'] ?? 'Continuer';
            $id = $choice['id'] ?? 0;

            // Special case: chapter 20 has two choices about forcing the chest —
            // one for thieves and one for non-thieves. Show only the appropriate
            // button depending on the hero's class.
            $chapterId = $chapter->getId();
            $isVoleurChoice = stripos($text, 'voleur') !== false;
            if ($chapterId === 20) {
                if (isset($hero) && isset($hero->class)) {
                    if ($hero->class === 'Voleur' && !$isVoleurChoice) {
                        continue; // skip non-thief choice for thief
                    }
                    if ($hero->class !== 'Voleur' && $isVoleurChoice) {
                        continue; // skip thief-only choice for non-thief
                    }
                }
            }
            ?>
            <form method="post" action="/DungeonXplorer/chapter/choice">
                <input type="hidden" name="choice_id" value="<?= intval($id) ?>">
                <button type="submit" class="choice-button"><?= htmlspecialchars($text) ?></button>
            </form>
        <?php endforeach; ?>
    </div>
    <div class="mt-4 text-center">
        <form method="post" action="/DungeonXplorer/chapter/quit" style="display:inline-block;">
            <input type="hidden" name="chapter_id" value="<?= (int)$chapter->getId() ?>">
            <button type="submit" class="choice-button" style="background:#8c2b2b; border-color:#6b1f1f;">Quitter et sauvegarder</button>
        </form>
    </div>
</div>

</body>
</html>
