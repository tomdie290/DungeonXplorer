<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once 'models/Hero.php';
require_once 'models/Monster.php';

if (!isset($_SESSION['hero_id'])) die("Erreur : héros non sélectionné");

$hero = Hero::loadById($_SESSION['hero_id']);
if (!$hero) die("Erreur : héros introuvable");

if (!isset($monster)) die("Erreur : monstre introuvable"); 
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php require 'head.php'; ?>
    <title>Combat</title>
</head>
<body>
<div class="container mt-4">
    <h1 class="text-center texte-principal text-white">⚔️ Combat</h1>

    <div class="row mt-4 text-white">

        <div class="col-md-6 text-center">
            <h2><?= htmlspecialchars($hero->name ?? 'Héros') ?></h2>
            <img
                src="/DungeonXplorer/<?= htmlspecialchars($hero->image ?? 'img/HeroDefault.png') ?>"
                width="200"
                alt="Héros"
                class="mb-3"
            >

            <p>
                PV :
                <span id="hero-pv"><?= (int)$hero->pv ?></span> /
                <span id="hero-pv-max"><?= (int)$hero->pv_max ?></span>
            </p>

            <p>
                Mana :
                <span id="hero-mana"><?= (int)$hero->mana ?></span> /
                <span id="hero-mana-max"><?= (int)$hero->mana_max ?></span>
            </p>

            <span id="hero-strength" hidden><?= (int)$hero->strength ?></span>
            <span id="hero-initiative" hidden><?= (int)$hero->initiative ?></span>
        </div>

        <div class="col-md-6 text-center">
            <h2><?= htmlspecialchars($monster->getName()) ?></h2>
            <img
                src="/DungeonXplorer/<?= htmlspecialchars($monster->getImage()) ?>"
                width="200"
                alt="Monstre"
                class="mb-3"
            >

            <p>
                PV :
                <span id="monster-pv"><?= (int)$monster->getHp() ?></span>
            </p>

            <span id="monster-strength" hidden><?= (int)$monster->getStrength() ?></span>
        </div>
    </div>

    <div class="text-center mt-4">
        <button id="btn-attack" class="btn btn-danger btn-lg mx-1">
            ⚔️ Attaquer
        </button>

        <?php if ($hero->class === 'Magicien'): ?>
        <button id="btn-magic" class="btn btn-primary btn-lg mx-1">
            ✨ Magie
        </button>
        <?php endif; ?>

        <button id="btn-potion" class="btn btn-success btn-lg mx-1">
            🧪 Potion
        </button>
    </div>

    <div class="text-center mt-2">
        <form id="quit-form" method="post" action="/DungeonXplorer/chapter/quit" style="display:inline-block;">
            <input type="hidden" name="chapter_id" value="<?= (int)$chapterId ?>">
            <input type="hidden" name="hero_pv" id="quit-hero-pv" value="0">
            <input type="hidden" name="hero_mana" id="quit-hero-mana" value="0">
            <input type="hidden" name="monster_id" id="quit-monster-id" value="0">
            <input type="hidden" name="monster_pv" id="quit-monster-pv" value="0">
            <input type="hidden" name="hero_turn" id="quit-hero-turn" value="0">
            <button type="submit" class="btn btn-warning btn-lg mx-1">Quitter et sauvegarder</button>
        </form>
    </div>

    <div id="post-combat-actions" class="text-center mt-3"></div>


    <div
        id="combat-log"
        class="mt-4 p-3 bg-black text-white rounded"
        style="height:200px; overflow-y:auto;"
    ></div>
</div>

<script>
    const HERO_ID = <?= (int)$hero->id ?>;
    const MONSTER_ID = <?= (int)$monster->getId() ?>;
    const CHAPTER_ID = <?= (int)$chapterId ?>;
    const HERO_CLASS = '<?= addslashes($hero->class) ?>';
    const NEXT_CHAPTER_ID = <?= isset($nextChapterId) && $nextChapterId !== null ? (int)$nextChapterId : 'null' ?>;
    const NEXT_LINK_ID = <?= isset($nextLinkId) && $nextLinkId !== null ? (int)$nextLinkId : 'null' ?>;
    const NEXT_LINK_TEXT = <?= isset($nextLinkText) && $nextLinkText !== null ? json_encode($nextLinkText) : 'null' ?>;
    const DEATH_CHAPTER_ID = <?= isset($deathChapterId) && $deathChapterId !== null ? (int)$deathChapterId : 'null' ?>;
    const DEATH_LINK_ID = <?= isset($deathLinkId) && $deathLinkId !== null ? (int)$deathLinkId : 'null' ?>;
    const DEATH_LINK_TEXT = <?= isset($deathLinkText) && $deathLinkText !== null ? json_encode($deathLinkText) : 'null' ?>;
    const RESUME_COMBAT = <?= isset($heroTurnResume) && $heroTurnResume !== null ? 'true' : 'false' ?>;
    const HERO_TURN_RESUME = <?= isset($heroTurnResume) && $heroTurnResume ? 'true' : 'false' ?>;
    var HERO_DAMAGE_BOOST = <?= isset($heroDamageBoost) ? (int)$heroDamageBoost : 0 ?>;
</script>

<script src="/DungeonXplorer/js/combat.js"></script>
</body>
</html>
