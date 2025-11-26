<?php
// Inclure le fichier de connexion à la base de données
require_once 'connexion.php';

// Récupérer tous les comptes et leurs héros associés
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
        ORDER BY Account.id, Hero.id";

$stmt = $conn->prepare($sql);
$stmt->execute();
$accounts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil des Comptes</title>
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
<body>
    <h1>Profil des Comptes</h1>
    <?php 
    $currentAccountId = null;
    foreach ($accounts as $account): 
        // Afficher les informations du compte uniquement une fois
        if ($currentAccountId !== $account['account_id']):
            $currentAccountId = $account['account_id'];
    ?>
        <div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 20px;">
            <h2>Compte : <?php echo htmlspecialchars($account['username']); ?></h2>
            <p><strong>Email :</strong> <?php echo htmlspecialchars($account['email']); ?></p>
            <p>
                <strong>Mot de passe :</strong>
                <input type="password" id="password-<?php echo $account['account_id']; ?>" class="password" value="<?php echo htmlspecialchars($account['password_hash']); ?>" readonly>
                <button onclick="togglePassword('password-<?php echo $account['account_id']; ?>')">Afficher/Masquer</button>
            </p>
            <p><strong>Date de création :</strong> <?php echo htmlspecialchars($account['creation_date']); ?></p>
            <a href="edit_account.php?account_id=<?php echo $account['account_id']; ?>">Modifier le compte</a>
            <h3>Héros associés :</h3>
            <?php endif; ?>
            <?php if ($account['hero_id']): ?>
                <ul>
                    <li>
                        <strong>Nom :</strong> <?php echo htmlspecialchars($account['hero_name']); ?> |
                        <strong>Niveau :</strong> <?php echo htmlspecialchars($account['current_level']); ?> |
                        <strong>XP :</strong> <?php echo htmlspecialchars($account['xp']); ?>
                        <a href="edit_hero.php?hero_id=<?php echo $account['hero_id']; ?>">Modifier</a>
                    </li>
                </ul>
            <?php else: ?>
                <p>Aucun héros associé.</p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</body>
</html>