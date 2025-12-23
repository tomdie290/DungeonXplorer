<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($treasure)) {
    echo 'Aucun trésor chargé.';
    exit;
}
?>
<!doctype html>
<html lang="fr">
<head>
    <?php require_once __DIR__ . '/head.php'; ?>
    <meta charset="UTF-8">
    <title>Modifier le trésor</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>.small-img{max-width:150px;height:auto;object-fit:cover;}</style>
</head>
<body>
<?php require_once __DIR__ . '/navbar.php'; ?>
<div class="container mt-5">
    <h1 class="mb-4">Modifier le trésor #<?php echo htmlspecialchars($treasure['id']); ?></h1>

    <?php if (!empty($_SESSION['flash'])): ?>
        <div class="alert alert-info"><?php echo htmlspecialchars($_SESSION['flash']); ?></div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <div class="card p-4">
        <form method="POST" action="/manage_treasures/update">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($treasure['id']); ?>">
            <div class="mb-2">
                <label class="form-label">Nom</label>
                <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($treasure['name']); ?>">
            </div>
            <div class="mb-2">
                <label class="form-label">Valeur</label>
                <input type="number" name="value" class="form-control" required min="0" value="<?php echo htmlspecialchars($treasure['value']); ?>">
            </div>
            <div class="mb-2">
                <label class="form-label">Description</label>
                <input type="text" name="description" class="form-control" value="<?php echo htmlspecialchars($treasure['description']); ?>">
            </div>
            <div class="mb-2">
                <label class="form-label">Image (optionnel)</label>
                <input type="text" name="image_path" class="form-control" value="<?php echo htmlspecialchars($treasure['image']); ?>">
            </div>
            <div class="text-center mt-3">
                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <a href="/manage_treasures" class="btn btn-secondary ms-2">Annuler</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
