<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($monster)) {
    echo 'Aucun monstre chargé.';
    exit;
}
?>

<!doctype html>
<html lang="fr">
<head>
    <?php require_once __DIR__ . '/head.php'; ?>
    <meta charset="UTF-8">
    <title>Modifier le monstre</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
</head>
<body>
<?php require_once __DIR__ . '/navbar.php'; ?>
<div class="container mt-5">
    <h1 class="mb-4">Modifier le monstre #<?php echo htmlspecialchars($monster['id']); ?></h1>

    <?php if (!empty($_SESSION['flash'])): ?>
        <div class="alert alert-info"><?php echo htmlspecialchars($_SESSION['flash']); ?></div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <div class="card p-4 background-secondaire texte-principal">
        <form method="POST" action="/manage_monsters/update" class="d-flex flex-column gap-3">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($monster['id']); ?>">

            <div class="row g-2">
                <div class="col-md-6">
                    <label class="form-label">Nom</label>
                    <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($monster['name']); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">PV</label>
                    <input type="number" name="pv" class="form-control" required min="1" value="<?php echo htmlspecialchars($monster['pv']); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Mana</label>
                    <input type="number" name="mana" class="form-control" min="0" value="<?php echo htmlspecialchars($monster['mana']); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Force</label>
                    <input type="number" name="strength" class="form-control" required min="1" value="<?php echo htmlspecialchars($monster['strength']); ?>">
                </div>
            </div>

            <div class="row g-2">
                <div class="col-md-6">
                    <label class="form-label">Initiative</label>
                    <input type="number" name="initiative" class="form-control" required min="1" value="<?php echo htmlspecialchars($monster['initiative']); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">XP récompense</label>
                    <input type="number" name="xp_reward" class="form-control" required min="0" value="<?php echo htmlspecialchars($monster['xp_reward']); ?>">
                </div>
            </div>

            <div class="input-group mt-2">
                <label class="form-label">Texte d'attaque</label>
                <input type="text" name="attack_text" class="form-control" value="<?php echo htmlspecialchars($monster['attack_text'] ?? ''); ?>">
            </div>

            <div class="input-group flex-column align-items-start mt-2">
                <label class="input-group-text mb-2">Image</label>
                <?php
                $imageOptions = [];
                $imgDir = __DIR__ . '/../img';
                if (is_dir($imgDir)) {
                    $files = glob($imgDir . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
                    foreach ($files as $f) {
                        $imageOptions[] = basename($f);
                    }
                }
                $currentImage = $monster['image'] ?? '';
                $currentFilename = !empty($currentImage) ? basename($currentImage) : '';
                ?>
                <select name="image_path" class="form-select mt-2" id="imageSelector">
                    <option value="">Aucune image</option>
                    <?php foreach ($imageOptions as $opt):
                        $sel = ($currentFilename === $opt) ? ' selected' : '';
                    ?>
                        <option value="<?php echo htmlspecialchars($opt); ?>"<?php echo $sel; ?>><?php echo htmlspecialchars($opt); ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="mt-3" id="imagePreview">
                    <?php if (!empty($currentImage)): ?>
                        <img src="<?php echo "../" . htmlspecialchars($currentImage); ?>" alt="Aperçu" style="max-width:150px; height:auto; border:1px solid #ccc; padding:5px;">
                    <?php endif; ?>
                </div>
            </div>

            <div class="text-center mt-3">
                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <a href="/manage_monsters" class="btn btn-secondary ms-2">Annuler</a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('imageSelector').addEventListener('change', function() {
    const preview = document.getElementById('imagePreview');
    const filename = this.value;
    
    if (!filename) {
        preview.innerHTML = '';
        return;
    }
    
    const imgPath = '../img/' + encodeURIComponent(filename);
    preview.innerHTML = '<img src="' + imgPath + '" alt="Aperçu" style="max-width:150px; height:auto; border:1px solid #ccc; padding:5px;">';
});
</script>

</body>
</html>
