<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'core/Database.php';

$db = getDB();

if (!isset($_SESSION['id'])) {
    header("Location: login");
    exit;
}

$accountId = $_SESSION['id'];


$sql = "
SELECT 
    h.*,
    c.name AS class_name,
    a.id AS adventure_id,
    a.end_date AS adventure_end,
    a.current_chapter_id
FROM Hero h
LEFT JOIN Class c ON h.class_id = c.id
LEFT JOIN Adventure a ON h.id = a.hero_id AND a.end_date IS NULL
WHERE h.account_id = :acc
";

$stmt = $db->prepare($sql);
$stmt->execute(['acc' => $accountId]);
$heroes = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <?php require_once 'head.php'; ?>
        <title>Mon compte - DungeonXplorer</title>
    </head>
    
    <body>
    <?php require_once 'navbar.php'; ?>
        <?php if (!empty($_SESSION['flash'])): ?>
            <div class="container mt-3">
                <div class="alert alert-success"><?= htmlspecialchars($_SESSION['flash']) ?></div>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>
        <div class="container">
        <div>
            <h1 class="login-title mt-5 mb-4">Bienvenue <?= htmlspecialchars($_SESSION['username']) ?> !</h1>
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
        <h2 class="login-title mt-5 mb-4">Liste de mes héros</h2>
        

<div class="container">
    <?php if (empty($heroes)): ?>
        <p class="texte-principal text-center">Aucun héro créé pour l’instant.</p>
    <?php endif; ?>

    <div class="row g-4">
        <?php foreach ($heroes as $hero): ?>
            <div class="col-sm-6 col-md-4 col-lg-3 d-flex">
                <div class="hero-card">
                    <div class="hero-image-wrapper">
                        <img src="<?= $hero['image'] ? htmlspecialchars($hero['image']) : 'img/HeroDefault.png' ?>"
                             alt="Image du héros">
                    </div>

                    <h1><?= htmlspecialchars($hero['name']) ?></h1>
                    <p class="texte-principal">Classe : <strong><?= htmlspecialchars($hero['class_name']) ?></strong></p>

                    <p class="texte-principal">
                        PV : <?= $hero['pv'] ?> — Mana : <?= $hero['mana'] ?><br>
                        Force : <?= $hero['strength'] ?> — Initiative : <?= $hero['initiative'] ?>
                    </p>

                    <p class="texte-principal">
                        Niveau : <?= $hero['current_level'] ?> — XP : <?= $hero['xp'] ?>
                    </p>

                    <?php if (!empty($hero['biography'])): ?>
                        <p class="texte-principal"><em>"<?= nl2br(htmlspecialchars($hero['biography'])) ?>"</em></p>
                    <?php endif; ?>

                    <div class="mt-3">
                        <?php if ($hero['adventure_id']): ?>
                            <div class="alert alert-warning text-center">
                                En aventure (Chapitre <?= $hero['current_chapter_id'] ?>)
                            </div>
                            <a href="adventure?id=<?= $hero['adventure_id'] ?>"
                               class="btn btn-primary w-100">
                                Continuer l'aventure
                            </a>
                        <?php else: ?>
                            <div class="alert alert-success text-center">
                                Disponible — Pas en aventure
                            </div>
                            <a href="start_adventure?hero=<?= $hero['id'] ?>"
                               class="btn btn-primary w-100">
                                Démarrer une aventure
                            </a>
                        <?php endif; ?>
                        </div>

                        <a href="inventory?hero=<?= $hero['id'] ?>" class="btn btn-info w-100 mb-2">Voir inventaire</a>

                        <?php
                        // Compter les types de potions différentes possédées
                        $stmtP = $db->prepare("SELECT COUNT(DISTINCT it.id) FROM Inventory i JOIN Items it ON i.item_id = it.id WHERE i.hero_id = ? AND it.item_type = 'potion'");
                        $stmtP->execute([$hero['id']]);
                        $potionCount = (int)$stmtP->fetchColumn();
                        ?>

                        <form method="POST" action="inventory/add" class="mt-2">
                            <input type="hidden" name="hero_id" value="<?= $hero['id'] ?>">
                            <div class="mb-2">
                                <label for="potion_type_<?= $hero['id'] ?>" class="form-label text-light">Ajouter une potion (max 2 types)</label>
                                <div class="d-flex gap-2">
                                    <?php if ($potionCount >= 2): ?>
                                        <?php
                                            $stmtList = $db->prepare("SELECT it.id, it.name FROM Inventory i JOIN Items it ON i.item_id = it.id WHERE i.hero_id = ? AND it.item_type = 'potion'");
                                            $stmtList->execute([$hero['id']]);
                                            $owned = $stmtList->fetchAll(PDO::FETCH_ASSOC);
                                        ?>
                                        <select name="potion_type" id="potion_type_<?= $hero['id'] ?>" class="form-select">
                                            <?php foreach ($owned as $o): ?>
                                                <option value="<?= (int)$o['id'] ?>"><?= htmlspecialchars($o['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="form-text text-light">Vous possédez déjà ces potions — la quantité ne peut plus être modifiée une fois ajoutée.</div>
                                    <?php else: ?>
                                        <select name="potion_type" id="potion_type_<?= $hero['id'] ?>" class="form-select">
                                            <option value="small_heal">Petite potion de soin (+10 PV)</option>
                                            <option value="big_heal">Grosse potion de soin (+25 PV)</option>
                                            <option value="mana">Potion de mana (+15 Mana)</option>
                                            <option value="strength">Potion de Force (Inflige 20 dégâts)</option>
                                            <option value="power_boost">Elixir de Puissance (+10% dégâts, unique)</option>
                                        </select>
                                        <input type="number" name="quantity" min="1" max="999" value="1" class="form-control" style="width:120px" title="Quantité">
                                    <?php endif; ?>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success w-100">Ajouter</button>
                            <?php if ($potionCount >= 2): ?>
                                <div class="mt-2 alert alert-info text-center">Vous avez déjà deux types de potions différentes.</div>
                            <?php endif; ?>
                        </form>

                        <form method="POST" action="delete_hero" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce héros ? Cette action est irréversible.')" class="mt-2">
                            <input type="hidden" name="hero_id" value="<?= $hero['id'] ?>">
                            <button type="submit" class="btn btn-danger w-100">Supprimer le héros</button>
                        </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>