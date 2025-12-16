<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'core/Database.php';

if (!isset($_SESSION['id'])) {
    header("Location: login");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php require_once 'head.php'; ?>
    <title>Inventaire de <?= htmlspecialchars($hero['name']) ?> - DungeonXplorer</title>
</head>
<?php require_once 'navbar.php'; ?>
<body>
    <h2 class="login-title mt-5 mb-4">Inventaire de <?= htmlspecialchars($hero['name']) ?></h2>

    <div class="container">
        <?php if (empty($inventory)): ?>
            <p class="texte-principal text-center">L'inventaire est vide.</p>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($inventory as $item): ?>
                    <div class="col-sm-6 col-md-4 col-lg-3 d-flex">
                        <div class="hero-card">
                            <h1><?= htmlspecialchars($item['name']) ?></h1>
                            <p class="texte-principal">Type : <strong><?= htmlspecialchars($item['item_type']) ?></strong></p>
                            <p class="texte-principal">Quantité : <strong><?= $item['quantity'] ?></strong></p>
                            <p class="texte-principal"><em><?= htmlspecialchars($item['description']) ?></em></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="text-center mt-4">
        <a href="account" class="btn btn-secondary">Retour au compte</a>
    </div>
</body>
</html>