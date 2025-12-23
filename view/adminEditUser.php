<?php
if (!isset($account)) {
    echo 'Aucun compte chargé.';
    exit;
}
?>

<!doctype html>
<html lang="fr">
<head>
    <?php require_once __DIR__ . '/head.php'; ?>
    <meta charset="UTF-8">
    <title>Modifier un utilisateur</title>
</head>
<body>
<?php require_once __DIR__ . '/navbar.php'; ?>
<div class="container mt-5">
    <h1 class="mb-4">Modifier l'utilisateur #<?php echo htmlspecialchars($account['id']); ?></h1>

    <div class="card hero-card background-secondaire rounded-3 mb-5 p-4">
        <form method="POST" action="/DungeonXplorer/manage_accounts/update" class="d-flex flex-column gap-3 w-50 mx-auto">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($account['id']); ?>">

            <div class="input-group">
                <span class="input-group-text">Nom d'utilisateur</span>
                <input type="text" name="username" class="form-control" required value="<?php echo htmlspecialchars($account['username']); ?>">
            </div>

            <div class="input-group">
                <span class="input-group-text">Nouveau mot de passe</span>
                <input type="password" name="password" class="form-control" placeholder="Laisser vide pour conserver le mot de passe actuel">
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <a href="/DungeonXplorer/manage_accounts" class="btn btn-secondary ms-2">Annuler</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
