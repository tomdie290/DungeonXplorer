<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'core/Database.php';
$db = getDB();

$classes = $db->query("SELECT * FROM Class")->fetchAll(PDO::FETCH_ASSOC);

$creation_error = '';
$class_success = '';
$class_error = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_hero') {

    $name       = $_POST['hero_name'] ?? '';
    $class_id   = $_POST['class_id'] ?? '';
    $biography  = $_POST['biography'] ?? '';
    $gender     = $_POST['gender'] ?? 'H';

    if (empty($name) || empty($class_id)) {
        $creation_error = "Veuillez remplir tous les champs.";
    } else {

        $stmt = $db->prepare("SELECT * FROM Class WHERE id = ?");
        $stmt->execute([$class_id]);
        $class = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$class) {
            $creation_error = "Classe invalide.";
        } else {

            $className  = preg_replace('/[^A-Za-z]/', '', $class['name']);
            $image_path = "img/Hero{$className}{$gender}.png";

            $stmt = $db->prepare("
                INSERT INTO Hero
                (account_id, name, class_id, pv, mana, strength, initiative, xp, current_level, biography, image)
                VALUES (?, ?, ?, ?, ?, ?, ?, 0, 1, ?, ?)
            ");

            $stmt->execute([
                $_SESSION['id'],
                $name,
                $class['id'],
                $class['base_pv'],
                $class['base_mana'],
                $class['strength'],
                $class['initiative'],
                $biography,
                $image_path
            ]);

            $heroId = $db->lastInsertId();
            /* si on veux un potion aux debut
            $db->prepare("
                INSERT INTO Inventory (hero_id, item_id, quantity)
                VALUES (?, 1, 1)
            ")->execute([$heroId]);
            */
            header("Location: /DungeonXplorer/account");
            exit;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_class') {

    if (empty($_POST['class_name'])) {
        $class_error = "Nom de classe obligatoire.";
    } else {

        $db->prepare("
            INSERT INTO Class
            (name, description, base_pv, base_mana, strength, initiative, max_items)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ")->execute([
            $_POST['class_name'],
            $_POST['description'] ?? '',
            $_POST['base_pv'],
            $_POST['base_mana'],
            $_POST['strength'],
            $_POST['initiative'],
            $_POST['max_items'] ?? 5
        ]);

        $class_success = "Classe créée avec succès.";
        $classes = $db->query("SELECT * FROM Class")->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php require_once 'head.php'; ?>
    <title>Créer un héros - DungeonXplorer</title>
</head>

<body>

<div class="login-container">

<h1 class="login-title mb-4">Créer un héros</h1>

<form method="post" class="d-flex flex-column align-items-center gap-3">

    <input type="hidden" name="action" value="create_hero">
    <input type="hidden" name="gender" id="gender" value="H">

    <div class="btn-group">
        <button type="button" id="btn-homme" class="btn btn-primary">Homme</button>
        <button type="button" id="btn-femme" class="btn btn-outline-primary">Femme</button>
    </div>

    <div class="hero-image-wrapper selected-image">
        <img
            id="heroPreview"
            src="img/HeroDefault.png"
            class="hero-image-preview"
            alt="Aperçu du héros">
    </div>

    <input  
        type="text"
        name="hero_name"
        class="form-control"
        placeholder="Nom du héros"
        required>

    <select
        name="class_id"
        id="classSelect"
        class="form-control"
        required>
        <option value="">-- Choisir une classe --</option>
        <?php foreach ($classes as $cls): ?>
            <option
                value="<?= $cls['id'] ?>"
                data-name="<?= htmlspecialchars($cls['name']) ?>">
                <?= htmlspecialchars($cls['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <textarea
        name="biography"
        class="form-control"
        placeholder="Biographie"></textarea>

    <input
        type="submit"
        value="Créer le héros"
        class="btn btn-primary w-50">

</form>

<?php if ($creation_error): ?>
    <div class="alert alert-danger mt-3"><?= $creation_error ?></div>
<?php endif; ?>

<hr>

<h1 class="login-title mb-4">Créer une classe</h1>

<form method="post" class="d-flex flex-column gap-3">

    <input type="hidden" name="action" value="create_class">

    <input type="text" name="class_name" class="form-control" placeholder="Nom" required>
    <textarea name="description" class="form-control" placeholder="Description"></textarea>

    <input type="number" name="base_pv" class="form-control" placeholder="PV" required>
    <input type="number" name="base_mana" class="form-control" placeholder="Mana" required>
    <input type="number" name="strength" class="form-control" placeholder="Force" required>
    <input type="number" name="initiative" class="form-control" placeholder="Initiative" required>
    <input type="number" name="max_items" class="form-control" value="5">

    <input type="submit" value="Créer la classe" class="btn btn-primary w-50">

</form>

<?php if ($class_success): ?>
    <div class="alert alert-success mt-3"><?= $class_success ?></div>
<?php endif; ?>

<?php if ($class_error): ?>
    <div class="alert alert-danger mt-3"><?= $class_error ?></div>
<?php endif; ?>

</div>

<script>
let gender = 'H';

const btnHomme = document.getElementById('btn-homme');
const btnFemme = document.getElementById('btn-femme');
const genderInput = document.getElementById('gender');
const classSelect = document.getElementById('classSelect');
const heroPreview = document.getElementById('heroPreview');

function updateImage() {
    const opt = classSelect.options[classSelect.selectedIndex];
    if (!opt || !opt.dataset.name) return;
    const className = opt.dataset.name.replace(/[^A-Za-z]/g, '');
    heroPreview.src = `img/Hero${className}${gender}.png`;
}

btnHomme.onclick = () => {
    gender = 'H';
    genderInput.value = 'H';
    btnHomme.classList.add('btn-primary');
    btnHomme.classList.remove('btn-outline-primary');
    btnFemme.classList.add('btn-outline-primary');
    btnFemme.classList.remove('btn-primary');
    updateImage();
};

btnFemme.onclick = () => {
    gender = 'F';
    genderInput.value = 'F';
    btnFemme.classList.add('btn-primary');
    btnFemme.classList.remove('btn-outline-primary');
    btnHomme.classList.add('btn-outline-primary');
    btnHomme.classList.remove('btn-primary');
    updateImage();
};

classSelect.onchange = updateImage;
</script>

</body>
</html>
