<?php

require_once 'core/Database.php';

$db = getDB();
$q = $db->prepare("SELECT account.admin FROM Account WHERE id = :user_id");
$q->execute([
    'user_id' => $_SESSION['id']
]);
$isAdmin = $q->fetchColumn();
if (!$isAdmin) {
    header("Location: /DungeonXplorer/account");
    exit();
}

$q = $db -> prepare("SELECT * FROM account");
$q -> execute();
$accounts = $q -> fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <?php require_once 'head.php'; ?>
    <title>Gestion des comptes</title>
</head>

<body>
    <?php require_once 'navbar.php'; ?>
    <h2 class="login-title mt-5 mb-4">Gestion des comptes</h2>
    <div class="container background-secondary texte-principal mb-5">
        <div class="list-group background-secondary texte-principal">
            <?php foreach ($accounts as $account): ?>
                <div class="background-secondary texte-principal list-group-item d-flex justify-content-between align-items-center">
                    <div class="background-secondary texte-principal">
                        <strong><?php echo htmlspecialchars($account['username']); ?></strong>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <?php if ($account['admin']): ?>
                            <span class="badge bg-primary">Admin</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Utilisateur</span>
                        <?php endif; ?>

                        <?php if ((int)($account['id'] ?? 0) !== (int)($_SESSION['id'] ?? 0)): ?>
                            <a href="/DungeonXplorer/manage_accounts/edit?id=<?php echo urlencode($account['id']); ?>" class="btn btn-sm btn-warning">Modifier</a>
                            <form method="POST" action="/DungeonXplorer/manage_accounts/delete" onsubmit="return confirm('Confirmer la suppression du compte <?php echo htmlspecialchars($account['username']); ?> ?');" style="display:inline-block; margin:0;">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($account['id']); ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                            </form>
                        <?php else: ?>
                            <button class="btn btn-sm btn-outline-secondary" disabled>Ton compte</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>

</html>