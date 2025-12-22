<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'core/Database.php';
$db = getDB();

// Assurer qu'une potion existe
$db->exec("INSERT IGNORE INTO Items (id, name, description, item_type) VALUES (1, 'Potion de Soin', 'Restaure 20 PV', 'potion')");

$defaultSelected = "img/HeroDefault.png";

$defaultImages = [
    "img/HeroDefault.png",
    "img/HeroRogueM.png",
    "img/HeroRogueF.png",
    "img/HeroMageM.png",
    "img/HeroMageF.png"
];


$classes = $db->query("SELECT * FROM Class")->fetchAll(PDO::FETCH_ASSOC);
$creation_success = '';
$creation_error = '';
$class_success = '';
$class_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_hero') {
    $name = $_POST['hero_name'] ?? '';
    $class_id = $_POST['class_id'] ?? '';
    $biography = $_POST['biography'] ?? '';
    $image_path = $defaultSelected;

    if (!empty($_FILES['hero_image']['name']) && $_FILES['hero_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $image_name = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.\-]/', '_', basename($_FILES['hero_image']['name']));
        $image_path = $uploadDir . $image_name;
        move_uploaded_file($_FILES['hero_image']['tmp_name'], $image_path);
    }
    else if (isset($_POST['selected_default_image']) && !empty($_POST['selected_default_image'])) {
        $sel = $_POST['selected_default_image'];
        if (in_array($sel, $defaultImages, true)) {
            $image_path = $sel;
        } else {
            $image_path = $defaultSelected;
        }
    }


    if (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $image_name = time() . '_' . basename($_FILES['hero_image']['name']);
        $image_path = $uploadDir . $image_name;
        move_uploaded_file($_FILES['hero_image']['tmp_name'], $image_path);
    }

    if (empty($name) || empty($class_id)) {
        $creation_error = "Veuillez remplir tous les champs du héros.";
    } else {
        $stmt = $db->prepare("SELECT * FROM Class WHERE id = :id");
        $stmt->execute(['id' => $class_id]);
        $class = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($class) {
            $stmt = $db->prepare("
                INSERT INTO Hero 
                (account_id, name, class_id, pv, mana, strength, initiative, xp, current_level, biography, image)
                VALUES (:account_id, :name, :class_id, :pv, :mana, :strength, :initiative, 0, 1, :biography, :image)
            ");
            $stmt->execute([
                'account_id' => $_SESSION['id'],
                'name' => $name,
                'class_id' => $class['id'],
                'pv' => $class['base_pv'],
                'mana' => $class['base_mana'],
                'strength' => $class['strength'],
                'initiative' => $class['initiative'],
                'biography' => $biography,
                'image' => $image_path
            ]);

            // Ajouter une potion à l'inventaire
            $heroId = $db->lastInsertId();
            $stmt = $db->prepare("INSERT INTO Inventory (hero_id, item_id, quantity) VALUES (?, 1, 1)");
            $stmt->execute([$heroId]);

            $creation_success = "Héros $name créé avec succès !";
        } else {
            $creation_error = "Classe invalide.";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_class') {
    $class_name = $_POST['class_name'] ?? '';
    $description = $_POST['description'] ?? '';
    $base_pv = $_POST['base_pv'] ?? 0;
    $base_mana = $_POST['base_mana'] ?? 0;
    $strength = $_POST['strength'] ?? 0;
    $initiative = $_POST['initiative'] ?? 0;
    $max_items = $_POST['max_items'] ?? 5;

    if (empty($class_name)) {
        $class_error = "Le nom de la classe est obligatoire.";
    } else {
        $stmt = $db->prepare("
            INSERT INTO Class 
            (name, description, base_pv, base_mana, strength, initiative, max_items)
            VALUES (:name, :description, :pv, :mana, :strength, :initiative, :max_items)
        ");
        $stmt->execute([
            'name' => $class_name,
            'description' => $description,
            'pv' => $base_pv,
            'mana' => $base_mana,
            'strength' => $strength,
            'initiative' => $initiative,
            'max_items' => $max_items
        ]);
        $class_success = "Classe $class_name créée avec succès !";

        $classes = $db->query("SELECT * FROM Class")->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<?php if ($creation_success): ?>
        <div class="alert alert-success mt-3 text-center"><?= $creation_success ?></div>
        <?php header("Location: /DungeonXplorer/account"); exit; ?>
    <?php endif; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php require_once 'head.php'; ?>
    <title>Créer un héros - DungeonXplorer</title>
</head>
<body>
<?php require_once 'navbar.php'; ?>
<div class="login-container">

    <h1 class="login-title mb-4">Créer un héros</h1>

    <form method="post" enctype="multipart/form-data" class="d-flex flex-column align-items-center gap-3 w-100">
        
        <label class="texte-principal">Choisir une image :</label>
        <div class="d-flex flex-wrap justify-content-center gap-3">
            <?php foreach ($defaultImages as $img): 
                $isSelected = ($img === $defaultSelected); ?>
                <label style="cursor: pointer;">
                    <input type="radio" name="selected_default_image" value="<?= htmlspecialchars($img) ?>" hidden <?= $isSelected ? 'checked' : '' ?>>
                    <div class="hero-image-wrapper <?= $isSelected ? 'selected-image' : '' ?>">
                        <img src="<?= htmlspecialchars($img) ?>" class="hero-image-preview" alt="">
                    </div>
                </label>
            <?php endforeach; ?>
        </div>

        <script>
            document.querySelectorAll("input[name='selected_default_image']").forEach(input => {
                input.addEventListener("change", () => {
                    document.querySelectorAll(".hero-image-wrapper").forEach(w => w.classList.remove("selected-image"));
                    const wrapper = input.parentElement.querySelector(".hero-image-wrapper");
                    if(wrapper) wrapper.classList.add("selected-image");
                });
            });
        </script>
        
    
        <input type="hidden" name="action" value="create_hero">

        <div class="input-group w-100">
            <input type="text" name="hero_name" class="form-control background-secondaire texte-principal" placeholder="Nom du héros" required>
        </div>

        <div class="input-group w-100">
            <select name="class_id" class="form-control background-secondaire texte-principal" required>
                <option value="">-- Choisir une classe --</option>
                <?php foreach ($classes as $cls): ?>
                    <option value="<?= $cls['id'] ?>">
                        <?= htmlspecialchars($cls['name']) ?> (PV <?= $cls['base_pv'] ?> | Mana <?= $cls['base_mana'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="input-group w-100">
            <textarea name="biography" class="form-control background-secondaire texte-principal" placeholder="Biographie du héros" rows="4"></textarea>
        </div>

        
        <div class="w-100">
            <label class="texte-principal">Ou importer une image :</label>
            <input type="file" name="hero_image" accept="image/*" class="form-control background-secondaire texte-principal">
        </div>


        <input type="submit" value="Créer le héros" class="btn btn-primary mt-2 w-50">
        


    </form>

    <!-- Messages -->
    

    <?php if ($creation_error): ?>
        <div class="alert alert-danger mt-3 text-center"><?= $creation_error ?></div>
    <?php endif; ?>

    <hr style="border-color: #C4975E; margin: 30px 0;">

    <h1 class="login-title mb-4">Créer une nouvelle classe</h1>

    <form method="post" class="d-flex flex-column align-items-center gap-3 w-100">
        <input type="hidden" name="action" value="create_class">

        <div class="input-group w-100">
            <input type="text" name="class_name" class="form-control background-secondaire texte-principal" placeholder="Nom de la classe" required>
        </div>

        <div class="input-group w-100">
            <textarea name="description" class="form-control background-secondaire texte-principal" placeholder="Description de la classe" rows="3"></textarea>
        </div>

        <div class="d-flex gap-2 w-100">
            <input type="number" name="base_pv" class="form-control background-secondaire texte-principal" placeholder="PV" min="0" required>
            <input type="number" name="base_mana" class="form-control background-secondaire texte-principal" placeholder="Mana" min="0" required>
        </div>

        <div class="d-flex gap-2 w-100">
            <input type="number" name="strength" class="form-control background-secondaire texte-principal" placeholder="Force" min="0" required>
            <input type="number" name="initiative" class="form-control background-secondaire texte-principal" placeholder="Initiative" min="0" required>
        </div>

        <input type="number" name="max_items" class="form-control background-secondaire texte-principal w-100" placeholder="Nombre max d'objets" min="1" value="5">

        <input type="submit" value="Créer la classe" class="btn btn-primary mt-2 w-50">

    </form>
    <a href="account" class="back-btn mt-2 d-flex justify-content-center">Retour</a>

    <?php if (!empty($class_success)): ?>
        <div class="alert alert-success mt-3 text-center"><?= htmlspecialchars($class_success) ?></div>
    <?php endif; ?>
    <?php if (!empty($class_error)): ?>
        <div class="alert alert-danger mt-3 text-center"><?= htmlspecialchars($class_error) ?></div>
    <?php endif; ?>

</div>

</body>
</html>
