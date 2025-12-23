<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$treasures = $treasures ?? [];
?>
<!doctype html>
<html lang="fr">
<head>
    <?php require_once __DIR__ . '/head.php'; ?>
    <meta charset="UTF-8">
    <title>Gestion des trésors</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style> .small-img{max-width:50px;height:50px;object-fit:cover;} </style>
</head>
<body>
<?php require_once __DIR__ . '/navbar.php'; ?>
<div class="container mt-5">
    <h1 class="mb-4">Gestion des trésors</h1>

    <?php if (!empty($_SESSION['flash'])): ?>
        <div class="alert alert-info"><?php echo htmlspecialchars($_SESSION['flash']); ?></div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <div class="table-responsive mb-4">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Valeur</th>
                    <th>Description</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($treasures as $t): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($t['id']); ?></td>
                        <td><?php echo htmlspecialchars($t['name']); ?></td>
                        <td><?php echo htmlspecialchars($t['value']); ?></td>
                        <td><?php echo htmlspecialchars($t['description']); ?></td>
                        <td>
                            <?php if (!empty($t['image'])): ?>
                                <img src="/DungeonXplorer/<?php echo htmlspecialchars($t['image']); ?>" class="small-img" alt="">
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="/manage_treasures/edit?id=<?php echo urlencode($t['id']); ?>" class="btn btn-sm btn-primary">Modifier</a>
                            <form method="POST" action="/manage_treasures/delete" style="display:inline-block;" onsubmit="return confirm('Supprimer ce trésor ?');">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($t['id']); ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card p-4">
        <h3>Ajouter un trésor</h3>
        <form method="POST" action="/manage_treasures/store">
            <div class="mb-2">
                <label class="form-label">Nom</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-2">
                <label class="form-label">Valeur</label>
                <input type="number" name="value" class="form-control" required min="0" value="0">
            </div>
            <div class="mb-2">
                <label class="form-label">Description</label>
                <input type="text" name="description" class="form-control">
            </div>
            <div class="mb-2">
                <label class="form-label">Image (optionnel)</label>
                <input type="text" name="image_path" class="form-control" placeholder="img/nom.jpg">
            </div>
            <div class="text-center mt-3">
                <button type="submit" class="btn btn-primary">Ajouter</button>
            </div>
        </form>
    </div>

    <div class="mt-3">
        <a href="/admin" class="btn btn-secondary">Retour</a>
    </div>
</div>
</body>
</html>
