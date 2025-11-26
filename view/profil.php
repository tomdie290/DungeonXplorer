<?php
// Inclure le fichier de connexion à la base de données
require_once 'core/Database.php';

if (!isset($_SESSION['id'])) {
    header("Location: login");
    exit;
}

$db = getDB();
$currentAccountId = $_SESSION['id'];

// Récupérer les informations du compte connecté et ses héros associés
$sql = "SELECT 
            Account.id AS account_id,
            Account.username,
            Account.email,
            Account.password_hash,
            Account.creation_date,
            Hero.id AS hero_id,
            Hero.name AS hero_name,
            Hero.current_level,
            Hero.xp
        FROM Account
        LEFT JOIN Hero ON Account.id = Hero.account_id
        WHERE Account.id = :account_id
        ORDER BY Hero.id";
$stmt = $db->prepare($sql);
$stmt->execute(['account_id' => $currentAccountId]);
$accounts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Profil des Comptes</title>
    <?php require_once 'head.php'; ?>
    <style>
        .password {
            font-family: monospace;
        }
    </style>
    <script>
        function togglePassword(id) {
            const passwordField = document.getElementById(id);
            if (passwordField.type === "password") {
                passwordField.type = "text";
            } else {
                passwordField.type = "password";
            }
        }
        </script>
</head>
<body class="bg-dark text-light "> <!-- Fond noir, texte blanc -->
    <div class="container mt-5">
        <h1 class="text-center mb-4">Profil de votre Compte</h1>
        <?php 
        foreach ($accounts as $account): 
        ?>
            <div class="card mb-4 bg-dark text-light border border-2 border-white rounded-3"> <!-- Carte avec fond blanc, texte noir -->
                <div class="card-body">
                    <h2 class="card-title">Compte : <?php echo htmlspecialchars($account['username']); ?></h2>
                    <p>
                        <strong>Mot de passe :</strong>
                        <input type="password" id="password-<?php echo $account['account_id']; ?>" class="form-control d-inline-block w-auto" value="<?php echo htmlspecialchars($account['password_hash']); ?>" readonly>
                        <button class="btn btn-secondary btn-sm" onclick="togglePassword('password-<?php echo $account['account_id']; ?>')">Afficher/Masquer</button>
                    </p>
                    <p>
                        <strong>Modifier le mot de passe :</strong>
                        <form method="POST" action="update_password" class="d-flex gap-2 align-items-center">
                            <div class="input-group">
                                <input type="password" name="new_password" id="new-password-<?php echo $account['account_id']; ?>" 
                                    class="form-control" placeholder="Nouveau mot de passe" required>
                                
                                <button type="button" class="btn btn-secondary" 
                                        onclick="toggleNewPassword('<?php echo $account['account_id']; ?>')">
                                    Voir / Masquer
                                </button>
                            </div>

                            <input type="hidden" name="account_id" value="<?php echo $account['account_id']; ?>">
                            <button type="submit" class="btn btn-primary">Modifier</button>
                        </form>
                    </p>

                    <script>
                        function toggleNewPassword(id) {
                            const field = document.getElementById('new-password-' + id);
                            if (field.type === "password") {
                                field.type = "text";
                            } else {
                                field.type = "password";
                            }
                        }
                    </script>


                    <p><strong>Date de création :</strong> <?php echo htmlspecialchars($account['creation_date']); ?></p>
                    <h3>Héros associés :</h3>
                    <?php if ($account['hero_id']): ?>
                        <ul class="list-group">
                            <li class="list-group-item bg-dark text-light">
                                <strong>Nom :</strong> <?php echo htmlspecialchars($account['hero_name']); ?> |
                                <strong>Niveau :</strong> <?php echo htmlspecialchars($account['current_level']); ?> |
                                <strong>XP :</strong> <?php echo htmlspecialchars($account['xp']); ?>
                            </li>
                        </ul>
                    <?php else: ?>
                        <p>Aucun héros associé.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>