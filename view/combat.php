<!DOCTYPE html>
<html lang="fr">
<head>
    <?php require_once 'head.php'; ?>
    <meta charset="UTF-8">
    <title>Combat contre <?= $monster->getName(); ?></title>

</head>
<body>

<div class="combat-container">

    <h1>Combat contre <?= $monster->getName(); ?></h1>

    <?php
    if (!isset($_SESSION['hero'])) {
        $_SESSION['hero'] = [
            'name' => "Héros",
            'hp'   => 100,
            'mana' => 30,
            'atk'  => 15
        ];
    }

    $hero = &$_SESSION['hero'];
    ?>

    <div class="section">
        <h2><?= $hero['name']; ?></h2>
        <div class="stats">
            ❤️ PV : <?= $hero['hp']; ?><br>
            🔷 Mana : <?= $hero['mana']; ?>
        </div>
    </div>

    <div class="section">
        <h2><?= $monster->getName(); ?></h2>
        <div class="stats">
            ❤️ PV : <?= $monster->getHealth(); ?><br>
            🔷 Mana : <?= $monster->getMana(); ?>
        </div>
    </div>

    <?php
    $log = [];

    if ($_SERVER["REQUEST_METHOD"] === "POST") {

        $damage = $hero['atk'];
        $log[] = "Vous attaquez et infligez $damage dégâts.";

        $monster->takeDamage($damage);

        if (!$monster->isAlive()) {
            $log[] = "Vous avez vaincu le " . $monster->getName() . " !";

            $xp = $monster->getExperienceValue();
            $gold = $monster->getTreasure()['gold'];

            $log[] = "Vous gagnez $xp XP et $gold or.";

            echo "<div class='victory-box'>";
            foreach ($log as $l) echo "<p>$l</p>";
            echo "</div>";

            echo "<p><a class='next-link' href='/DungeonXplorer/chapter/show/" . ($chapterId+1) . "'>➡ Continuer l'aventure</a></p>";

            exit;
        }

        $monsterDamage = rand(5, 12);
        $hero['hp'] -= $monsterDamage;

        $log[] = $monster->attack();
        $log[] = "Vous recevez $monsterDamage dégâts.";

        if ($hero['hp'] <= 0) {
            echo "<div class='victory-box' style='background:#4d1111;'>";
            echo "<h2>Vous êtes mort...</h2>";
            echo "<p><a class='next-link' href='/DungeonXplorer/chapter'>Recommencer</a></p>";
            echo "</div>";
            exit;
        }
    }
    ?>

    <div class="section">
        <h3>Journal du combat</h3>
        <div class="log-box">
            <?php
            if (!empty($log)) {
                foreach ($log as $line) {
                    echo "<p>• $line</p>";
                }
            } else {
                echo "<p>Le combat commence !</p>";
            }
            ?>
        </div>
    </div>

    <form method="POST">
        <button class="button-attack" type="submit">Attaquer</button>
    </form>

</div>

</body>
</html>
